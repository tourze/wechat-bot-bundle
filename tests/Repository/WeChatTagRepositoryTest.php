<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\TestDox;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Entity\WeChatTag;
use Tourze\WechatBotBundle\Repository\WeChatTagRepository;

/**
 * 微信标签仓储测试
 *
 * 测试微信标签数据访问层的各种查询方法：
 * - 基础查询方法
 * - 按账号过滤查询
 * - 按标签类型过滤查询（系统/自定义）
 * - 按好友过滤查询
 * - 搜索查询
 * - 统计查询
 *
 * @template-extends AbstractRepositoryTestCase<WeChatTag>
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(WeChatTagRepository::class)]
final class WeChatTagRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 此测试类不需要特殊的初始化逻辑
    }

    protected function createNewEntity(): object
    {
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('Test API Account ' . uniqid());
        $apiAccount->setBaseUrl('http://localhost:8080');
        $apiAccount->setUsername('test_user_' . uniqid());
        $apiAccount->setPassword('test_password');
        $apiAccount->setValid(true);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device-' . uniqid());
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $entity = new WeChatTag();
        $entity->setAccount($account);
        $entity->setTagId('test-tag-' . uniqid());
        $entity->setTagName('Test Tag');
        $entity->setFriendCount(5);
        $entity->setSortOrder(1);
        $entity->setValid(true);

        return $entity;
    }

    protected function getRepository(): WeChatTagRepository
    {
        return self::getService(WeChatTagRepository::class);
    }

    private function createApiAccount(): WeChatApiAccount
    {
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('Test API Account ' . uniqid());
        $apiAccount->setBaseUrl('http://localhost:8080');
        $apiAccount->setUsername('test_user_' . uniqid());
        $apiAccount->setPassword('test_password');
        $apiAccount->setValid(true);

        return $apiAccount;
    }

    #[TestDox('按账号查找标签')]
    public function testFindByAccount(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $tag1 = new WeChatTag();
        $tag1->setAccount($account);
        $tag1->setTagId('tag-1');
        $tag1->setTagName('Work');
        $tag1->setSortOrder(10);
        $tag1->setFriendCount(5);
        $tag1->setValid(true);

        $tag2 = new WeChatTag();
        $tag2->setAccount($account);
        $tag2->setTagId('tag-2');
        $tag2->setTagName('Friends');
        $tag2->setSortOrder(20);
        $tag2->setFriendCount(3);
        $tag2->setValid(true);

        $invalidTag = new WeChatTag();
        $invalidTag->setAccount($account);
        $invalidTag->setTagId('invalid-tag');
        $invalidTag->setTagName('Invalid');
        $invalidTag->setSortOrder(15);
        $invalidTag->setFriendCount(0);
        $invalidTag->setValid(false);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($tag1);
        self::getEntityManager()->persist($tag2);
        self::getEntityManager()->persist($invalidTag);
        self::getEntityManager()->flush();

        $tags = $this->getRepository()->findByAccount($account);
        $tagsList = array_values($tags);

        $this->assertCount(2, $tags);
        // 验证按sortOrder DESC, tagName ASC排序
        $this->assertArrayHasKey(0, $tagsList);
        $this->assertInstanceOf(WeChatTag::class, $tagsList[0]);
        $this->assertSame('tag-2', $tagsList[0]->getTagId());
        $this->assertArrayHasKey(1, $tagsList);
        $this->assertInstanceOf(WeChatTag::class, $tagsList[1]);
        $this->assertSame('tag-1', $tagsList[1]->getTagId());
    }

    #[TestDox('根据标签ID查找标签')]
    public function testFindByTagId(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $tag = new WeChatTag();
        $tag->setAccount($account);
        $tag->setTagId('test-tag-123');
        $tag->setTagName('Test Tag');
        $tag->setSortOrder(10);
        $tag->setFriendCount(5);
        $tag->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        $foundTag = $this->getRepository()->findByTagId('test-tag-123');

        $this->assertInstanceOf(WeChatTag::class, $foundTag);
        $this->assertSame('test-tag-123', $foundTag->getTagId());
        $this->assertSame('Test Tag', $foundTag->getTagName());
    }

    #[TestDox('根据标签ID查找不存在的标签返回null')]
    public function testFindByTagIdNotFound(): void
    {
        $foundTag = $this->getRepository()->findByTagId('non-existent-tag');

        $this->assertNull($foundTag);
    }

    #[TestDox('根据账号和标签ID查找标签')]
    public function testFindByAccountAndTagId(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $tag = new WeChatTag();
        $tag->setAccount($account);
        $tag->setTagId('account-tag-123');
        $tag->setTagName('Account Tag');
        $tag->setSortOrder(10);
        $tag->setFriendCount(5);
        $tag->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        $foundTag = $this->getRepository()->findByAccountAndTagId($account, 'account-tag-123');

        $this->assertInstanceOf(WeChatTag::class, $foundTag);
        $this->assertSame('account-tag-123', $foundTag->getTagId());
        $this->assertSame($account, $foundTag->getAccount());
    }

    #[TestDox('根据账号和标签名称查找标签')]
    public function testFindByAccountAndTagName(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $tag = new WeChatTag();
        $tag->setAccount($account);
        $tag->setTagId('name-tag-123');
        $tag->setTagName('Family');
        $tag->setSortOrder(10);
        $tag->setFriendCount(5);
        $tag->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        $foundTag = $this->getRepository()->findByAccountAndTagName($account, 'Family');

        $this->assertInstanceOf(WeChatTag::class, $foundTag);
        $this->assertSame('Family', $foundTag->getTagName());
        $this->assertSame($account, $foundTag->getAccount());
    }

    #[TestDox('根据账号和标签名称查找标签时只返回有效标签')]
    public function testFindByAccountAndTagNameOnlyValid(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $validTag = new WeChatTag();
        $validTag->setAccount($account);
        $validTag->setTagId('valid-tag');
        $validTag->setTagName('Same Name');
        $validTag->setSortOrder(10);
        $validTag->setFriendCount(5);
        $validTag->setValid(true);

        $invalidTag = new WeChatTag();
        $invalidTag->setAccount($account);
        $invalidTag->setTagId('invalid-tag');
        $invalidTag->setTagName('Same Name');
        $invalidTag->setSortOrder(15);
        $invalidTag->setFriendCount(0);
        $invalidTag->setValid(false);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($validTag);
        self::getEntityManager()->persist($invalidTag);
        self::getEntityManager()->flush();

        $foundTag = $this->getRepository()->findByAccountAndTagName($account, 'Same Name');

        $this->assertInstanceOf(WeChatTag::class, $foundTag);
        $this->assertSame('valid-tag', $foundTag->getTagId());
        $this->assertTrue($foundTag->isValid());
    }

    #[TestDox('查找系统标签')]
    public function testFindSystemTags(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $systemTag1 = new WeChatTag();
        $systemTag1->setAccount($account);
        $systemTag1->setTagId('system-tag-1');
        $systemTag1->setTagName('System A');
        $systemTag1->setSortOrder(10);
        $systemTag1->setFriendCount(5);
        $systemTag1->setIsSystem(true);
        $systemTag1->setValid(true);

        $systemTag2 = new WeChatTag();
        $systemTag2->setAccount($account);
        $systemTag2->setTagId('system-tag-2');
        $systemTag2->setTagName('System B');
        $systemTag2->setSortOrder(20);
        $systemTag2->setFriendCount(3);
        $systemTag2->setIsSystem(true);
        $systemTag2->setValid(true);

        $customTag = new WeChatTag();
        $customTag->setAccount($account);
        $customTag->setTagId('custom-tag');
        $customTag->setTagName('Custom');
        $customTag->setSortOrder(15);
        $customTag->setFriendCount(2);
        $customTag->setIsSystem(false);
        $customTag->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($systemTag1);
        self::getEntityManager()->persist($systemTag2);
        self::getEntityManager()->persist($customTag);
        self::getEntityManager()->flush();

        $systemTags = $this->getRepository()->findSystemTags($account);
        $systemTagsList = array_values($systemTags);

        $this->assertCount(2, $systemTags);
        // 验证按sortOrder DESC, tagName ASC排序
        $this->assertArrayHasKey(0, $systemTagsList);
        $this->assertInstanceOf(WeChatTag::class, $systemTagsList[0]);
        $this->assertSame('system-tag-2', $systemTagsList[0]->getTagId());
        $this->assertArrayHasKey(1, $systemTagsList);
        $this->assertInstanceOf(WeChatTag::class, $systemTagsList[1]);
        $this->assertSame('system-tag-1', $systemTagsList[1]->getTagId());
        $this->assertTrue($systemTagsList[0]->isSystem());
        $this->assertTrue($systemTagsList[1]->isSystem());
    }

    #[TestDox('查找自定义标签')]
    public function testFindCustomTags(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $customTag1 = new WeChatTag();
        $customTag1->setAccount($account);
        $customTag1->setTagId('custom-tag-1');
        $customTag1->setTagName('Custom A');
        $customTag1->setSortOrder(10);
        $customTag1->setFriendCount(5);
        $customTag1->setIsSystem(false);
        $customTag1->setValid(true);

        $customTag2 = new WeChatTag();
        $customTag2->setAccount($account);
        $customTag2->setTagId('custom-tag-2');
        $customTag2->setTagName('Custom B');
        $customTag2->setSortOrder(20);
        $customTag2->setFriendCount(3);
        $customTag2->setIsSystem(false);
        $customTag2->setValid(true);

        $systemTag = new WeChatTag();
        $systemTag->setAccount($account);
        $systemTag->setTagId('system-tag');
        $systemTag->setTagName('System');
        $systemTag->setSortOrder(15);
        $systemTag->setFriendCount(2);
        $systemTag->setIsSystem(true);
        $systemTag->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($customTag1);
        self::getEntityManager()->persist($customTag2);
        self::getEntityManager()->persist($systemTag);
        self::getEntityManager()->flush();

        $customTags = $this->getRepository()->findCustomTags($account);
        $customTagsList = array_values($customTags);

        $this->assertCount(2, $customTags);
        // 验证按sortOrder DESC, tagName ASC排序
        $this->assertArrayHasKey(0, $customTagsList);
        $this->assertInstanceOf(WeChatTag::class, $customTagsList[0]);
        $this->assertSame('custom-tag-2', $customTagsList[0]->getTagId());
        $this->assertArrayHasKey(1, $customTagsList);
        $this->assertInstanceOf(WeChatTag::class, $customTagsList[1]);
        $this->assertSame('custom-tag-1', $customTagsList[1]->getTagId());
        $this->assertFalse($customTagsList[0]->isSystem());
        $this->assertFalse($customTagsList[1]->isSystem());
    }

    #[TestDox('根据好友微信ID查找包含该好友的标签')]
    public function testFindTagsByFriend(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $tag1 = new WeChatTag();
        $tag1->setAccount($account);
        $tag1->setTagId('tag-1');
        $tag1->setTagName('Work');
        $tag1->setSortOrder(10);
        $tag1->setFriendCount(2);
        $tag1->setFriendList(['friend-123', 'friend-456']);
        $tag1->setValid(true);

        $tag2 = new WeChatTag();
        $tag2->setAccount($account);
        $tag2->setTagId('tag-2');
        $tag2->setTagName('Family');
        $tag2->setSortOrder(20);
        $tag2->setFriendCount(1);
        $tag2->setFriendList(['friend-123']);
        $tag2->setValid(true);

        $tag3 = new WeChatTag();
        $tag3->setAccount($account);
        $tag3->setTagId('tag-3');
        $tag3->setTagName('Others');
        $tag3->setSortOrder(5);
        $tag3->setFriendCount(1);
        $tag3->setFriendList(['friend-789']);
        $tag3->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($tag1);
        self::getEntityManager()->persist($tag2);
        self::getEntityManager()->persist($tag3);
        self::getEntityManager()->flush();

        $tags = $this->getRepository()->findTagsByFriend($account, 'friend-123');
        $tagsList = array_values($tags);

        $this->assertCount(2, $tags);
        // 验证按sortOrder DESC, tagName ASC排序
        $this->assertArrayHasKey(0, $tagsList);
        $this->assertInstanceOf(WeChatTag::class, $tagsList[0]);
        $this->assertSame('tag-2', $tagsList[0]->getTagId());
        $this->assertArrayHasKey(1, $tagsList);
        $this->assertInstanceOf(WeChatTag::class, $tagsList[1]);
        $this->assertSame('tag-1', $tagsList[1]->getTagId());
    }

    #[TestDox('统计标签数量')]
    public function testCountTags(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建系统标签
        for ($i = 0; $i < 2; ++$i) {
            $systemTag = new WeChatTag();
            $systemTag->setAccount($account);
            $systemTag->setTagId('system-tag-' . $i);
            $systemTag->setTagName('System ' . $i);
            $systemTag->setSortOrder(10 + $i);
            $systemTag->setFriendCount(5);
            $systemTag->setIsSystem(true);
            $systemTag->setValid(true);
            self::getEntityManager()->persist($systemTag);
        }

        // 创建自定义标签
        for ($i = 0; $i < 3; ++$i) {
            $customTag = new WeChatTag();
            $customTag->setAccount($account);
            $customTag->setTagId('custom-tag-' . $i);
            $customTag->setTagName('Custom ' . $i);
            $customTag->setSortOrder(20 + $i);
            $customTag->setFriendCount(3);
            $customTag->setIsSystem(false);
            $customTag->setValid(true);
            self::getEntityManager()->persist($customTag);
        }

        // 创建无效标签
        $invalidTag = new WeChatTag();
        $invalidTag->setAccount($account);
        $invalidTag->setTagId('invalid-tag');
        $invalidTag->setTagName('Invalid');
        $invalidTag->setSortOrder(30);
        $invalidTag->setFriendCount(0);
        $invalidTag->setIsSystem(false);
        $invalidTag->setValid(false);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($invalidTag);
        self::getEntityManager()->flush();

        $totalCount = $this->getRepository()->countTags();
        $accountCount = $this->getRepository()->countTags($account);
        $systemCount = $this->getRepository()->countTags($account, true);
        $customCount = $this->getRepository()->countTags($account, false);

        // 测试期望：当前账号创建的标签(5) + fixtures创建的标签(5) = 10
        $this->assertSame(10, $totalCount);
        $this->assertSame(5, $accountCount); // 当前测试账号的标签数量
        $this->assertSame(2, $systemCount);  // 当前测试账号的系统标签数量
        $this->assertSame(3, $customCount);  // 当前测试账号的自定义标签数量
    }

    #[TestDox('按名称搜索标签')]
    public function testSearchByName(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $tag1 = new WeChatTag();
        $tag1->setAccount($account);
        $tag1->setTagId('tag-1');
        $tag1->setTagName('Work Team');
        $tag1->setSortOrder(10);
        $tag1->setFriendCount(5);
        $tag1->setValid(true);

        $tag2 = new WeChatTag();
        $tag2->setAccount($account);
        $tag2->setTagId('tag-2');
        $tag2->setTagName('Team Lead');
        $tag2->setSortOrder(20);
        $tag2->setFriendCount(3);
        $tag2->setValid(true);

        $tag3 = new WeChatTag();
        $tag3->setAccount($account);
        $tag3->setTagId('tag-3');
        $tag3->setTagName('Family');
        $tag3->setSortOrder(15);
        $tag3->setFriendCount(2);
        $tag3->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($tag1);
        self::getEntityManager()->persist($tag2);
        self::getEntityManager()->persist($tag3);
        self::getEntityManager()->flush();

        $searchResults = $this->getRepository()->searchByName($account, 'Team');

        $this->assertCount(2, $searchResults);
        // 验证按sortOrder DESC, tagName ASC排序
        $this->assertArrayHasKey(0, $searchResults);
        $this->assertInstanceOf(WeChatTag::class, $searchResults[0]);
        $this->assertSame('tag-2', $searchResults[0]->getTagId());
        $this->assertArrayHasKey(1, $searchResults);
        $this->assertInstanceOf(WeChatTag::class, $searchResults[1]);
        $this->assertSame('tag-1', $searchResults[1]->getTagId());
    }

    #[TestDox('按名称搜索标签时只返回有效标签')]
    public function testSearchByNameOnlyValidTags(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $validTag = new WeChatTag();
        $validTag->setAccount($account);
        $validTag->setTagId('valid-tag');
        $validTag->setTagName('Valid Team');
        $validTag->setSortOrder(10);
        $validTag->setFriendCount(5);
        $validTag->setValid(true);

        $invalidTag = new WeChatTag();
        $invalidTag->setAccount($account);
        $invalidTag->setTagId('invalid-tag');
        $invalidTag->setTagName('Invalid Team');
        $invalidTag->setSortOrder(20);
        $invalidTag->setFriendCount(0);
        $invalidTag->setValid(false);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($validTag);
        self::getEntityManager()->persist($invalidTag);
        self::getEntityManager()->flush();

        $searchResults = $this->getRepository()->searchByName($account, 'Team');

        $this->assertCount(1, $searchResults);
        $this->assertArrayHasKey(0, $searchResults);
        $this->assertInstanceOf(WeChatTag::class, $searchResults[0]);
        $this->assertSame('valid-tag', $searchResults[0]->getTagId());
    }

    #[TestDox('查找空标签（没有好友的标签）')]
    public function testFindEmptyTags(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $emptyTag1 = new WeChatTag();
        $emptyTag1->setAccount($account);
        $emptyTag1->setTagId('empty-tag-1');
        $emptyTag1->setTagName('Empty A');
        $emptyTag1->setSortOrder(10);
        $emptyTag1->setFriendCount(0);
        $emptyTag1->setValid(true);

        $emptyTag2 = new WeChatTag();
        $emptyTag2->setAccount($account);
        $emptyTag2->setTagId('empty-tag-2');
        $emptyTag2->setTagName('Empty B');
        $emptyTag2->setSortOrder(20);
        $emptyTag2->setFriendCount(0);
        $emptyTag2->setValid(true);

        $nonEmptyTag = new WeChatTag();
        $nonEmptyTag->setAccount($account);
        $nonEmptyTag->setTagId('non-empty-tag');
        $nonEmptyTag->setTagName('Non Empty');
        $nonEmptyTag->setSortOrder(15);
        $nonEmptyTag->setFriendCount(5);
        $nonEmptyTag->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($emptyTag1);
        self::getEntityManager()->persist($emptyTag2);
        self::getEntityManager()->persist($nonEmptyTag);
        self::getEntityManager()->flush();

        $emptyTags = $this->getRepository()->findEmptyTags($account);

        $this->assertCount(2, $emptyTags);
        // 验证按tagName ASC排序
        $this->assertArrayHasKey(0, $emptyTags);
        $this->assertInstanceOf(WeChatTag::class, $emptyTags[0]);
        $this->assertSame('empty-tag-1', $emptyTags[0]->getTagId());
        $this->assertSame(0, $emptyTags[0]->getFriendCount());
        $this->assertArrayHasKey(1, $emptyTags);
        $this->assertInstanceOf(WeChatTag::class, $emptyTags[1]);
        $this->assertSame('empty-tag-2', $emptyTags[1]->getTagId());
        $this->assertSame(0, $emptyTags[1]->getFriendCount());
    }

    #[TestDox('获取最大排序权重')]
    public function testGetMaxSortOrder(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $tag1 = new WeChatTag();
        $tag1->setAccount($account);
        $tag1->setTagId('tag-1');
        $tag1->setTagName('Tag 1');
        $tag1->setSortOrder(10);
        $tag1->setFriendCount(5);
        $tag1->setValid(true);

        $tag2 = new WeChatTag();
        $tag2->setAccount($account);
        $tag2->setTagId('tag-2');
        $tag2->setTagName('Tag 2');
        $tag2->setSortOrder(25);
        $tag2->setFriendCount(3);
        $tag2->setValid(true);

        $tag3 = new WeChatTag();
        $tag3->setAccount($account);
        $tag3->setTagId('tag-3');
        $tag3->setTagName('Tag 3');
        $tag3->setSortOrder(15);
        $tag3->setFriendCount(2);
        $tag3->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($tag1);
        self::getEntityManager()->persist($tag2);
        self::getEntityManager()->persist($tag3);
        self::getEntityManager()->flush();

        $maxSortOrder = $this->getRepository()->getMaxSortOrder($account);

        $this->assertSame(25, $maxSortOrder);
    }

    #[TestDox('获取最大排序权重时没有标签返回0')]
    public function testGetMaxSortOrderWithNoTags(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $maxSortOrder = $this->getRepository()->getMaxSortOrder($account);

        $this->assertSame(0, $maxSortOrder);
    }

    #[TestDox('不同账号的标签相互独立')]
    public function testTagsAreAccountSpecific(): void
    {
        $apiAccount1 = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount1);
        $apiAccount2 = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount2);

        $account1 = new WeChatAccount();
        $account1->setApiAccount($apiAccount1);
        $account1->setDeviceId('test-device-1');
        $account1->setNickname('Test Account 1');
        $account1->setStatus('online');
        $account1->setValid(true);

        $account2 = new WeChatAccount();
        $account2->setApiAccount($apiAccount2);
        $account2->setDeviceId('test-device-2');
        $account2->setNickname('Test Account 2');
        $account2->setStatus('online');
        $account2->setValid(true);

        $tag1 = new WeChatTag();
        $tag1->setAccount($account1);
        $tag1->setTagId('tag-1');
        $tag1->setTagName('Tag 1');
        $tag1->setSortOrder(10);
        $tag1->setFriendCount(5);
        $tag1->setValid(true);

        $tag2 = new WeChatTag();
        $tag2->setAccount($account2);
        $tag2->setTagId('tag-2');
        $tag2->setTagName('Tag 2');
        $tag2->setSortOrder(20);
        $tag2->setFriendCount(3);
        $tag2->setValid(true);

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->persist($tag1);
        self::getEntityManager()->persist($tag2);
        self::getEntityManager()->flush();

        $account1Tags = $this->getRepository()->findByAccount($account1);
        $account2Tags = $this->getRepository()->findByAccount($account2);

        $this->assertCount(1, $account1Tags);
        $this->assertCount(1, $account2Tags);
        $this->assertArrayHasKey(0, $account1Tags);
        $this->assertInstanceOf(WeChatTag::class, $account1Tags[0]);
        $this->assertSame('tag-1', $account1Tags[0]->getTagId());
        $this->assertArrayHasKey(0, $account2Tags);
        $this->assertInstanceOf(WeChatTag::class, $account2Tags[0]);
        $this->assertSame('tag-2', $account2Tags[0]->getTagId());
    }

    #[TestDox('空数据库时的查询方法')]
    public function testEmptyDatabase(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $this->assertEmpty($this->getRepository()->findByAccount($account));
        $this->assertNull($this->getRepository()->findByTagId('any-tag'));
        $this->assertNull($this->getRepository()->findByAccountAndTagId($account, 'any-tag'));
        $this->assertNull($this->getRepository()->findByAccountAndTagName($account, 'any-name'));
        $this->assertEmpty($this->getRepository()->findSystemTags($account));
        $this->assertEmpty($this->getRepository()->findCustomTags($account));
        $this->assertEmpty($this->getRepository()->findTagsByFriend($account, 'any-friend'));
        $this->assertSame(5, $this->getRepository()->countTags()); // fixtures创建的有效标签数量
        $this->assertSame(0, $this->getRepository()->countTags($account));
        $this->assertEmpty($this->getRepository()->searchByName($account, 'any-keyword'));
        $this->assertEmpty($this->getRepository()->findEmptyTags($account));
        $this->assertSame(0, $this->getRepository()->getMaxSortOrder($account));
    }

    // ================== 基础 Doctrine 方法测试 ==================

    #[TestDox('save方法应持久化新实体')]
    public function testSave(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $tag = new WeChatTag();
        $tag->setAccount($account);
        $tag->setTagId('new-tag');
        $tag->setTagName('New Tag');
        $tag->setSortOrder(10);
        $tag->setFriendCount(5);
        $tag->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $this->getRepository()->save($tag, true);

        $foundTag = $this->getRepository()->findOneBy(['tagId' => 'new-tag']);
        $this->assertInstanceOf(WeChatTag::class, $foundTag);
        $this->assertSame('new-tag', $foundTag->getTagId());
    }

    #[TestDox('remove方法应删除实体')]
    public function testRemove(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $tag = new WeChatTag();
        $tag->setAccount($account);
        $tag->setTagId('to-delete');
        $tag->setTagName('To Delete');
        $tag->setSortOrder(10);
        $tag->setFriendCount(5);
        $tag->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($tag);
        self::getEntityManager()->flush();

        $this->getRepository()->remove($tag, true);

        $foundTag = $this->getRepository()->findOneBy(['tagId' => 'to-delete']);
        $this->assertNull($foundTag);
    }

    // ================== 健壮性测试 ==================

    // ================== 补充缺失的测试 ==================

    #[TestDox('关联查询测试')]
    public function testQueryWithAssociations(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account1 = new WeChatAccount();
        $account1->setApiAccount($apiAccount);
        $account1->setDeviceId('test-device-1');
        $account1->setNickname('Test Account 1');
        $account1->setStatus('online');
        $account1->setValid(true);

        $account2 = new WeChatAccount();
        $account2->setApiAccount($apiAccount);
        $account2->setDeviceId('test-device-2');
        $account2->setNickname('Test Account 2');
        $account2->setStatus('online');
        $account2->setValid(true);

        $tag1 = new WeChatTag();
        $tag1->setAccount($account1);
        $tag1->setTagId('tag-1');
        $tag1->setTagName('Tag 1');
        $tag1->setSortOrder(10);
        $tag1->setFriendCount(5);
        $tag1->setValid(true);

        $tag2 = new WeChatTag();
        $tag2->setAccount($account2);
        $tag2->setTagId('tag-2');
        $tag2->setTagName('Tag 2');
        $tag2->setSortOrder(20);
        $tag2->setFriendCount(3);
        $tag2->setValid(true);

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->persist($tag1);
        self::getEntityManager()->persist($tag2);
        self::getEntityManager()->flush();

        $tagsForAccount1 = $this->getRepository()->findBy(['account' => $account1]);
        $tagsForAccount2 = $this->getRepository()->findBy(['account' => $account2]);

        $this->assertCount(1, $tagsForAccount1);
        $this->assertCount(1, $tagsForAccount2);
        $this->assertArrayHasKey(0, $tagsForAccount1);
        $this->assertInstanceOf(WeChatTag::class, $tagsForAccount1[0]);
        $this->assertSame($account1, $tagsForAccount1[0]->getAccount());
        $this->assertArrayHasKey(0, $tagsForAccount2);
        $this->assertInstanceOf(WeChatTag::class, $tagsForAccount2[0]);
        $this->assertSame($account2, $tagsForAccount2[0]->getAccount());
    }

    #[TestDox('统计关联查询')]
    public function testCountWithAssociations(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account1 = new WeChatAccount();
        $account1->setApiAccount($apiAccount);
        $account1->setDeviceId('test-device-1');
        $account1->setNickname('Test Account 1');
        $account1->setStatus('online');
        $account1->setValid(true);

        $account2 = new WeChatAccount();
        $account2->setApiAccount($apiAccount);
        $account2->setDeviceId('test-device-2');
        $account2->setNickname('Test Account 2');
        $account2->setStatus('online');
        $account2->setValid(true);

        for ($i = 1; $i <= 3; ++$i) {
            $tag = new WeChatTag();
            $tag->setAccount($account1);
            $tag->setTagId('tag-acc1-' . $i);
            $tag->setTagName('Tag Acc1 ' . $i);
            $tag->setSortOrder(10 + $i);
            $tag->setFriendCount(5);
            $tag->setValid(true);
            self::getEntityManager()->persist($tag);
        }

        for ($i = 1; $i <= 2; ++$i) {
            $tag = new WeChatTag();
            $tag->setAccount($account2);
            $tag->setTagId('tag-acc2-' . $i);
            $tag->setTagName('Tag Acc2 ' . $i);
            $tag->setSortOrder(20 + $i);
            $tag->setFriendCount(3);
            $tag->setValid(true);
            self::getEntityManager()->persist($tag);
        }

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->flush();

        $account1Count = $this->getRepository()->count(['account' => $account1]);
        $account2Count = $this->getRepository()->count(['account' => $account2]);

        $this->assertSame(3, $account1Count);
        $this->assertSame(2, $account2Count);
    }

    #[TestDox('查询可空字段为NULL的记录')]
    public function testFindByNullableFields(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $tagWithFriendList = new WeChatTag();
        $tagWithFriendList->setAccount($account);
        $tagWithFriendList->setTagId('tag-with-list');
        $tagWithFriendList->setTagName('Tag With List');
        $tagWithFriendList->setSortOrder(10);
        $tagWithFriendList->setFriendCount(2);
        $tagWithFriendList->setFriendList(['friend1', 'friend2']);
        $tagWithFriendList->setValid(true);

        $tagWithoutFriendList = new WeChatTag();
        $tagWithoutFriendList->setAccount($account);
        $tagWithoutFriendList->setTagId('tag-without-list');
        $tagWithoutFriendList->setTagName('Tag Without List');
        $tagWithoutFriendList->setSortOrder(20);
        $tagWithoutFriendList->setFriendCount(0);
        $tagWithoutFriendList->setFriendList(null);
        $tagWithoutFriendList->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($tagWithFriendList);
        self::getEntityManager()->persist($tagWithoutFriendList);
        self::getEntityManager()->flush();

        $tagsWithoutList = $this->getRepository()->findBy(['friendList' => null]);

        // 期望2个：fixtures中的"空标签" + 当前测试创建的标签
        $this->assertCount(2, $tagsWithoutList);

        // 验证包含我们测试创建的标签
        $testTagIds = array_map(fn ($tag) => $tag->getTagId(), $tagsWithoutList);
        $this->assertContains('tag-without-list', $testTagIds);

        // 验证所有标签的friendList都为null
        foreach ($tagsWithoutList as $tag) {
            $this->assertNull($tag->getFriendList());
        }
    }

    #[TestDox('统计可空字段为NULL的记录数量')]
    public function testCountByNullableFields(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        for ($i = 1; $i <= 2; ++$i) {
            $tag = new WeChatTag();
            $tag->setAccount($account);
            $tag->setTagId('with-list-' . $i);
            $tag->setTagName('With List ' . $i);
            $tag->setSortOrder(10 + $i);
            $tag->setFriendCount(1);
            $tag->setFriendList(['friend' . $i]);
            $tag->setValid(true);
            self::getEntityManager()->persist($tag);
        }

        for ($i = 1; $i <= 3; ++$i) {
            $tag = new WeChatTag();
            $tag->setAccount($account);
            $tag->setTagId('without-list-' . $i);
            $tag->setTagName('Without List ' . $i);
            $tag->setSortOrder(20 + $i);
            $tag->setFriendCount(0);
            $tag->setFriendList(null);
            $tag->setValid(true);
            self::getEntityManager()->persist($tag);
        }

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $countWithoutList = $this->getRepository()->count(['friendList' => null]);

        // 期望4个：fixtures中的"空标签" + 当前测试创建的3个标签
        $this->assertSame(4, $countWithoutList);
    }

    // ================== 完整的可空字段测试 ==================

    #[TestDox('查询所有可空字段为NULL的记录')]
    public function testFindByAllNullableFields(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建有颜色的标签
        $tagWithColor = new WeChatTag();
        $tagWithColor->setAccount($account);
        $tagWithColor->setTagId('with-color');
        $tagWithColor->setTagName('Tag With Color');
        $tagWithColor->setColor('red');
        $tagWithColor->setSortOrder(10);
        $tagWithColor->setValid(true);

        // 创建没有颜色的标签
        $tagWithoutColor = new WeChatTag();
        $tagWithoutColor->setAccount($account);
        $tagWithoutColor->setTagId('without-color');
        $tagWithoutColor->setTagName('Tag Without Color');
        $tagWithoutColor->setColor(null);
        $tagWithoutColor->setSortOrder(20);
        $tagWithoutColor->setValid(true);

        // 创建有好友列表的标签
        $tagWithFriendList = new WeChatTag();
        $tagWithFriendList->setAccount($account);
        $tagWithFriendList->setTagId('with-friend-list');
        $tagWithFriendList->setTagName('Tag With Friend List');
        $tagWithFriendList->setFriendList(['friend1', 'friend2']);
        $tagWithFriendList->setSortOrder(30);
        $tagWithFriendList->setValid(true);

        // 创建有备注的标签
        $tagWithRemark = new WeChatTag();
        $tagWithRemark->setAccount($account);
        $tagWithRemark->setTagId('with-remark');
        $tagWithRemark->setTagName('Tag With Remark');
        $tagWithRemark->setRemark('Test remark');
        $tagWithRemark->setSortOrder(40);
        $tagWithRemark->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($tagWithColor);
        self::getEntityManager()->persist($tagWithoutColor);
        self::getEntityManager()->persist($tagWithFriendList);
        self::getEntityManager()->persist($tagWithRemark);
        self::getEntityManager()->flush();

        // 测试查询颜色为NULL的标签
        $tagsWithoutColor = $this->getRepository()->findBy(['color' => null]);
        $this->assertCount(4, $tagsWithoutColor); // fixtures中1个 + 当前测试的3个

        // 测试查询好友列表为NULL的标签
        $tagsWithoutFriendList = $this->getRepository()->findBy(['friendList' => null]);
        $this->assertCount(4, $tagsWithoutFriendList); // fixtures中1个 + 当前测试的3个

        // 测试查询备注为NULL的标签
        $tagsWithoutRemark = $this->getRepository()->findBy(['remark' => null]);
        $this->assertCount(4, $tagsWithoutRemark); // fixtures中1个 + 当前测试的3个
    }

    #[TestDox('统计所有可空字段为NULL的记录数量')]
    public function testCountByAllNullableFields(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建具有各种可空字段值的标签
        $tagFull = new WeChatTag();
        $tagFull->setAccount($account);
        $tagFull->setTagId('full-tag');
        $tagFull->setTagName('Full Tag');
        $tagFull->setColor('blue');
        $tagFull->setFriendList(['friend1', 'friend2', 'friend3']);
        $tagFull->setRemark('test-remark');
        $tagFull->setSortOrder(10);
        $tagFull->setValid(true);

        // 创建空字段标签
        $tagEmpty = new WeChatTag();
        $tagEmpty->setAccount($account);
        $tagEmpty->setTagId('empty-tag');
        $tagEmpty->setTagName('Empty Tag');
        $tagEmpty->setSortOrder(20);
        $tagEmpty->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($tagFull);
        self::getEntityManager()->persist($tagEmpty);
        self::getEntityManager()->flush();

        // 统计各个可空字段为NULL的记录数量（包含fixtures数据）
        $this->assertSame(2, $this->getRepository()->count(['color' => null])); // fixtures中1个 + 当前测试1个
        $this->assertSame(2, $this->getRepository()->count(['friendList' => null])); // fixtures中1个 + 当前测试1个
        $this->assertSame(2, $this->getRepository()->count(['remark' => null])); // fixtures中1个 + 当前测试1个
    }

    // ================== findOneBy 排序测试 ==================

    #[TestDox('findOneBy应遵循排序参数')]
    public function testFindOneByShouldRespectOrderByClause(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $tag1 = new WeChatTag();
        $tag1->setAccount($account);
        $tag1->setTagId('tag-1');
        $tag1->setTagName('Tag B');
        $tag1->setSortOrder(20);
        $tag1->setValid(true);

        $tag2 = new WeChatTag();
        $tag2->setAccount($account);
        $tag2->setTagId('tag-2');
        $tag2->setTagName('Tag A');
        $tag2->setSortOrder(10);
        $tag2->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($tag1);
        self::getEntityManager()->persist($tag2);
        self::getEntityManager()->flush();

        // 按标签名称升序查询，应返回第一个匹配的记录（限制到当前测试账号）
        $tag = $this->getRepository()->findOneBy(
            ['valid' => true, 'account' => $account],
            ['tagName' => 'ASC']
        );

        $this->assertInstanceOf(WeChatTag::class, $tag);
        $this->assertSame('Tag A', $tag->getTagName());

        // 按标签名称降序查询，应返回第一个匹配的记录（限制到当前测试账号）
        $tagDesc = $this->getRepository()->findOneBy(
            ['valid' => true, 'account' => $account],
            ['tagName' => 'DESC']
        );

        $this->assertInstanceOf(WeChatTag::class, $tagDesc);
        $this->assertSame('Tag B', $tagDesc->getTagName());

        // 按排序权重升序查询（限制到当前测试账号）
        $tagBySortOrder = $this->getRepository()->findOneBy(
            ['valid' => true, 'account' => $account],
            ['sortOrder' => 'ASC']
        );

        $this->assertInstanceOf(WeChatTag::class, $tagBySortOrder);
        $this->assertSame('Tag A', $tagBySortOrder->getTagName()); // 排序权重10

        // 按排序权重降序查询（限制到当前测试账号）
        $tagBySortOrderDesc = $this->getRepository()->findOneBy(
            ['valid' => true, 'account' => $account],
            ['sortOrder' => 'DESC']
        );

        $this->assertInstanceOf(WeChatTag::class, $tagBySortOrderDesc);
        $this->assertSame('Tag B', $tagBySortOrderDesc->getTagName()); // 排序权重20
    }
}
