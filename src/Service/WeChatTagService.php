<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
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
class WeChatTagService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WeChatApiClient $apiClient,
        private readonly LoggerInterface $logger,
        private readonly WeChatContactRepository $contactRepository
    ) {}

    /**
     * 创建好友标签
     */
    public function createFriendTag(WeChatAccount $account, string $tagName): ?TagResult
    {
        try {
            $request = new CreateFriendTagRequest($account->getApiAccount(), $account->getDeviceId(), $tagName);
            $data = $this->apiClient->request($request);
            $tagId = $data['tag_id'] ?? '';

            $this->logger->info('创建好友标签成功', [
                'device_id' => $account->getDeviceId(),
                'tag_name' => $tagName,
                'tag_id' => $tagId
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
                'exception' => $e->getMessage()
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
            $request = new UpdateFriendTagRequest($account->getApiAccount(), $account->getDeviceId(), $tagId, $newTagName);
            $this->apiClient->request($request);

            $this->logger->info('修改好友标签成功', [
                'device_id' => $account->getDeviceId(),
                'tag_id' => $tagId,
                'new_tag_name' => $newTagName
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('修改好友标签异常', [
                'device_id' => $account->getDeviceId(),
                'tag_id' => $tagId,
                'new_tag_name' => $newTagName,
                'exception' => $e->getMessage()
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
            $request = new DeleteFriendTagRequest($account->getApiAccount(), $account->getDeviceId(), $tagId);
            $this->apiClient->request($request);

            $this->logger->info('删除好友标签成功', [
                'device_id' => $account->getDeviceId(),
                'tag_id' => $tagId
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('删除好友标签异常', [
                'device_id' => $account->getDeviceId(),
                'tag_id' => $tagId,
                'exception' => $e->getMessage()
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
            if ((bool) in_array($tagId, $currentTags, true)) {
                $this->logger->info('联系人已有此标签', [
                    'device_id' => $account->getDeviceId(),
                    'contact_wxid' => $contact->getContactId(),
                    'tag_id' => $tagId
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
                'tag_id' => $tagId
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('为联系人添加标签异常', [
                'device_id' => $account->getDeviceId(),
                'contact_wxid' => $contact->getContactId(),
                'tag_id' => $tagId,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 获取联系人的标签列表
     */
    public function getContactTags(WeChatContact $contact): array
    {
        $tags = $contact->getTags();
        if ((bool) empty($tags)) {
            return [];
        }

        return array_filter(explode(',', $tags));
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
            $newTags = array_filter($currentTags, fn($tag) => $tag !== $tagId);
            $contact->setTags(implode(',', $newTags));

            $this->entityManager->flush();

            $this->logger->info('从联系人移除标签成功', [
                'device_id' => $account->getDeviceId(),
                'contact_wxid' => $contact->getContactId(),
                'tag_id' => $tagId
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('从联系人移除标签异常', [
                'device_id' => $account->getDeviceId(),
                'contact_wxid' => $contact->getContactId(),
                'tag_id' => $tagId,
                'exception' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 获取标签统计信息
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
                'exception' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * 获取标签列表
     */
    public function getTagList(WeChatAccount $account): array
    {
        try {
            $request = new GetTagListRequest($account->getApiAccount(), $account->getDeviceId());
            $data = $this->apiClient->request($request);
            $tags = $data['tags'] ?? [];

            $results = [];
            foreach ($tags as $tag) {
                $results[] = new TagResult(
                    $tag['tag_id'] ?? '',
                    $tag['tag_name'] ?? '',
                    $tag['member_count'] ?? 0,
                    strtotime($tag['create_time'] ?? 'now')
                );
            }

            $this->logger->info('获取标签列表成功', [
                'device_id' => $account->getDeviceId(),
                'tag_count' => count($results)
            ]);

            return $results;
        } catch (\Exception $e) {
            $this->logger->error('获取标签列表异常', [
                'device_id' => $account->getDeviceId(),
                'exception' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * 获取标签下的联系人列表
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
                ->getResult();

            // 过滤确实包含该标签的联系人（避免子字符串匹配的误报）
            return array_filter($contacts, function (WeChatContact $contact) use ($tagId) {
                return in_array($tagId, $this->getContactTags($contact), true);
            });
        } catch (\Exception $e) {
            $this->logger->error('获取标签下的联系人列表异常', [
                'device_id' => $account->getDeviceId(),
                'tag_id' => $tagId,
                'exception' => $e->getMessage()
            ]);
            return [];
        }
    }
}

/**
 * 标签结果DTO
 */
class TagResult
{
    public function __construct(
        public readonly string $tagId,
        public readonly string $tagName,
        public readonly int $memberCount,
        public readonly int $createTime
    ) {}

    /**
     * 获取格式化的创建时间
     */
    public function getFormattedCreateTime(): string
    {
        return date('Y-m-d H:i:s', $this->createTime);
    }
}

/**
 * 标签统计结果DTO
 */
class TagStatResult
{
    public function __construct(
        public readonly string $tagId,
        public readonly string $tagName,
        public readonly int $contactCount,
        public readonly int $createTime
    ) {}

    /**
     * 获取格式化的创建时间
     */
    public function getFormattedCreateTime(): string
    {
        return date('Y-m-d H:i:s', $this->createTime);
    }
}
