<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\DTO\ContactInfoResult;
use Tourze\WechatBotBundle\DTO\ContactSearchResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatContact;
use Tourze\WechatBotBundle\Exception\InvalidArgumentException;
use Tourze\WechatBotBundle\Repository\WeChatContactRepository;
use Tourze\WechatBotBundle\Request\Friend\AcceptFriendRequest;
use Tourze\WechatBotBundle\Request\Friend\AddFriendRequest;
use Tourze\WechatBotBundle\Request\Friend\DeleteFriendRequest;
use Tourze\WechatBotBundle\Request\Friend\GetContactInfoRequest;
use Tourze\WechatBotBundle\Request\Friend\GetEnterpriseContactRequest;
use Tourze\WechatBotBundle\Request\Friend\GetMyQrCodeRequest;
use Tourze\WechatBotBundle\Request\Friend\SearchContactRequest;
use Tourze\WechatBotBundle\Request\Friend\UpdateFriendRemarkRequest;
use Tourze\WechatBotBundle\Request\GetFriendsAndGroupsRequest;

/**
 * 微信联系人管理服务
 *
 * 提供联系人的搜索、添加、删除、更新等业务功能
 */
#[WithMonologChannel(channel: 'wechat_bot')]
#[Autoconfigure(public: true)]
readonly class WeChatContactService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WeChatApiClient $apiClient,
        private LoggerInterface $logger,
        private WeChatContactRepository $contactRepository,
    ) {
    }

    /**
     * 搜索联系人
     */
    public function searchContact(WeChatAccount $account, string $keyword): ?ContactSearchResult
    {
        try {
            $this->validateAccount($account);
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();
            assert(null !== $apiAccount && null !== $deviceId);
            $request = new SearchContactRequest($apiAccount, $deviceId, $keyword);
            $data = $this->apiClient->request($request);
            $this->ensureArrayResponse($data);
            assert(is_array($data));

            /** @var array<string, mixed> $dataTyped */
            $dataTyped = $data;

            return $this->buildContactSearchResult($dataTyped);
        } catch (\Exception $e) {
            $this->logger->error('搜索联系人异常', [
                'device_id' => $account->getDeviceId(),
                'keyword' => $keyword,
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * 获取联系人详细信息
     */
    public function getContactInfo(WeChatAccount $account, string $wxid): ?ContactInfoResult
    {
        try {
            $this->validateAccount($account);
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();
            assert(null !== $apiAccount && null !== $deviceId);
            $request = new GetContactInfoRequest($apiAccount, $deviceId, $wxid);
            $data = $this->apiClient->request($request);
            $this->ensureArrayResponse($data);
            assert(is_array($data));

            /** @var array<string, mixed> $dataTyped */
            $dataTyped = $data;

            return $this->buildContactInfoResult($dataTyped);
        } catch (\Exception $e) {
            $this->logger->error('获取联系人信息异常', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxid,
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * 获取企业微信联系人信息
     */
    public function getEnterpriseContact(WeChatAccount $account, string $wxid): ?ContactInfoResult
    {
        try {
            $this->validateAccount($account);
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();
            assert(null !== $apiAccount && null !== $deviceId);
            $request = new GetEnterpriseContactRequest($apiAccount, $deviceId, $wxid);
            $data = $this->apiClient->request($request);
            $this->ensureArrayResponse($data);
            assert(is_array($data));

            /** @var array<string, mixed> $dataTyped */
            $dataTyped = $data;

            return $this->buildContactInfoResult(
                $dataTyped,
                is_string($dataTyped['corp_name'] ?? null) ? $dataTyped['corp_name'] : '',
                is_string($dataTyped['position'] ?? null) ? $dataTyped['position'] : ''
            );
        } catch (\Exception $e) {
            $this->logger->error('获取企业微信联系人异常', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxid,
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * 添加好友
     */
    public function addFriend(WeChatAccount $account, string $wxid, string $verifyMessage = '', string $addType = 'search'): bool
    {
        try {
            $this->validateAccount($account);
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();
            assert(null !== $apiAccount && null !== $deviceId);
            $request = new AddFriendRequest($apiAccount, $deviceId, $wxid, $verifyMessage, $addType);
            $this->apiClient->request($request);

            $this->logger->info('添加好友成功', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxid,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('添加好友异常', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxid,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * 同意好友添加请求
     */
    public function acceptFriend(WeChatAccount $account, string $wxId, bool $accept = true, string $message = ''): bool
    {
        try {
            $this->validateAccount($account);
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();
            assert(null !== $apiAccount && null !== $deviceId);
            $request = new AcceptFriendRequest($apiAccount, $deviceId, $wxId, $accept, $message);
            $this->apiClient->request($request);

            $this->logger->info('同意好友请求成功', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxId,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('同意好友请求异常', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxId,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * 删除好友
     */
    public function deleteFriend(WeChatAccount $account, string $wxid): bool
    {
        try {
            $this->validateAccount($account);
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();
            assert(null !== $apiAccount && null !== $deviceId);
            $request = new DeleteFriendRequest($apiAccount, $deviceId, $wxid);
            $this->apiClient->request($request);

            $this->logger->info('删除好友成功', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxid,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('删除好友异常', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxid,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * 修改好友备注
     */
    public function updateFriendRemark(WeChatAccount $account, string $wxid, string $remark): bool
    {
        try {
            $this->validateAccount($account);
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();
            assert(null !== $apiAccount && null !== $deviceId);
            $request = new UpdateFriendRemarkRequest($apiAccount, $deviceId, $wxid, $remark);
            $this->apiClient->request($request);

            $this->logger->info('修改好友备注成功', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxid,
                'remark' => $remark,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('修改好友备注异常', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxid,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * 获取自己的微信二维码
     */
    public function getMyQrCode(WeChatAccount $account): ?string
    {
        try {
            $this->validateAccount($account);
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();
            assert(null !== $apiAccount && null !== $deviceId);
            $request = new GetMyQrCodeRequest($apiAccount, $deviceId);
            $data = $this->apiClient->request($request);

            // 确保API响应是数组格式
            if (!is_array($data)) {
                throw new \InvalidArgumentException('Invalid API response format: expected array, got ' . gettype($data));
            }

            $qrCodeUrl = is_string($data['qr_code_url'] ?? null) ? $data['qr_code_url'] : '';

            $this->logger->info('获取个人二维码成功', [
                'device_id' => $account->getDeviceId(),
                'qr_code_url' => $qrCodeUrl,
            ]);

            return $qrCodeUrl;
        } catch (\Exception $e) {
            $this->logger->error('获取个人二维码异常', [
                'device_id' => $account->getDeviceId(),
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * 同步联系人列表到本地数据库
     */
    public function syncContacts(WeChatAccount $account): bool
    {
        try {
            $this->validateAccount($account);
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();
            assert(null !== $apiAccount && null !== $deviceId);
            $request = new GetFriendsAndGroupsRequest($apiAccount, $deviceId);
            $data = $this->apiClient->request($request);

            // 确保API响应是数组格式
            if (!is_array($data)) {
                throw new \InvalidArgumentException('Invalid API response format: expected array, got ' . gettype($data));
            }

            $friends = is_array($data['friends'] ?? null) ? $data['friends'] : [];

            foreach ($friends as $friendData) {
                $this->processFriendData($account, $friendData);
            }

            $this->entityManager->flush();

            $this->logger->info('同步联系人成功', [
                'device_id' => $account->getDeviceId(),
                'contact_count' => count($friends),
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('同步联系人异常', [
                'device_id' => $account->getDeviceId(),
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * 转换性别数字为字符串
     */
    private function convertSexToGender(int $sex): string
    {
        return match ($sex) {
            1 => 'male',
            2 => 'female',
            default => 'unknown',
        };
    }

    /**
     * 构建地区信息
     *
     * @param array<string, mixed> $data
     */
    private function buildRegion(array $data): string
    {
        $parts = array_filter([
            $this->extractString($data, 'country'),
            $this->extractString($data, 'province'),
            $this->extractString($data, 'city'),
        ], static fn (string $v): bool => '' !== $v);

        return implode(' ', $parts);
    }

    /**
     * 根据微信ID获取本地联系人信息
     */
    public function getLocalContact(WeChatAccount $account, string $wxid): ?WeChatContact
    {
        return $this->contactRepository->findOneBy(['account' => $account, 'contactId' => $wxid]);
    }

    /**
     * 获取账号的所有好友
     *
     * @return WeChatContact[]
     */
    public function getAllFriends(WeChatAccount $account): array
    {
        return $this->contactRepository->findBy(['account' => $account, 'contactType' => 'friend', 'isGroup' => false]);
    }

    /**
     * 搜索本地好友
     *
     * @return WeChatContact[]
     */
    public function searchLocalContacts(WeChatAccount $account, string $keyword): array
    {
        $qb = $this->contactRepository->createQueryBuilder('c');

        $result = $qb
            ->where('c.account = :account')
            ->andWhere('c.isFriend = :isFriend')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->like('c.nickname', ':keyword'),
                $qb->expr()->like('c.remark', ':keyword'),
                $qb->expr()->like('c.wxid', ':keyword')
            ))
            ->setParameter('account', $account)
            ->setParameter('isFriend', true)
            ->setParameter('keyword', '%' . $keyword . '%')
            ->getQuery()
            ->getResult()
        ;

        /** @var WeChatContact[] */
        return is_array($result) ? array_filter($result, fn ($item) => $item instanceof WeChatContact) : [];
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
     * 确保API响应是数组格式
     *
     * @param mixed $data
     */
    private function ensureArrayResponse($data): void
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid API response format: expected array, got ' . gettype($data));
        }
    }

    /**
     * 从API数据构建ContactSearchResult
     *
     * @param array<string, mixed> $data
     */
    private function buildContactSearchResult(array $data): ContactSearchResult
    {
        return new ContactSearchResult(
            $this->extractString($data, 'wxid'),
            $this->extractString($data, 'nickname'),
            $this->extractString($data, 'avatar'),
            $this->convertSexToGender($this->extractInt($data, 'sex')),
            $this->extractString($data, 'signature'),
            $this->extractString($data, 'phone'),
            $this->extractString($data, 'city'),
            $this->extractString($data, 'province'),
            $this->extractString($data, 'country')
        );
    }

    /**
     * 从API数据构建ContactInfoResult
     *
     * @param array<string, mixed> $data
     */
    private function buildContactInfoResult(array $data, string $corpName = '', string $position = ''): ContactInfoResult
    {
        return new ContactInfoResult(
            $this->extractString($data, 'wxid'),
            $this->extractString($data, 'nickname'),
            $this->extractString($data, 'avatar'),
            $this->extractString($data, 'remark'),
            $this->extractInt($data, 'sex'),
            $this->extractString($data, 'signature'),
            $this->extractString($data, 'phone'),
            $this->extractString($data, 'city'),
            $this->extractString($data, 'province'),
            $this->extractString($data, 'country'),
            $this->extractStringArray($data, 'tags'),
            (bool) ($data['is_friend'] ?? false),
            $corpName,
            $position
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractString(array $data, string $key): string
    {
        return is_string($data[$key] ?? null) ? $data[$key] : '';
    }

    /**
     * @param array<string, mixed> $data
     */
    private function extractInt(array $data, string $key): int
    {
        return is_int($data[$key] ?? null) ? $data[$key] : 0;
    }

    /**
     * @param array<string, mixed> $data
     * @return string[]
     */
    private function extractStringArray(array $data, string $key): array
    {
        $value = is_array($data[$key] ?? null) ? $data[$key] : [];

        /** @var string[] */
        return array_values(array_filter($value, 'is_string'));
    }

    /**
     * 处理单个好友数据
     *
     * @param mixed $friendData
     */
    private function processFriendData(WeChatAccount $account, mixed $friendData): void
    {
        if (!is_array($friendData)) {
            $this->logger->warning('Invalid friend data format, skipping', ['data' => $friendData]);

            return;
        }

        /** @var array<string, mixed> $friendDataTyped */
        $friendDataTyped = $friendData;
        $contactId = $this->extractString($friendDataTyped, 'wxid');
        if ('' === $contactId) {
            return;
        }

        $contact = $this->findOrCreateContact($account, $contactId);
        $this->updateContactFromData($contact, $friendDataTyped);
    }

    /**
     * 查找或创建联系人
     */
    private function findOrCreateContact(WeChatAccount $account, string $contactId): WeChatContact
    {
        $contact = $this->contactRepository->findOneBy([
            'account' => $account,
            'contactId' => $contactId,
        ]);

        if (null === $contact) {
            $contact = new WeChatContact();
            $contact->setAccount($account);
            $contact->setContactId($contactId);
            $this->entityManager->persist($contact);
        }

        return $contact;
    }

    /**
     * 从数据更新联系人信息
     *
     * @param array<string, mixed> $friendData
     */
    private function updateContactFromData(WeChatContact $contact, array $friendData): void
    {
        $contact->setNickname($this->extractString($friendData, 'nickname'));
        $contact->setRemarkName($this->extractString($friendData, 'remark'));
        $contact->setAvatar($this->extractString($friendData, 'avatar'));
        $contact->setGender($this->convertSexToGender($this->extractInt($friendData, 'sex')));
        $contact->setRegion($this->buildRegion($friendData));
        $contact->setSignature($this->extractString($friendData, 'signature'));
        $contact->setContactType('friend');
    }
}
