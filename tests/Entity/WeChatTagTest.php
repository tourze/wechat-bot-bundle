<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatTag;

/**
 * WeChatTag 实体单元测试
 *
 * @internal
 */
#[CoversClass(WeChatTag::class)]
final class WeChatTagTest extends AbstractEntityTestCase
{
    private WeChatAccount $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = new WeChatAccount();
    }

    public function testConstructorSetsDefaultValues(): void
    {
        $tag = new WeChatTag();

        // 验证默认值
        $this->assertNull($tag->getId());
        $this->assertNull($tag->getAccount());
        $this->assertNull($tag->getTagId());
        $this->assertNull($tag->getTagName());
        $this->assertNull($tag->getColor());
        $this->assertEquals(0, $tag->getFriendCount());
        $this->assertNull($tag->getFriendList());
        $this->assertEquals(0, $tag->getSortOrder());
        $this->assertFalse($tag->isSystem());
        $this->assertTrue($tag->isValid());
        $this->assertNull($tag->getRemark());
    }

    public function testSettersAndGettersWorkCorrectly(): void
    {
        $tag = new WeChatTag();

        // 测试账号
        $tag->setAccount($this->account);
        $this->assertSame($this->account, $tag->getAccount());

        // 测试标签ID
        $tagId = 'tag_123';
        $tag->setTagId($tagId);
        $this->assertEquals($tagId, $tag->getTagId());

        // 测试标签名称
        $tagName = 'Test Tag';
        $tag->setTagName($tagName);
        $this->assertEquals($tagName, $tag->getTagName());

        // 测试颜色
        $color = '#FF0000';
        $tag->setColor($color);
        $this->assertEquals($color, $tag->getColor());

        // 测试好友数量
        $friendCount = 10;
        $tag->setFriendCount($friendCount);
        $this->assertEquals($friendCount, $tag->getFriendCount());

        // 测试好友列表
        $friendList = ['friend1', 'friend2', 'friend3'];
        $tag->setFriendList($friendList);
        $this->assertEquals($friendList, $tag->getFriendList());

        // 测试排序权重
        $sortOrder = 100;
        $tag->setSortOrder($sortOrder);
        $this->assertEquals($sortOrder, $tag->getSortOrder());

        // 测试是否为系统标签
        $tag->setIsSystem(true);
        $this->assertTrue($tag->isSystem());

        // 测试有效性
        $tag->setValid(false);
        $this->assertFalse($tag->isValid());

        // 测试备注
        $remark = 'Test remark';
        $tag->setRemark($remark);
        $this->assertEquals($remark, $tag->getRemark());
    }

    public function testSetFriendListAutomaticallyUpdatesFriendCount(): void
    {
        $tag = new WeChatTag();
        $friendList = ['friend1', 'friend2', 'friend3'];

        $tag->setFriendList($friendList);

        $this->assertEquals(3, $tag->getFriendCount());
        $this->assertEquals($friendList, $tag->getFriendList());
    }

    public function testSetFriendListWithNullSetsCountToZero(): void
    {
        $tag = new WeChatTag();
        $tag->setFriendList(['friend1', 'friend2']);
        $this->assertEquals(2, $tag->getFriendCount());

        $tag->setFriendList(null);
        $this->assertEquals(0, $tag->getFriendCount());
        $this->assertNull($tag->getFriendList());
    }

    public function testSetFriendListWithEmptyArraySetsCountToZero(): void
    {
        $tag = new WeChatTag();
        $tag->setFriendList(['friend1', 'friend2']);
        $this->assertEquals(2, $tag->getFriendCount());

        $tag->setFriendList([]);
        $this->assertEquals(0, $tag->getFriendCount());
        $this->assertEquals([], $tag->getFriendList());
    }

    public function testToStringReturnsTagNameAndFriendCount(): void
    {
        $tag = new WeChatTag();
        $tag->setTagName('Test Tag');
        $tag->setFriendCount(5);

        $result = (string) $tag;
        $this->assertStringContainsString('Test Tag', $result);
        $this->assertStringContainsString('5人', $result);
    }

    public function testToStringFallsBackToTagIdWhenNoTagName(): void
    {
        $tag = new WeChatTag();
        $tag->setTagId('tag_123');
        $tag->setFriendCount(3);

        $result = (string) $tag;
        $this->assertStringContainsString('tag_123', $result);
        $this->assertStringContainsString('3人', $result);
    }

    public function testAddFriendAddsNewFriend(): void
    {
        $tag = new WeChatTag();
        $tag->addFriend('friend1');

        $this->assertEquals(1, $tag->getFriendCount());
        $this->assertEquals(['friend1'], $tag->getFriendList());
    }

    public function testAddFriendDoesNotAddDuplicateFriend(): void
    {
        $tag = new WeChatTag();
        $tag->addFriend('friend1');
        $tag->addFriend('friend1');

        $this->assertEquals(1, $tag->getFriendCount());
        $this->assertEquals(['friend1'], $tag->getFriendList());
    }

    public function testAddFriendAddsMultipleFriends(): void
    {
        $tag = new WeChatTag();
        $tag->addFriend('friend1');
        $tag->addFriend('friend2');
        $tag->addFriend('friend3');

        $this->assertEquals(3, $tag->getFriendCount());
        $this->assertEquals(['friend1', 'friend2', 'friend3'], $tag->getFriendList());
    }

    public function testRemoveFriendRemovesExistingFriend(): void
    {
        $tag = new WeChatTag();
        $tag->setFriendList(['friend1', 'friend2', 'friend3']);

        $tag->removeFriend('friend2');

        $this->assertEquals(2, $tag->getFriendCount());
        $this->assertEquals(['friend1', 'friend3'], $tag->getFriendList());
    }

    public function testRemoveFriendDoesNothingForNonExistentFriend(): void
    {
        $tag = new WeChatTag();
        $tag->setFriendList(['friend1', 'friend2']);

        $tag->removeFriend('friend3');

        $this->assertEquals(2, $tag->getFriendCount());
        $this->assertEquals(['friend1', 'friend2'], $tag->getFriendList());
    }

    public function testRemoveFriendFromEmptyList(): void
    {
        $tag = new WeChatTag();
        $tag->removeFriend('friend1');

        $this->assertEquals(0, $tag->getFriendCount());
        // 当没有找到要删除的好友时，friendList可能仍然是null
        $this->assertTrue(null === $tag->getFriendList() || [] === $tag->getFriendList());
    }

    public function testHasFriendReturnsTrueForExistingFriend(): void
    {
        $tag = new WeChatTag();
        $tag->setFriendList(['friend1', 'friend2', 'friend3']);

        $this->assertTrue($tag->hasFriend('friend2'));
    }

    public function testHasFriendReturnsFalseForNonExistentFriend(): void
    {
        $tag = new WeChatTag();
        $tag->setFriendList(['friend1', 'friend2']);

        $this->assertFalse($tag->hasFriend('friend3'));
    }

    public function testHasFriendReturnsFalseForEmptyList(): void
    {
        $tag = new WeChatTag();
        $this->assertFalse($tag->hasFriend('friend1'));
    }

    public function testClearFriendsClearsAllFriends(): void
    {
        $tag = new WeChatTag();
        $tag->setFriendList(['friend1', 'friend2', 'friend3']);
        $this->assertEquals(3, $tag->getFriendCount());

        $tag->clearFriends();

        $this->assertEquals(0, $tag->getFriendCount());
        $this->assertEquals([], $tag->getFriendList());
    }

    public function testIncrementFriendCountIncrementsCount(): void
    {
        $tag = new WeChatTag();
        $initialCount = $tag->getFriendCount();

        $tag->incrementFriendCount();
        $this->assertEquals($initialCount + 1, $tag->getFriendCount());

        $tag->incrementFriendCount();
        $this->assertEquals($initialCount + 2, $tag->getFriendCount());
    }

    public function testDecrementFriendCountDecrementsCount(): void
    {
        $tag = new WeChatTag();
        $tag->setFriendCount(5);

        $tag->decrementFriendCount();
        $this->assertEquals(4, $tag->getFriendCount());

        $tag->decrementFriendCount();
        $this->assertEquals(3, $tag->getFriendCount());
    }

    public function testDecrementFriendCountNeverGoesBelowZero(): void
    {
        $tag = new WeChatTag();
        $tag->setFriendCount(1);

        $tag->decrementFriendCount();
        $this->assertEquals(0, $tag->getFriendCount());

        $tag->decrementFriendCount();
        $this->assertEquals(0, $tag->getFriendCount());
    }

    public function testIsEmptyReturnsTrueWhenFriendCountIsZero(): void
    {
        $tag = new WeChatTag();
        $this->assertTrue($tag->isEmpty());

        $tag->setFriendCount(0);
        $this->assertTrue($tag->isEmpty());
    }

    public function testIsEmptyReturnsFalseWhenFriendCountIsNotZero(): void
    {
        $tag = new WeChatTag();
        $tag->setFriendCount(1);

        $this->assertFalse($tag->isEmpty());
    }

    public function testSetterMethods(): void
    {
        $tag = new WeChatTag();

        $tag->setAccount($this->account);
        $tag->setTagId('tag_123');
        $tag->setTagName('Test Tag');
        $tag->setColor('#FF0000');
        $tag->setFriendList(['friend1', 'friend2']); // friendCount will be automatically set to 2
        $tag->setSortOrder(100);
        $tag->setIsSystem(true);
        $tag->setValid(true);
        $tag->setRemark('Test remark');

        // Verify all values are set correctly
        $this->assertSame($this->account, $tag->getAccount());
        $this->assertEquals('tag_123', $tag->getTagId());
        $this->assertEquals('Test Tag', $tag->getTagName());
        $this->assertEquals('#FF0000', $tag->getColor());
        $this->assertEquals(2, $tag->getFriendCount()); // Auto-calculated from friendList
        $this->assertEquals(['friend1', 'friend2'], $tag->getFriendList());
        $this->assertEquals(100, $tag->getSortOrder());
        $this->assertTrue($tag->isSystem());
        $this->assertTrue($tag->isValid());
        $this->assertEquals('Test remark', $tag->getRemark());
    }

    public function testBusinessMethods(): void
    {
        $tag = new WeChatTag();
        $tag->setFriendCount(3);
        $tag->setFriendList(['friend1', 'friend2']);

        // Test addFriend
        $friendList = $tag->getFriendList();
        $originalCount = count($friendList ?? []);
        $tag->addFriend('friend3');
        $this->assertContains('friend3', $tag->getFriendList() ?? []);
        $currentFriendList = $tag->getFriendList();
        $this->assertEquals($originalCount + 1, count($currentFriendList));

        // Test removeFriend
        $tag->removeFriend('friend1');
        $this->assertNotContains('friend1', $tag->getFriendList() ?? []);

        // Test clearFriends
        $tag->clearFriends();
        $this->assertEmpty($tag->getFriendList() ?? []);
        $this->assertEquals(0, $tag->getFriendCount());

        // Test incrementFriendCount
        $tag->setFriendCount(5);
        $originalCount = $tag->getFriendCount();
        $tag->incrementFriendCount();
        $this->assertEquals($originalCount + 1, $tag->getFriendCount());

        // Test decrementFriendCount
        $tag->decrementFriendCount();
        $this->assertEquals($originalCount, $tag->getFriendCount());
    }

    public function testFriendManagement(): void
    {
        $tag = new WeChatTag();

        $tag->addFriend('friend1');
        $tag->addFriend('friend2');
        $tag->addFriend('friend3');
        $tag->removeFriend('friend2');

        $this->assertEquals(2, $tag->getFriendCount());
        $this->assertEquals(['friend1', 'friend3'], $tag->getFriendList());
        $this->assertTrue($tag->hasFriend('friend1'));
        $this->assertFalse($tag->hasFriend('friend2'));
        $this->assertTrue($tag->hasFriend('friend3'));
    }

    public function testComplexFriendOperations(): void
    {
        $tag = new WeChatTag();

        // 添加好友
        $tag->addFriend('friend1');
        $tag->addFriend('friend2');
        $tag->addFriend('friend3');

        $this->assertEquals(3, $tag->getFriendCount());

        // 移除中间的好友
        $tag->removeFriend('friend2');
        $this->assertEquals(2, $tag->getFriendCount());
        $this->assertEquals(['friend1', 'friend3'], $tag->getFriendList());

        // 清空所有好友
        $tag->clearFriends();
        $this->assertEquals(0, $tag->getFriendCount());
        $this->assertEquals([], $tag->getFriendList());
        $this->assertTrue($tag->isEmpty());
    }

    public function testSetNullValuesWorkCorrectly(): void
    {
        $tag = new WeChatTag();

        // 测试可以设置为null的字段
        $tag->setAccount(null);
        $this->assertNull($tag->getAccount());

        $tag->setColor(null);
        $this->assertNull($tag->getColor());

        $tag->setFriendList(null);
        $this->assertNull($tag->getFriendList());

        $tag->setRemark(null);
        $this->assertNull($tag->getRemark());
    }

    public function testFriendListTypeStrict(): void
    {
        $tag = new WeChatTag();
        $tag->setFriendList(['friend1', 'friend2']);

        // 测试严格类型检查
        $this->assertTrue($tag->hasFriend('friend1'));
        $this->assertFalse($tag->hasFriend('friend3'));

        // 测试数组键重新索引
        $tag->removeFriend('friend1');
        $friendList = $tag->getFriendList();
        $this->assertEquals(['friend2'], $friendList);
        $this->assertEquals([0 => 'friend2'], $friendList); // 确保数组键被重新索引
    }

    public function testAddFriendToExistingList(): void
    {
        $tag = new WeChatTag();
        $tag->setFriendList(['friend1', 'friend2']);

        $tag->addFriend('friend3');

        $this->assertEquals(3, $tag->getFriendCount());
        $this->assertEquals(['friend1', 'friend2', 'friend3'], $tag->getFriendList());
    }

    public function testIsSystemFlagBehavior(): void
    {
        $tag = new WeChatTag();

        // 默认不是系统标签
        $this->assertFalse($tag->isSystem());

        // 设置为系统标签
        $tag->setIsSystem(true);
        $this->assertTrue($tag->isSystem());

        // 设置为非系统标签
        $tag->setIsSystem(false);
        $this->assertFalse($tag->isSystem());
    }

    public function testSortOrderBehavior(): void
    {
        $tag = new WeChatTag();

        // 默认排序权重为0
        $this->assertEquals(0, $tag->getSortOrder());

        // 设置正数排序权重
        $tag->setSortOrder(100);
        $this->assertEquals(100, $tag->getSortOrder());

        // 设置负数排序权重
        $tag->setSortOrder(-10);
        $this->assertEquals(-10, $tag->getSortOrder());
    }

    protected function createEntity(): WeChatTag
    {
        return new WeChatTag();
    }

    /**
     * 提供 WeChatTag 实体的属性数据进行自动测试。
     *
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'tagId' => ['tagId', 'tag_123'];
        yield 'tagName' => ['tagName', 'Test Tag'];
        yield 'color' => ['color', '#FF0000'];
        yield 'friendCount' => ['friendCount', 10];
        yield 'friendList' => ['friendList', ['friend1', 'friend2', 'friend3']];
        yield 'sortOrder' => ['sortOrder', 100];
        // 注意：isSystem, valid 属性有特殊的 getter 方法，暂时跳过自动测试
        yield 'remark' => ['remark', 'Test remark'];
    }
}
