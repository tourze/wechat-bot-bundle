<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use HttpClientBundle\Request\RequestInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\DTO\GroupCreateResult;
use Tourze\WechatBotBundle\DTO\GroupDetailResult;
use Tourze\WechatBotBundle\DTO\GroupMemberInfo;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Entity\WeChatGroup;
use Tourze\WechatBotBundle\Exception\InvalidArgumentException;
use Tourze\WechatBotBundle\Repository\WeChatGroupRepository;
use Tourze\WechatBotBundle\Request\CreateGroupRequest;
use Tourze\WechatBotBundle\Request\GetFriendsAndGroupsRequest;
use Tourze\WechatBotBundle\Request\Group\AcceptGroupInviteRequest;
use Tourze\WechatBotBundle\Request\Group\AddGroupMemberAsFriendRequest;
use Tourze\WechatBotBundle\Request\Group\AddGroupMemberRequest;
use Tourze\WechatBotBundle\Request\Group\AtGroupMemberRequest;
use Tourze\WechatBotBundle\Request\Group\GetGroupDetailRequest;
use Tourze\WechatBotBundle\Request\Group\GetGroupMemberDetailRequest;
use Tourze\WechatBotBundle\Request\Group\GetGroupMembersRequest;
use Tourze\WechatBotBundle\Request\Group\GetGroupQrCodeRequest;
use Tourze\WechatBotBundle\Request\Group\GroupAdminOperationRequest;
use Tourze\WechatBotBundle\Request\Group\InviteGroupMemberRequest;
use Tourze\WechatBotBundle\Request\Group\LeaveGroupRequest;
use Tourze\WechatBotBundle\Request\Group\SaveGroupToContactRequest;
use Tourze\WechatBotBundle\Request\Group\UpdateGroupNameRequest;
use Tourze\WechatBotBundle\Request\Group\UpdateGroupNicknameRequest;
use Tourze\WechatBotBundle\Request\Group\UpdateGroupRemarkRequest;
use Tourze\WechatBotBundle\Request\RemoveGroupMemberRequest;

/**
 * 微信群组管理服务
 *
 * 提供群组创建、成员管理、群信息管理等业务功能
 */
#[WithMonologChannel(channel: 'wechat_bot')]
#[Autoconfigure(public: true)]
readonly class WeChatGroupService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WeChatApiClient $apiClient,
        private LoggerInterface $logger,
        private WeChatGroupRepository $groupRepository,
    ) {
    }

    /**
     * 创建微信群
     *
     * @param string[] $memberWxids
     */
    public function createGroup(WeChatAccount $account, array $memberWxids, string $groupName = '新群聊'): ?GroupCreateResult
    {
        return $this->executeApiCall(
            $account,
            '创建群聊',
            fn (WeChatApiAccount $apiAccount, string $deviceId) => $this->performCreateGroup($apiAccount, $deviceId, $account, $memberWxids, $groupName),
            ['members' => $memberWxids, 'group_name' => $groupName]
        );
    }

    /**
     * @param string[] $memberWxids
     */
    private function performCreateGroup(WeChatApiAccount $apiAccount, string $deviceId, WeChatAccount $account, array $memberWxids, string $groupName): ?GroupCreateResult
    {
        $userList = implode(',', $memberWxids);
        $request = new CreateGroupRequest($apiAccount, $deviceId, $groupName, $userList);
        $data = $this->apiClient->request($request);

        $this->validateArrayResponse($data);
        assert(is_array($data));

        /** @var array<string, mixed> $data */
        $groupWxid = is_string($data['group_wxid'] ?? null) ? $data['group_wxid'] : '';

        if ('' === $groupWxid) {
            $this->logCreateGroupError($deviceId, $data);

            return null;
        }

        $this->createLocalGroupRecord($account, $groupWxid, $data, $memberWxids, $groupName);

        $this->logger->info('创建群聊成功', [
            'device_id' => $deviceId,
            'group_wxid' => $groupWxid,
            'member_count' => count($memberWxids),
        ]);

        $finalGroupName = is_string($data['group_name'] ?? null) ? $data['group_name'] : $groupName;

        return new GroupCreateResult($groupWxid, $finalGroupName, $memberWxids);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function logCreateGroupError(string $deviceId, array $data): void
    {
        $this->logger->error('创建群聊返回数据异常', [
            'device_id' => $deviceId,
            'response_data' => $data,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @param string[] $memberWxids
     */
    private function createLocalGroupRecord(WeChatAccount $account, string $groupWxid, array $data, array $memberWxids, string $groupName): void
    {
        $group = new WeChatGroup();
        $group->setAccount($account);
        $group->setGroupId($groupWxid);
        $group->setGroupName(is_string($data['group_name'] ?? null) ? $data['group_name'] : $groupName);
        $group->setMemberCount(count($memberWxids) + 1); // 包含自己

        $this->entityManager->persist($group);
        $this->entityManager->flush();
    }

    /**
     * 添加群成员
     */
    public function addGroupMember(WeChatAccount $account, string $groupWxid, string $memberWxid): bool
    {
        return $this->executeApiCall(
            $account,
            '添加群成员',
            fn (WeChatApiAccount $apiAccount, string $deviceId) => $this->performAddGroupMember($apiAccount, $deviceId, $groupWxid, $memberWxid),
            ['group_wxid' => $groupWxid, 'member_wxid' => $memberWxid]
        ) ?? false;
    }

    private function performAddGroupMember(WeChatApiAccount $apiAccount, string $deviceId, string $groupWxid, string $memberWxid): bool
    {
        $request = new AddGroupMemberRequest($apiAccount, $deviceId, $groupWxid, $memberWxid);
        $this->apiClient->request($request);

        $this->logger->info('添加群成员成功', [
            'device_id' => $deviceId,
            'group_wxid' => $groupWxid,
            'member_wxid' => $memberWxid,
        ]);

        return true;
    }

    /**
     * 邀请群成员
     *
     * @param array<string, mixed> $memberWxids
     */
    public function inviteGroupMember(WeChatAccount $account, string $groupWxid, array $memberWxids): bool
    {
        return $this->executeApiCall(
            $account,
            '邀请群成员',
            fn (WeChatApiAccount $apiAccount, string $deviceId) => $this->performInviteGroupMember($apiAccount, $deviceId, $groupWxid, $memberWxids),
            ['group_wxid' => $groupWxid, 'member_wxids' => $memberWxids]
        ) ?? false;
    }

    /**
     * @param array<string, mixed> $memberWxids
     */
    private function performInviteGroupMember(WeChatApiAccount $apiAccount, string $deviceId, string $groupWxid, array $memberWxids): bool
    {
        $memberList = implode(',', $memberWxids);
        $request = new InviteGroupMemberRequest($apiAccount, $deviceId, $groupWxid, $memberList);
        $this->apiClient->request($request);

        $this->logger->info('邀请群成员成功', [
            'device_id' => $deviceId,
            'group_wxid' => $groupWxid,
            'member_count' => count($memberWxids),
        ]);

        return true;
    }

    /**
     * 移除群成员
     */
    public function removeGroupMember(WeChatAccount $account, string $groupWxid, string $memberWxid): bool
    {
        try {
            $this->validateAccount($account);

            $apiAccount = $account->getApiAccount();
            if (null === $apiAccount) {
                throw new \RuntimeException('API account not found for WeChatAccount');
            }

            $deviceId = $account->getDeviceId();
            if (null === $deviceId) {
                throw new \RuntimeException('Device ID not found for WeChatAccount');
            }

            $request = new RemoveGroupMemberRequest(
                $apiAccount,
                $deviceId,
                $groupWxid,
                $memberWxid
            );

            $response = $this->apiClient->request($request);

            $this->logger->info('群成员移除成功', [
                'device_id' => $deviceId,
                'group_wxid' => $groupWxid,
                'member_wxid' => $memberWxid,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('移除群成员异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'member_wxid' => $memberWxid,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * 退出群聊
     */
    public function leaveGroup(WeChatAccount $account, string $groupWxid): bool
    {
        return $this->executeApiCall(
            $account,
            '退出群聊',
            fn (WeChatApiAccount $apiAccount, string $deviceId) => $this->performLeaveGroup($apiAccount, $deviceId, $account, $groupWxid),
            ['group_wxid' => $groupWxid]
        ) ?? false;
    }

    private function performLeaveGroup(WeChatApiAccount $apiAccount, string $deviceId, WeChatAccount $account, string $groupWxid): bool
    {
        $request = new LeaveGroupRequest($apiAccount, $deviceId, $groupWxid);
        $this->apiClient->request($request);

        // 删除本地群组记录
        $group = $this->groupRepository->findOneBy(['account' => $account, 'groupId' => $groupWxid]);

        if ((bool) $group) {
            $this->entityManager->remove($group);
            $this->entityManager->flush();
        }

        $this->logger->info('退出群聊成功', [
            'device_id' => $deviceId,
            'group_wxid' => $groupWxid,
        ]);

        return true;
    }

    /**
     * 修改群名称
     */
    public function updateGroupName(WeChatAccount $account, string $groupWxid, string $groupName): bool
    {
        return $this->executeApiCall(
            $account,
            '修改群名称',
            fn (WeChatApiAccount $apiAccount, string $deviceId) => $this->performUpdateGroupName($apiAccount, $deviceId, $account, $groupWxid, $groupName),
            ['group_wxid' => $groupWxid, 'group_name' => $groupName]
        ) ?? false;
    }

    private function performUpdateGroupName(WeChatApiAccount $apiAccount, string $deviceId, WeChatAccount $account, string $groupWxid, string $groupName): bool
    {
        $request = new UpdateGroupNameRequest($apiAccount, $deviceId, $groupWxid, $groupName);
        $this->apiClient->request($request);

        // 更新本地群组记录
        $group = $this->groupRepository->findOneBy(['account' => $account, 'groupId' => $groupWxid]);

        if ((bool) $group) {
            $group->setGroupName($groupName);
            $this->entityManager->flush();
        }

        $this->logger->info('修改群名称成功', [
            'device_id' => $deviceId,
            'group_wxid' => $groupWxid,
            'group_name' => $groupName,
        ]);

        return true;
    }

    /**
     * 修改群备注
     */
    public function updateGroupRemark(WeChatAccount $account, string $groupWxid, string $remark): bool
    {
        return $this->executeApiCall(
            $account,
            '修改群备注',
            fn (WeChatApiAccount $apiAccount, string $deviceId) => $this->performUpdateGroupRemark($apiAccount, $deviceId, $account, $groupWxid, $remark),
            ['group_wxid' => $groupWxid, 'remark' => $remark]
        ) ?? false;
    }

    private function performUpdateGroupRemark(WeChatApiAccount $apiAccount, string $deviceId, WeChatAccount $account, string $groupWxid, string $remark): bool
    {
        $request = new UpdateGroupRemarkRequest($apiAccount, $deviceId, $groupWxid, $remark);
        $this->apiClient->request($request);

        // 更新本地群组记录
        $group = $this->groupRepository->findOneBy(['account' => $account, 'groupId' => $groupWxid]);

        if ((bool) $group) {
            $group->setRemark($remark);
            $this->entityManager->flush();
        }

        $this->logger->info('修改群备注成功', [
            'device_id' => $deviceId,
            'group_wxid' => $groupWxid,
            'remark' => $remark,
        ]);

        return true;
    }

    /**
     * 设置群公告
     */
    public function setGroupAnnouncement(WeChatAccount $account, string $groupWxid, string $announcement): void
    {
        try {
            // 设置群公告功能暂未实现，记录日志
            $this->logger->warning('设置群公告功能暂未实现', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'announcement' => $announcement,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('设置群公告异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 获取群二维码
     */
    public function getGroupQrCode(WeChatAccount $account, string $groupWxid): ?string
    {
        return $this->executeApiCall(
            $account,
            '获取群二维码',
            fn (WeChatApiAccount $apiAccount, string $deviceId) => $this->performGetGroupQrCode($apiAccount, $deviceId, $groupWxid),
            ['group_wxid' => $groupWxid]
        );
    }

    private function performGetGroupQrCode(WeChatApiAccount $apiAccount, string $deviceId, string $groupWxid): ?string
    {
        $request = new GetGroupQrCodeRequest($apiAccount, $deviceId, $groupWxid);
        $data = $this->apiClient->request($request);

        $this->validateArrayResponse($data);
        assert(is_array($data));

        $qrCode = $data['qr_code'] ?? null;

        return is_string($qrCode) ? $qrCode : null;
    }

    /**
     * 获取群详细信息
     */
    public function getGroupDetail(WeChatAccount $account, string $groupWxid): ?GroupDetailResult
    {
        return $this->executeApiCall(
            $account,
            '获取群详情',
            fn (WeChatApiAccount $apiAccount, string $deviceId) => $this->performGetGroupDetail($apiAccount, $deviceId, $groupWxid),
            ['group_wxid' => $groupWxid]
        );
    }

    private function performGetGroupDetail(WeChatApiAccount $apiAccount, string $deviceId, string $groupWxid): GroupDetailResult
    {
        $request = new GetGroupDetailRequest($apiAccount, $deviceId, $groupWxid);
        $data = $this->apiClient->request($request);

        $this->validateArrayResponse($data);
        assert(is_array($data));
        /** @var array<string, mixed> $data */

        return new GroupDetailResult(
            $this->extractStringFromData($data, 'wxid'),
            $this->extractStringFromData($data, 'group_name'),
            $this->extractIntFromData($data, 'member_count'),
            $this->extractIntFromData($data, 'max_member_count'),
            $this->extractStringFromData($data, 'owner_wxid'),
            $this->extractStringFromData($data, 'notice'),
            $this->extractStringFromData($data, 'avatar'),
            $this->extractIntFromData($data, 'create_time')
        );
    }

    /**
     * 获取群成员列表
     *
     * @return list<GroupMemberInfo>
     */
    public function getGroupMembers(WeChatAccount $account, string $groupWxid): array
    {
        $result = $this->executeApiCall(
            $account,
            '获取群成员列表',
            fn (WeChatApiAccount $apiAccount, string $deviceId) => $this->performGetGroupMembers($apiAccount, $deviceId, $groupWxid),
            ['group_wxid' => $groupWxid]
        );

        return $result ?? [];
    }

    /**
     * @return list<GroupMemberInfo>
     */
    private function performGetGroupMembers(WeChatApiAccount $apiAccount, string $deviceId, string $groupWxid): array
    {
        $request = new GetGroupMembersRequest($apiAccount, $deviceId, $groupWxid);
        $data = $this->apiClient->request($request);

        $this->validateArrayResponse($data);
        assert(is_array($data));
        $rawMembers = $data['members'] ?? [];
        $members = is_array($rawMembers) ? $rawMembers : [];

        /** @var array<int, mixed> $membersTyped */
        $membersTyped = array_values($members);

        return $this->buildGroupMembersList($membersTyped);
    }

    /**
     * 获取群成员详情
     */
    public function getGroupMemberDetail(WeChatAccount $account, string $groupWxid, string $memberWxid): ?GroupMemberInfo
    {
        return $this->executeApiCall(
            $account,
            '获取群成员详情',
            fn (WeChatApiAccount $apiAccount, string $deviceId) => $this->performGetGroupMemberDetail($apiAccount, $deviceId, $groupWxid, $memberWxid),
            ['group_wxid' => $groupWxid, 'member_wxid' => $memberWxid]
        );
    }

    private function performGetGroupMemberDetail(WeChatApiAccount $apiAccount, string $deviceId, string $groupWxid, string $memberWxid): GroupMemberInfo
    {
        $request = new GetGroupMemberDetailRequest($apiAccount, $deviceId, $groupWxid, $memberWxid);
        $data = $this->apiClient->request($request);

        $this->validateArrayResponse($data);
        assert(is_array($data));
        /** @var array<string, mixed> $data */

        return $this->buildGroupMemberInfo($data);
    }

    /**
     * 群管理员操作（设置/取消管理员）
     */
    public function groupAdminOperation(WeChatAccount $account, string $groupWxid, string $memberWxid, string $operation): bool
    {
        return $this->executeSimpleApiCall(
            $account,
            '群管理员操作',
            $groupWxid,
            fn (WeChatApiAccount $api, string $did) => new GroupAdminOperationRequest($api, $did, $groupWxid, $memberWxid, $operation)
        );
    }

    /**
     * 修改在群里的昵称
     */
    public function updateGroupNickname(WeChatAccount $account, string $groupWxid, string $nickname): bool
    {
        return $this->executeSimpleApiCall(
            $account,
            '修改群昵称',
            $groupWxid,
            fn (WeChatApiAccount $api, string $did) => new UpdateGroupNicknameRequest($api, $did, $groupWxid, $nickname)
        );
    }

    /**
     * 保存群聊到通讯录
     */
    public function saveGroupToContact(WeChatAccount $account, string $groupWxid): bool
    {
        return $this->executeSimpleApiCall(
            $account,
            '保存群聊到通讯录',
            $groupWxid,
            fn (WeChatApiAccount $api, string $did) => new SaveGroupToContactRequest($api, $did, $groupWxid)
        );
    }

    /**
     * 通过入群邀请
     */
    public function acceptGroupInvite(WeChatAccount $account, string $encryptUsername, string $ticket): bool
    {
        return $this->executeApiCall(
            $account,
            '通过入群邀请',
            fn (WeChatApiAccount $apiAccount, string $deviceId) => $this->performAcceptGroupInvite($apiAccount, $deviceId, $encryptUsername, $ticket),
            ['encrypt_username' => $encryptUsername]
        ) ?? false;
    }

    private function performAcceptGroupInvite(WeChatApiAccount $apiAccount, string $deviceId, string $encryptUsername, string $ticket): bool
    {
        $request = new AcceptGroupInviteRequest($apiAccount, $deviceId, $encryptUsername, $ticket);
        $this->apiClient->request($request);

        $this->logger->info('通过入群邀请成功', [
            'device_id' => $deviceId,
            'encrypt_username' => $encryptUsername,
        ]);

        return true;
    }

    /**
     * 添加群成员为好友
     */
    public function addGroupMemberAsFriend(WeChatAccount $account, string $groupWxid, string $memberWxid, string $verifyMessage = ''): bool
    {
        return $this->executeApiCall(
            $account,
            '添加群成员为好友',
            fn (WeChatApiAccount $apiAccount, string $deviceId) => $this->performAddGroupMemberAsFriend($apiAccount, $deviceId, $groupWxid, $memberWxid, $verifyMessage),
            ['group_wxid' => $groupWxid, 'member_wxid' => $memberWxid]
        ) ?? false;
    }

    private function performAddGroupMemberAsFriend(WeChatApiAccount $apiAccount, string $deviceId, string $groupWxid, string $memberWxid, string $verifyMessage): bool
    {
        $request = new AddGroupMemberAsFriendRequest($apiAccount, $deviceId, $groupWxid, $memberWxid, $verifyMessage);
        $this->apiClient->request($request);

        $this->logger->info('添加群成员为好友成功', [
            'device_id' => $deviceId,
            'group_wxid' => $groupWxid,
            'member_wxid' => $memberWxid,
        ]);

        return true;
    }

    /**
     * @群成员
     *
     * @param list<string> $memberWxids
     */
    public function atGroupMember(WeChatAccount $account, string $groupWxid, array $memberWxids, string $message): bool
    {
        return $this->executeApiCall(
            $account,
            '@群成员',
            fn (WeChatApiAccount $apiAccount, string $deviceId) => $this->performAtGroupMember($apiAccount, $deviceId, $groupWxid, $memberWxids, $message),
            ['group_wxid' => $groupWxid, 'member_wxids' => $memberWxids]
        ) ?? false;
    }

    /**
     * @param list<string> $memberWxids
     */
    private function performAtGroupMember(WeChatApiAccount $apiAccount, string $deviceId, string $groupWxid, array $memberWxids, string $message): bool
    {
        $memberList = implode(',', $memberWxids);
        $request = new AtGroupMemberRequest($apiAccount, $deviceId, $groupWxid, $memberList, $message);
        $this->apiClient->request($request);

        $this->logger->info('@群成员成功', [
            'device_id' => $deviceId,
            'group_wxid' => $groupWxid,
            'member_count' => count($memberWxids),
        ]);

        return true;
    }

    /**
     * 获取好友和群列表
     */
    public function syncGroups(WeChatAccount $account): bool
    {
        return $this->executeApiCall(
            $account,
            '同步群列表',
            fn (WeChatApiAccount $apiAccount, string $deviceId) => $this->performSyncGroups($apiAccount, $deviceId, $account),
            []
        ) ?? false;
    }

    private function performSyncGroups(WeChatApiAccount $apiAccount, string $deviceId, WeChatAccount $account): bool
    {
        $request = new GetFriendsAndGroupsRequest($apiAccount, $deviceId);
        $data = $this->apiClient->request($request);

        $this->validateArrayResponse($data);
        assert(is_array($data));
        $rawGroups = $data['groups'] ?? [];
        $groups = is_array($rawGroups) ? $rawGroups : [];

        /** @var array<int, mixed> $groupsTyped */
        $groupsTyped = array_values($groups);
        $syncCount = $this->processSyncGroups($account, $groupsTyped);

        $this->entityManager->flush();

        $this->logger->info('同步群列表成功', [
            'device_id' => $deviceId,
            'sync_count' => $syncCount,
        ]);

        return true;
    }

    /**
     * 根据微信ID获取本地群组信息
     */
    public function getLocalGroup(WeChatAccount $account, string $wxid): ?WeChatGroup
    {
        return $this->groupRepository
            ->findOneBy(['account' => $account, 'groupId' => $wxid])
        ;
    }

    /**
     * 获取账号的所有群组
     *
     * @return WeChatGroup[]
     */
    public function getAllGroups(WeChatAccount $account): array
    {
        return $this->groupRepository
            ->findBy(['account' => $account])
        ;
    }

    /**
     * 搜索本地群组
     *
     * @return WeChatGroup[]
     */
    public function searchLocalGroups(WeChatAccount $account, string $keyword): array
    {
        $qb = $this->groupRepository->createQueryBuilder('g');
        $keywordParam = '%' . $keyword . '%';

        $result = $qb
            ->where('g.account = :account')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->like('g.groupName', ':keyword'),
                $qb->expr()->like('g.remark', ':keyword'),
                $qb->expr()->like('g.groupId', ':keyword')
            ))
            ->setParameter('account', $account)
            ->setParameter('keyword', $keywordParam)
            ->getQuery()
            ->getResult()
        ;

        return $this->filterGroupsFromResult($result);
    }

    /**
     * @param mixed $result
     * @return WeChatGroup[]
     */
    private function filterGroupsFromResult(mixed $result): array
    {
        if (!is_array($result)) {
            return [];
        }

        return array_filter($result, fn ($item) => $item instanceof WeChatGroup);
    }

    /**
     * 验证账号的API账号和设备ID不为null
     */
    private function validateAccount(WeChatAccount $account): void
    {
        if (null === $account->getApiAccount()) {
            throw new InvalidArgumentException('API账号不能为null');
        }

        if (null === $account->getDeviceId()) {
            throw new InvalidArgumentException('设备ID不能为null');
        }
    }

    /**
     * @return array{0: WeChatApiAccount, 1: string}
     */
    private function requireApiAndDevice(WeChatAccount $account): array
    {
        $apiAccount = $account->getApiAccount();
        $deviceId = $account->getDeviceId();

        if (null === $apiAccount || null === $deviceId) {
            // 保持与 validateAccount 语义一致
            throw new InvalidArgumentException('API账号或设备ID不能为null');
        }

        return [$apiAccount, $deviceId];
    }

    /**
     * 验证API响应是数组格式
     *
     * @param mixed $data
     */
    private function validateArrayResponse(mixed $data): void
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid API response format: expected array, got ' . gettype($data));
        }
    }

    /**
     * 构建群成员列表
     *
     * @param array<int, mixed> $members
     * @return list<GroupMemberInfo>
     */
    private function buildGroupMembersList(array $members): array
    {
        $result = [];
        foreach ($members as $memberData) {
            $member = $this->tryBuildGroupMemberInfo($memberData);
            if (null !== $member) {
                $result[] = $member;
            }
        }

        return $result;
    }

    /**
     * @param mixed $memberData
     */
    private function tryBuildGroupMemberInfo(mixed $memberData): ?GroupMemberInfo
    {
        if (!is_array($memberData)) {
            $this->logger->warning('Invalid member data format, skipping', ['data' => $memberData]);

            return null;
        }

        /** @var array<string, mixed> $memberDataTyped */
        $memberDataTyped = $memberData;

        return $this->buildGroupMemberInfo($memberDataTyped);
    }

    /**
     * 构建单个群成员信息
     *
     * @param array<string, mixed> $data
     */
    private function buildGroupMemberInfo(array $data): GroupMemberInfo
    {
        return new GroupMemberInfo(
            $this->extractStringFromData($data, 'wxid'),
            $this->extractStringFromData($data, 'nickname'),
            $this->extractStringFromData($data, 'display_name'),
            $this->extractStringFromData($data, 'avatar'),
            $this->extractStringFromData($data, 'inviter_wxid'),
            $this->extractIntFromData($data, 'join_time'),
            (bool) ($data['is_admin'] ?? false)
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractStringFromData(array $data, string $key): string
    {
        return is_string($data[$key] ?? null) ? $data[$key] : '';
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractIntFromData(array $data, string $key): int
    {
        return is_int($data[$key] ?? null) ? $data[$key] : 0;
    }

    /**
     * 处理群组同步数据
     *
     * @param array<int, mixed> $groups
     * @return int 同步的群组数量
     */
    private function processSyncGroups(WeChatAccount $account, array $groups): int
    {
        $syncCount = 0;
        foreach ($groups as $groupData) {
            if ($this->processGroupData($account, $groupData)) {
                ++$syncCount;
            }
        }

        return $syncCount;
    }

    /**
     * 处理单个群组数据
     *
     * @param mixed $groupData
     */
    private function processGroupData(WeChatAccount $account, mixed $groupData): bool
    {
        $groupDataTyped = $this->validateGroupData($groupData);
        if (null === $groupDataTyped) {
            return false;
        }

        $wxid = is_string($groupDataTyped['wxid'] ?? null) ? $groupDataTyped['wxid'] : '';
        if ('' === $wxid) {
            return false;
        }

        $group = $this->findOrCreateGroup($account, $wxid);
        $this->updateGroupFromData($group, $groupDataTyped);

        return true;
    }

    /**
     * @param mixed $groupData
     * @return array<string, mixed>|null
     */
    private function validateGroupData(mixed $groupData): ?array
    {
        if (!is_array($groupData)) {
            $this->logger->warning('Invalid group data format, skipping', ['data' => $groupData]);

            return null;
        }

        /** @var array<string, mixed> */
        return $groupData;
    }

    /**
     * 查找或创建群组
     */
    private function findOrCreateGroup(WeChatAccount $account, string $wxid): WeChatGroup
    {
        $group = $this->groupRepository->findOneBy(['account' => $account, 'groupId' => $wxid]);

        if (null === $group) {
            return $this->createNewGroup($account, $wxid);
        }

        return $group;
    }

    private function createNewGroup(WeChatAccount $account, string $wxid): WeChatGroup
    {
        $group = new WeChatGroup();
        $group->setAccount($account);
        $group->setGroupId($wxid);

        return $group;
    }

    /**
     * 从数据中更新群组信息
     *
     * @param array<string, mixed> $data
     */
    private function updateGroupFromData(WeChatGroup $group, array $data): void
    {
        $this->setGroupBasicInfo($group, $data);
        $this->setGroupOptionalFields($group, $data);
        $this->entityManager->persist($group);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setGroupBasicInfo(WeChatGroup $group, array $data): void
    {
        $group->setGroupName($this->extractStringFromData($data, 'group_name'));
        $group->setRemark($this->extractStringFromData($data, 'remark'));
        $group->setAvatar($this->extractStringFromData($data, 'avatar'));
        $group->setMemberCount($this->extractIntFromData($data, 'member_count'));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function setGroupOptionalFields(WeChatGroup $group, array $data): void
    {
        $group->setOwnerId($this->extractStringFromData($data, 'owner_wxid'));
        $group->setAnnouncement($this->extractStringFromData($data, 'notice'));
    }

    /**
     * 统一的API调用包装器，处理异常和日志
     *
     * @template T
     * @param callable(WeChatApiAccount, string): T $operation
     * @param array<string, mixed> $context
     * @return T|null
     */
    private function executeApiCall(WeChatAccount $account, string $operationName, callable $operation, array $context = []): mixed
    {
        try {
            $this->validateAccount($account);
            [$apiAccount, $deviceId] = $this->requireApiAndDevice($account);

            return $operation($apiAccount, $deviceId);
        } catch (\Exception $e) {
            $this->logger->error($operationName . '异常', array_merge(
                ['device_id' => $account->getDeviceId() ?? '', 'exception' => $e->getMessage()],
                $context
            ));

            return null;
        }
    }

    /**
     * 简单API调用的辅助方法（只需要创建request并记录成功日志）
     */
    private function executeSimpleApiCall(WeChatAccount $account, string $operationName, string $groupWxid, callable $requestFactory): bool
    {
        $result = $this->executeApiCall(
            $account,
            $operationName,
            fn (WeChatApiAccount $api, string $did) => $this->performSimpleApiCall($api, $did, $operationName, $groupWxid, $requestFactory),
            ['group_wxid' => $groupWxid]
        );

        return $result ?? false;
    }

    private function performSimpleApiCall(WeChatApiAccount $apiAccount, string $deviceId, string $operationName, string $groupWxid, callable $requestFactory): bool
    {
        $request = $requestFactory($apiAccount, $deviceId);
        assert($request instanceof RequestInterface);
        $this->apiClient->request($request);

        $this->logger->info($operationName . '成功', [
            'device_id' => $deviceId,
            'group_wxid' => $groupWxid,
        ]);

        return true;
    }
}
