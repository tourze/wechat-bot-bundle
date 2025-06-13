<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatContact;
use Tourze\WechatBotBundle\Request\AcceptFriendRequest;
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
class WeChatContactService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WeChatApiClient $apiClient,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * 搜索联系人
     */
    public function searchContact(WeChatAccount $account, string $keyword): ?ContactSearchResult
    {
        try {
            $request = new SearchContactRequest($account->getApiAccount(), $account->getDeviceId(), $keyword);
            $data = $this->apiClient->request($request);

            // ApiClient成功时直接返回data数组，失败时抛出异常
            return new ContactSearchResult(
                $data['wxid'] ?? '',
                $data['nickname'] ?? '',
                $data['avatar'] ?? '',
                $data['sex'] ?? 0,
                $data['signature'] ?? '',
                $data['phone'] ?? '',
                $data['city'] ?? '',
                $data['province'] ?? '',
                $data['country'] ?? ''
            );

        } catch (\Exception $e) {
            $this->logger->error('搜索联系人异常', [
                'device_id' => $account->getDeviceId(),
                'keyword' => $keyword,
                'exception' => $e->getMessage()
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
            $request = new GetContactInfoRequest($account->getApiAccount(), $account->getDeviceId(), $wxid);
            $data = $this->apiClient->request($request);

            return new ContactInfoResult(
                $data['wxid'] ?? '',
                $data['nickname'] ?? '',
                $data['avatar'] ?? '',
                $data['remark'] ?? '',
                $data['sex'] ?? 0,
                $data['signature'] ?? '',
                $data['phone'] ?? '',
                $data['city'] ?? '',
                $data['province'] ?? '',
                $data['country'] ?? '',
                $data['tags'] ?? [],
                (bool) ($data['is_friend'] ?? false)
            );

        } catch (\Exception $e) {
            $this->logger->error('获取联系人信息异常', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxid,
                'exception' => $e->getMessage()
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
            $request = new GetEnterpriseContactRequest($account->getApiAccount(), $account->getDeviceId(), $wxid);
            $data = $this->apiClient->request($request);

            return new ContactInfoResult(
                $data['wxid'] ?? '',
                $data['nickname'] ?? '',
                $data['avatar'] ?? '',
                $data['remark'] ?? '',
                $data['sex'] ?? 0,
                $data['signature'] ?? '',
                $data['phone'] ?? '',
                $data['city'] ?? '',
                $data['province'] ?? '',
                $data['country'] ?? '',
                $data['tags'] ?? [],
                (bool) ($data['is_friend'] ?? false),
                $data['corp_name'] ?? '',
                $data['position'] ?? ''
            );

        } catch (\Exception $e) {
            $this->logger->error('获取企业微信联系人异常', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxid,
                'exception' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * 添加好友
     */
    public function addFriend(WeChatAccount $account, string $wxid, string $verifyMessage = '', string $source = '1'): bool
    {
        try {
            $request = new AddFriendRequest($account->getApiAccount(), $account->getDeviceId(), $wxid, $verifyMessage, $source);
            $this->apiClient->request($request);

            $this->logger->info('添加好友成功', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxid
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('添加好友异常', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxid,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 同意好友添加请求
     */
    public function acceptFriend(WeChatAccount $account, string $encryptUsername, string $ticket, int $type = 3): bool
    {
        try {
            $request = new AcceptFriendRequest($account->getApiAccount(), $account->getDeviceId(), $encryptUsername, $ticket, $type);
            $this->apiClient->request($request);

            $this->logger->info('同意好友请求成功', [
                'device_id' => $account->getDeviceId(),
                'encrypt_username' => $encryptUsername
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('同意好友请求异常', [
                'device_id' => $account->getDeviceId(),
                'encrypt_username' => $encryptUsername,
                'exception' => $e->getMessage()
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
            $request = new DeleteFriendRequest($account->getApiAccount(), $account->getDeviceId(), $wxid);
            $this->apiClient->request($request);

            $this->logger->info('删除好友成功', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxid
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('删除好友异常', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxid,
                'exception' => $e->getMessage()
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
            $request = new UpdateFriendRemarkRequest($account->getApiAccount(), $account->getDeviceId(), $wxid, $remark);
            $this->apiClient->request($request);

            $this->logger->info('修改好友备注成功', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxid,
                'remark' => $remark
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('修改好友备注异常', [
                'device_id' => $account->getDeviceId(),
                'wxid' => $wxid,
                'exception' => $e->getMessage()
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
            $request = new GetMyQrCodeRequest($account->getApiAccount(), $account->getDeviceId());
            $data = $this->apiClient->request($request);

            $qrCodeUrl = $data['qr_code_url'] ?? '';

            $this->logger->info('获取个人二维码成功', [
                'device_id' => $account->getDeviceId(),
                'qr_code_url' => $qrCodeUrl
            ]);

            return $qrCodeUrl;

        } catch (\Exception $e) {
            $this->logger->error('获取个人二维码异常', [
                'device_id' => $account->getDeviceId(),
                'exception' => $e->getMessage()
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
            $request = new GetFriendsAndGroupsRequest($account->getApiAccount(), $account->getDeviceId());
            $data = $this->apiClient->request($request);

            $friends = $data['friends'] ?? [];

            foreach ($friends as $friendData) {
                $contactId = $friendData['wxid'] ?? '';
                if (empty($contactId)) {
                    continue;
                }

                $contact = $this->entityManager->getRepository(WeChatContact::class)
                    ->findOneBy([
                        'account' => $account,
                        'contactId' => $contactId
                    ]);

                if (!$contact) {
                    $contact = new WeChatContact();
                    $contact->setAccount($account);
                    $contact->setContactId($contactId);
                    $this->entityManager->persist($contact);
                }

                $contact->setNickname($friendData['nickname'] ?? '');
                $contact->setRemarkName($friendData['remark'] ?? '');
                $contact->setAvatar($friendData['avatar'] ?? '');
                $contact->setGender($this->convertSexToGender($friendData['sex'] ?? 0));
                $contact->setRegion($this->buildRegion($friendData));
                $contact->setSignature($friendData['signature'] ?? '');
                $contact->setContactType('friend');
            }

            $this->entityManager->flush();

            $this->logger->info('同步联系人成功', [
                'device_id' => $account->getDeviceId(),
                'contact_count' => count($friends)
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('同步联系人异常', [
                'device_id' => $account->getDeviceId(),
                'exception' => $e->getMessage()
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
            default => 'unknown'
        };
    }

    /**
     * 构建地区信息
     */
    private function buildRegion(array $data): string
    {
        $parts = array_filter([
            $data['country'] ?? '',
            $data['province'] ?? '',
            $data['city'] ?? ''
        ]);

        return implode(' ', $parts);
    }

    /**
     * 根据微信ID获取本地联系人信息
     */
    public function getLocalContact(WeChatAccount $account, string $wxid): ?WeChatContact
    {
        return $this->entityManager->getRepository(WeChatContact::class)
            ->findOneBy(['account' => $account, 'contactId' => $wxid]);
    }

    /**
     * 获取账号的所有好友
     */
    public function getAllFriends(WeChatAccount $account): array
    {
        return $this->entityManager->getRepository(WeChatContact::class)
            ->findBy(['account' => $account, 'isFriend' => true, 'isGroup' => false]);
    }

    /**
     * 搜索本地好友
     */
    public function searchLocalContacts(WeChatAccount $account, string $keyword): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        return $qb->select('c')
            ->from(WeChatContact::class, 'c')
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
            ->getResult();
    }
}

/**
 * 联系人搜索结果DTO
 */
class ContactSearchResult
{
    public function __construct(
        public readonly string $wxid,
        public readonly string $nickname,
        public readonly string $avatar,
        public readonly string $sex,
        public readonly string $signature,
        public readonly string $phone,
        public readonly string $city,
        public readonly string $province,
        public readonly string $country
    ) {
    }
}

/**
 * 联系人详情结果DTO
 */
class ContactInfoResult
{
    public function __construct(
        public readonly string $wxid,
        public readonly string $nickname,
        public readonly string $avatar,
        public readonly string $remark,
        public readonly int $sex,
        public readonly string $signature,
        public readonly string $phone,
        public readonly string $city,
        public readonly string $province,
        public readonly string $country,
        public readonly array $tags,
        public readonly bool $isFriend,
        public readonly string $corpName = '',
        public readonly string $position = ''
    ) {
    }
}