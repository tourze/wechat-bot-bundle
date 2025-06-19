<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\DTO\GroupCreateResult;
use Tourze\WechatBotBundle\DTO\GroupDetailResult;
use Tourze\WechatBotBundle\DTO\GroupMemberInfo;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatGroup;
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

/**
 * 微信群组管理服务
 *
 * 提供群组创建、成员管理、群信息管理等业务功能
 */
class WeChatGroupService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WeChatApiClient $apiClient,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * 创建微信群
     */
    public function createGroup(WeChatAccount $account, array $memberWxids, string $groupName = '新群聊'): ?GroupCreateResult
    {
        try {
            $userList = implode(',', $memberWxids);
            $request = new CreateGroupRequest($account->getApiAccount(), $account->getDeviceId(), $groupName, $userList);
            $data = $this->apiClient->request($request);

            $groupWxid = $data['group_wxid'] ?? '';

            if ((bool) empty($groupWxid)) {
                $this->logger->error('创建群聊返回数据异常', [
                    'device_id' => $account->getDeviceId(),
                    'response_data' => $data
                ]);
                return null;
            }

            // 创建本地群组记录
            $group = new WeChatGroup();
            $group->setAccount($account);
            $group->setGroupId($groupWxid);
            $group->setGroupName($data['group_name'] ?? $groupName);
            $group->setMemberCount(count($memberWxids) + 1); // 包含自己

            $this->entityManager->persist($group);
            $this->entityManager->flush();

            $this->logger->info('创建群聊成功', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'member_count' => count($memberWxids)
            ]);

            return new GroupCreateResult($groupWxid, $data['group_name'] ?? $groupName, $memberWxids);
        } catch (\Exception $e) {
            $this->logger->error('创建群聊异常', [
                'device_id' => $account->getDeviceId(),
                'members' => $memberWxids,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 添加群成员
     */
    public function addGroupMember(WeChatAccount $account, string $groupWxid, string $memberWxid): bool
    {
        try {
            $request = new AddGroupMemberRequest($account->getApiAccount(), $account->getDeviceId(), $groupWxid, $memberWxid);
            $this->apiClient->request($request);

            $this->logger->info('添加群成员成功', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'member_wxid' => $memberWxid
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('添加群成员异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'member_wxid' => $memberWxid,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 邀请群成员
     */
    public function inviteGroupMember(WeChatAccount $account, string $groupWxid, array $memberWxids): bool
    {
        try {
            $memberList = implode(',', $memberWxids);
            $request = new InviteGroupMemberRequest($account->getApiAccount(), $account->getDeviceId(), $groupWxid, $memberList);
            $this->apiClient->request($request);

            $this->logger->info('邀请群成员成功', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'member_count' => count($memberWxids)
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('邀请群成员异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'member_wxids' => $memberWxids,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 移除群成员
     */
    public function removeGroupMember(WeChatAccount $account, string $groupWxid, string $memberWxid): bool
    {
        try {
            // 移除群成员功能暂未实现，记录日志
            $this->logger->warning('移除群成员功能暂未实现', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'member_wxid' => $memberWxid
            ]);

            return false;
        } catch (\Exception $e) {
            $this->logger->error('移除群成员异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'member_wxid' => $memberWxid,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 退出群聊
     */
    public function leaveGroup(WeChatAccount $account, string $groupWxid): bool
    {
        try {
            $request = new LeaveGroupRequest($account->getApiAccount(), $account->getDeviceId(), $groupWxid);
            $this->apiClient->request($request);

            // 删除本地群组记录
            $group = $this->entityManager->getRepository(WeChatGroup::class)
                ->findOneBy(['account' => $account, 'groupId' => $groupWxid]);

            if ((bool) $group) {
                $this->entityManager->remove($group);
                $this->entityManager->flush();
            }

            $this->logger->info('退出群聊成功', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('退出群聊异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 修改群名称
     */
    public function updateGroupName(WeChatAccount $account, string $groupWxid, string $groupName): bool
    {
        try {
            $request = new UpdateGroupNameRequest($account->getApiAccount(), $account->getDeviceId(), $groupWxid, $groupName);
            $this->apiClient->request($request);

            // 更新本地群组记录
            $group = $this->entityManager->getRepository(WeChatGroup::class)
                ->findOneBy(['account' => $account, 'groupId' => $groupWxid]);

            if ((bool) $group) {
                $group->setGroupName($groupName);
                $this->entityManager->flush();
            }

            $this->logger->info('修改群名称成功', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'group_name' => $groupName
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('修改群名称异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'group_name' => $groupName,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 修改群备注
     */
    public function updateGroupRemark(WeChatAccount $account, string $groupWxid, string $remark): bool
    {
        try {
            $request = new UpdateGroupRemarkRequest($account->getApiAccount(), $account->getDeviceId(), $groupWxid, $remark);
            $this->apiClient->request($request);

            // 更新本地群组记录
            $group = $this->entityManager->getRepository(WeChatGroup::class)
                ->findOneBy(['account' => $account, 'groupId' => $groupWxid]);

            if ((bool) $group) {
                $group->setRemark($remark);
                $this->entityManager->flush();
            }

            $this->logger->info('修改群备注成功', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'remark' => $remark
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('修改群备注异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'remark' => $remark,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 设置群公告
     */
    public function setGroupAnnouncement(WeChatAccount $account, string $groupWxid, string $announcement): bool
    {
        try {
            // 设置群公告功能暂未实现，记录日志
            $this->logger->warning('设置群公告功能暂未实现', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'announcement' => $announcement
            ]);

            return false;
        } catch (\Exception $e) {
            $this->logger->error('设置群公告异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 获取群二维码
     */
    public function getGroupQrCode(WeChatAccount $account, string $groupWxid): ?string
    {
        try {
            $request = new GetGroupQrCodeRequest($account->getApiAccount(), $account->getDeviceId(), $groupWxid);
            $data = $this->apiClient->request($request);

            return $data['qr_code'] ?? '';
        } catch (\Exception $e) {
            $this->logger->error('获取群二维码异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 获取群详细信息
     */
    public function getGroupDetail(WeChatAccount $account, string $groupWxid): ?GroupDetailResult
    {
        try {
            $request = new GetGroupDetailRequest($account->getApiAccount(), $account->getDeviceId(), $groupWxid);
            $data = $this->apiClient->request($request);

            return new GroupDetailResult(
                $data['wxid'] ?? '',
                $data['group_name'] ?? '',
                $data['member_count'] ?? 0,
                $data['max_member_count'] ?? 0,
                $data['owner_wxid'] ?? '',
                $data['notice'] ?? '',
                $data['avatar'] ?? '',
                $data['create_time'] ?? 0
            );
        } catch (\Exception $e) {
            $this->logger->error('获取群详情异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 获取群成员列表
     */
    public function getGroupMembers(WeChatAccount $account, string $groupWxid): array
    {
        try {
            $request = new GetGroupMembersRequest($account->getApiAccount(), $account->getDeviceId(), $groupWxid);
            $data = $this->apiClient->request($request);

            $members = $data['members'] ?? [];

            $result = [];
            foreach ($members as $memberData) {
                $result[] = new GroupMemberInfo(
                    $memberData['wxid'] ?? '',
                    $memberData['nickname'] ?? '',
                    $memberData['display_name'] ?? '',
                    $memberData['avatar'] ?? '',
                    $memberData['inviter_wxid'] ?? '',
                    $memberData['join_time'] ?? 0,
                    (bool) ($memberData['is_admin'] ?? false)
                );
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('获取群成员列表异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'exception' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * 获取群成员详情
     */
    public function getGroupMemberDetail(WeChatAccount $account, string $groupWxid, string $memberWxid): ?GroupMemberInfo
    {
        try {
            $request = new GetGroupMemberDetailRequest($account->getApiAccount(), $account->getDeviceId(), $groupWxid, $memberWxid);
            $data = $this->apiClient->request($request);

            return new GroupMemberInfo(
                $data['wxid'] ?? '',
                $data['nickname'] ?? '',
                $data['display_name'] ?? '',
                $data['avatar'] ?? '',
                $data['inviter_wxid'] ?? '',
                $data['join_time'] ?? 0,
                (bool) ($data['is_admin'] ?? false)
            );
        } catch (\Exception $e) {
            $this->logger->error('获取群成员详情异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'member_wxid' => $memberWxid,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 群管理员操作（设置/取消管理员）
     */
    public function groupAdminOperation(WeChatAccount $account, string $groupWxid, string $memberWxid, string $operation): bool
    {
        try {
            $request = new GroupAdminOperationRequest($account->getApiAccount(), $account->getDeviceId(), $groupWxid, $memberWxid, $operation);
            $this->apiClient->request($request);

            $this->logger->info('群管理员操作成功', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'member_wxid' => $memberWxid,
                'operation' => $operation
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('群管理员操作异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'member_wxid' => $memberWxid,
                'operation' => $operation,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 修改在群里的昵称
     */
    public function updateGroupNickname(WeChatAccount $account, string $groupWxid, string $nickname): bool
    {
        try {
            $request = new UpdateGroupNicknameRequest($account->getApiAccount(), $account->getDeviceId(), $groupWxid, $nickname);
            $this->apiClient->request($request);

            $this->logger->info('修改群昵称成功', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'nickname' => $nickname
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('修改群昵称异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'nickname' => $nickname,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 保存群聊到通讯录
     */
    public function saveGroupToContact(WeChatAccount $account, string $groupWxid): bool
    {
        try {
            $request = new SaveGroupToContactRequest($account->getApiAccount(), $account->getDeviceId(), $groupWxid);
            $this->apiClient->request($request);

            $this->logger->info('保存群聊到通讯录成功', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('保存群聊到通讯录异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 通过入群邀请
     */
    public function acceptGroupInvite(WeChatAccount $account, string $encryptUsername, string $ticket): bool
    {
        try {
            $request = new AcceptGroupInviteRequest($account->getApiAccount(), $account->getDeviceId(), $encryptUsername, $ticket);
            $this->apiClient->request($request);

            $this->logger->info('通过入群邀请成功', [
                'device_id' => $account->getDeviceId(),
                'encrypt_username' => $encryptUsername
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('通过入群邀请异常', [
                'device_id' => $account->getDeviceId(),
                'encrypt_username' => $encryptUsername,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 添加群成员为好友
     */
    public function addGroupMemberAsFriend(WeChatAccount $account, string $groupWxid, string $memberWxid, string $verifyMessage = ''): bool
    {
        try {
            $request = new AddGroupMemberAsFriendRequest($account->getApiAccount(), $account->getDeviceId(), $groupWxid, $memberWxid, $verifyMessage);
            $this->apiClient->request($request);

            $this->logger->info('添加群成员为好友成功', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'member_wxid' => $memberWxid
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('添加群成员为好友异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'member_wxid' => $memberWxid,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * @群成员
     */
    public function atGroupMember(WeChatAccount $account, string $groupWxid, array $memberWxids, string $message): bool
    {
        try {
            $memberList = implode(',', $memberWxids);
            $request = new AtGroupMemberRequest($account->getApiAccount(), $account->getDeviceId(), $groupWxid, $memberList, $message);
            $this->apiClient->request($request);

            $this->logger->info('@群成员成功', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'member_count' => count($memberWxids)
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('@群成员异常', [
                'device_id' => $account->getDeviceId(),
                'group_wxid' => $groupWxid,
                'member_wxids' => $memberWxids,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 获取好友和群列表
     */
    public function syncGroups(WeChatAccount $account): bool
    {
        try {
            $request = new GetFriendsAndGroupsRequest($account->getApiAccount(), $account->getDeviceId());
            $data = $this->apiClient->request($request);

            $groups = $data['groups'] ?? [];

            $syncCount = 0;
            foreach ($groups as $groupData) {
                $wxid = $groupData['wxid'] ?? '';
                if ((bool) empty($wxid)) {
                    continue;
                }

                $group = $this->entityManager->getRepository(WeChatGroup::class)
                    ->findOneBy(['account' => $account, 'groupId' => $wxid]);

                if (!$group) {
                    $group = new WeChatGroup();
                    $group->setAccount($account);
                    $group->setGroupId($wxid);
                }

                $group->setGroupName($groupData['group_name'] ?? '');
                $group->setRemark($groupData['remark'] ?? '');
                $group->setAvatar($groupData['avatar'] ?? '');
                $group->setMemberCount($groupData['member_count'] ?? 0);
                $group->setOwnerId($groupData['owner_wxid'] ?? '');
                $group->setAnnouncement($groupData['notice'] ?? '');

                $this->entityManager->persist($group);
                $syncCount++;
            }

            $this->entityManager->flush();

            $this->logger->info('同步群列表成功', [
                'device_id' => $account->getDeviceId(),
                'sync_count' => $syncCount
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('同步群列表异常', [
                'device_id' => $account->getDeviceId(),
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 根据微信ID获取本地群组信息
     */
    public function getLocalGroup(WeChatAccount $account, string $wxid): ?WeChatGroup
    {
        return $this->entityManager->getRepository(WeChatGroup::class)
            ->findOneBy(['account' => $account, 'groupId' => $wxid]);
    }

    /**
     * 获取账号的所有群组
     */
    public function getAllGroups(WeChatAccount $account): array
    {
        return $this->entityManager->getRepository(WeChatGroup::class)
            ->findBy(['account' => $account]);
    }

    /**
     * 搜索本地群组
     */
    public function searchLocalGroups(WeChatAccount $account, string $keyword): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('g')
            ->from(WeChatGroup::class, 'g')
            ->where('g.account = :account')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->like('g.groupName', ':keyword'),
                $qb->expr()->like('g.remark', ':keyword'),
                $qb->expr()->like('g.groupId', ':keyword')
            ))
            ->setParameter('account', $account)
            ->setParameter('keyword', '%' . $keyword . '%')
            ->getQuery()
            ->getResult();
    }
}
