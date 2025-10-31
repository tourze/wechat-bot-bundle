<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\DTO\TagResult;
use Tourze\WechatBotBundle\DTO\TagStatResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatContact;
use Tourze\WechatBotBundle\Repository\WeChatContactRepository;
use Tourze\WechatBotBundle\Request\Tag\CreateFriendTagRequest;
use Tourze\WechatBotBundle\Request\Tag\DeleteFriendTagRequest;
use Tourze\WechatBotBundle\Request\Tag\GetTagListRequest;
use Tourze\WechatBotBundle\Request\Tag\UpdateFriendTagRequest;

/**
 * 微信标签管理服务
 *
 * 提供标签创建、修改、删除、管理等业务功能
 */
#[WithMonologChannel(channel: 'wechat_bot')]
#[Autoconfigure(public: true)]
readonly class WeChatTagService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WeChatApiClient $apiClient,
        private LoggerInterface $logger,
        private WeChatContactRepository $contactRepository,
    ) {
    }

    /**
     * 创建好友标签
     */
    public function createFriendTag(WeChatAccount $account, string $tagName): ?TagResult
    {
        try {
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();
            if (null === $apiAccount || null === $deviceId) {
                throw new \InvalidArgumentException('API账号和设备ID不能为null');
            }
            $request = new CreateFriendTagRequest($apiAccount, $deviceId, $tagName);
            $data = $this->apiClient->request($request);

            if (!is_array($data)) {
                throw new \RuntimeException('API响应数据格式错误');
            }

            $tagIdValue = $data['tag_id'] ?? '';
            if (!is_string($tagIdValue) && !is_int($tagIdValue)) {
                throw new \RuntimeException('标签ID格式错误');
            }
            $tagId = (string) $tagIdValue;

            $this->logger->info('创建好友标签成功', [
                'device_id' => $account->getDeviceId(),
                'tag_name' => $tagName,
                'tag_id' => $tagId,
            ]);

            return new TagResult(
                $tagId,
                $tagName,
                0, // 新创建的标签成员数为0
                time()
            );
        } catch (\Exception $e) {
            $this->logger->error('创建好友标签异常', [
                'device_id' => $account->getDeviceId(),
                'tag_name' => $tagName,
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * 修改好友标签
     */
    public function updateFriendTag(WeChatAccount $account, string $tagId, string $newTagName): bool
    {
        try {
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();
            if (null === $apiAccount || null === $deviceId) {
                throw new \InvalidArgumentException('API账号和设备ID不能为null');
            }
            $request = new UpdateFriendTagRequest($apiAccount, $deviceId, $tagId, $newTagName);
            $this->apiClient->request($request);

            $this->logger->info('修改好友标签成功', [
                'device_id' => $account->getDeviceId(),
                'tag_id' => $tagId,
                'new_tag_name' => $newTagName,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('修改好友标签异常', [
                'device_id' => $account->getDeviceId(),
                'tag_id' => $tagId,
                'new_tag_name' => $newTagName,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * 删除好友标签
     */
    public function deleteFriendTag(WeChatAccount $account, string $tagId): bool
    {
        try {
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();
            if (null === $apiAccount || null === $deviceId) {
                throw new \InvalidArgumentException('API账号和设备ID不能为null');
            }
            $request = new DeleteFriendTagRequest($apiAccount, $deviceId, $tagId);
            $this->apiClient->request($request);

            $this->logger->info('删除好友标签成功', [
                'device_id' => $account->getDeviceId(),
                'tag_id' => $tagId,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('删除好友标签异常', [
                'device_id' => $account->getDeviceId(),
                'tag_id' => $tagId,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * 为联系人添加标签
     */
    public function addContactToTag(WeChatAccount $account, WeChatContact $contact, string $tagId): bool
    {
        try {
            // 获取当前联系人的标签列表
            $currentTags = $this->getContactTags($contact);

            // 如果已经有这个标签，则不需要重复添加
            if (in_array($tagId, $currentTags, true)) {
                $this->logger->info('联系人已有此标签', [
                    'device_id' => $account->getDeviceId(),
                    'contact_wxid' => $contact->getContactId(),
                    'tag_id' => $tagId,
                ]);

                return true;
            }

            // 添加新标签
            $newTags = array_merge($currentTags, [$tagId]);
            $contact->setTags(implode(',', $newTags));

            $this->entityManager->flush();

            $this->logger->info('为联系人添加标签成功', [
                'device_id' => $account->getDeviceId(),
                'contact_wxid' => $contact->getContactId(),
                'tag_id' => $tagId,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('为联系人添加标签异常', [
                'device_id' => $account->getDeviceId(),
                'contact_wxid' => $contact->getContactId(),
                'tag_id' => $tagId,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * 获取联系人的标签列表
     * @return string[]
     */
    public function getContactTags(WeChatContact $contact): array
    {
        $tags = $contact->getTags();
        if (null === $tags || '' === $tags) {
            return [];
        }

        return array_filter(explode(',', $tags), fn (string $tag): bool => '' !== trim($tag));
    }

    /**
     * 从联系人移除标签
     */
    public function removeContactFromTag(WeChatAccount $account, WeChatContact $contact, string $tagId): bool
    {
        try {
            // 获取当前联系人的标签列表
            $currentTags = $this->getContactTags($contact);

            // 移除指定标签
            $newTags = array_filter($currentTags, fn (string $tag): bool => $tag !== $tagId);
            $contact->setTags(implode(',', $newTags));

            $this->entityManager->flush();

            $this->logger->info('从联系人移除标签成功', [
                'device_id' => $account->getDeviceId(),
                'contact_wxid' => $contact->getContactId(),
                'tag_id' => $tagId,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('从联系人移除标签异常', [
                'device_id' => $account->getDeviceId(),
                'contact_wxid' => $contact->getContactId(),
                'tag_id' => $tagId,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * 获取标签统计信息
     * @return array<string, mixed>
     */
    /**
     * @return TagStatResult[]
     */
    public function getTagStats(WeChatAccount $account): array
    {
        try {
            $tags = $this->getTagList($account);
            $stats = [];

            foreach ($tags as $tag) {
                $contactCount = count($this->getContactsByTag($account, $tag->tagId));
                $stats[] = new TagStatResult(
                    $tag->tagId,
                    $tag->tagName,
                    $contactCount,
                    $tag->createTime
                );
            }

            return $stats;
        } catch (\Exception $e) {
            $this->logger->error('获取标签统计信息异常', [
                'device_id' => $account->getDeviceId(),
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * 获取标签列表
     * @return TagResult[]
     */
    public function getTagList(WeChatAccount $account): array
    {
        try {
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();
            if (null === $apiAccount || null === $deviceId) {
                throw new \InvalidArgumentException('API账号和设备ID不能为null');
            }
            $request = new GetTagListRequest($apiAccount, $deviceId);
            $data = $this->apiClient->request($request);

            if (!is_array($data)) {
                throw new \RuntimeException('API响应数据格式错误');
            }

            $tagsData = $data['tags'] ?? [];
            if (!is_array($tagsData)) {
                throw new \RuntimeException('标签列表数据格式错误');
            }

            $results = $this->parseTagsData($tagsData);

            $this->logger->info('获取标签列表成功', [
                'device_id' => $account->getDeviceId(),
                'tag_count' => count($results),
            ]);

            return $results;
        } catch (\Exception $e) {
            $this->logger->error('获取标签列表异常', [
                'device_id' => $account->getDeviceId(),
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * 解析标签数据数组
     * @param array<mixed> $tagsData
     * @return TagResult[]
     */
    private function parseTagsData(array $tagsData): array
    {
        $results = [];
        foreach ($tagsData as $tag) {
            $tagResult = $this->parseTagData($tag);
            if (null !== $tagResult) {
                $results[] = $tagResult;
            }
        }

        return $results;
    }

    /**
     * 解析单个标签数据
     * @param mixed $tag
     */
    private function parseTagData(mixed $tag): ?TagResult
    {
        if (!is_array($tag)) {
            return null;
        }

        $tagIdValue = $tag['tag_id'] ?? '';
        $tagNameValue = $tag['tag_name'] ?? '';
        $memberCountValue = $tag['member_count'] ?? 0;
        $createTimeValue = $tag['create_time'] ?? 'now';

        if (!is_string($tagIdValue) && !is_int($tagIdValue)) {
            return null;
        }
        if (!is_string($tagNameValue)) {
            return null;
        }
        if (!is_int($memberCountValue) && !is_string($memberCountValue)) {
            $memberCountValue = 0;
        }
        if (!is_string($createTimeValue)) {
            $createTimeValue = 'now';
        }

        $createTime = strtotime($createTimeValue);
        if (false === $createTime) {
            $createTime = time();
        }

        return new TagResult(
            (string) $tagIdValue,
            $tagNameValue,
            (int) $memberCountValue,
            $createTime
        );
    }

    /**
     * 获取标签下的联系人列表
     * @return array<int, WeChatContact>
     */
    public function getContactsByTag(WeChatAccount $account, string $tagId): array
    {
        try {
            $contacts = $this->contactRepository->createQueryBuilder('c')
                ->where('c.account = :account')
                ->andWhere('c.tags LIKE :tag')
                ->setParameter('account', $account)
                ->setParameter('tag', "%{$tagId}%")
                ->getQuery()
                ->getResult()
            ;

            // 过滤确实包含该标签的联系人（避免子字符串匹配的误报）
            /** @var array<int, WeChatContact> */
            return is_array($contacts) ? array_filter($contacts, function ($contact) use ($tagId): bool {
                return $contact instanceof WeChatContact && in_array($tagId, $this->getContactTags($contact), true);
            }) : [];
        } catch (\Exception $e) {
            $this->logger->error('获取标签下的联系人列表异常', [
                'device_id' => $account->getDeviceId(),
                'tag_id' => $tagId,
                'exception' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
