<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatGroup;

/**
 * WeChatGroup 实体单元测试
 *
 * @internal
 */
#[CoversClass(WeChatGroup::class)]
final class WeChatGroupTest extends AbstractEntityTestCase
{
    private WeChatAccount $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = new WeChatAccount();
    }

    public function testConstructorSetsDefaultValues(): void
    {
        $group = new WeChatGroup();

        // 验证默认值
        $this->assertNull($group->getId());
        $this->assertNull($group->getGroupName());
        $this->assertNull($group->getRemarkName());
        $this->assertNull($group->getAvatar());
        $this->assertNull($group->getOwnerId());
        $this->assertNull($group->getOwnerName());
        $this->assertEquals(0, $group->getMemberCount());
        $this->assertNull($group->getAnnouncement());
        $this->assertNull($group->getDescription());
        $this->assertNull($group->getQrCodeUrl());
        $this->assertNull($group->getJoinTime());
        $this->assertNull($group->getLastActiveTime());
        $this->assertTrue($group->isInGroup());
        $this->assertTrue($group->isValid());
        $this->assertNull($group->getRemark());
    }

    public function testSettersAndGettersWorkCorrectly(): void
    {
        $group = new WeChatGroup();

        // 测试账号
        $group->setAccount($this->account);
        $this->assertSame($this->account, $group->getAccount());

        // 测试群组ID
        $groupId = 'test_group_123';
        $group->setGroupId($groupId);
        $this->assertEquals($groupId, $group->getGroupId());

        // 测试群名称
        $groupName = 'Test Group';
        $group->setGroupName($groupName);
        $this->assertEquals($groupName, $group->getGroupName());

        // 测试备注名
        $remarkName = 'My Group';
        $group->setRemarkName($remarkName);
        $this->assertEquals($remarkName, $group->getRemarkName());

        // 测试头像
        $avatar = 'https://example.com/group-avatar.jpg';
        $group->setAvatar($avatar);
        $this->assertEquals($avatar, $group->getAvatar());

        // 测试群主ID
        $ownerId = 'owner_123';
        $group->setOwnerId($ownerId);
        $this->assertEquals($ownerId, $group->getOwnerId());

        // 测试群主名称
        $ownerName = 'Group Owner';
        $group->setOwnerName($ownerName);
        $this->assertEquals($ownerName, $group->getOwnerName());

        // 测试成员数量
        $memberCount = 50;
        $group->setMemberCount($memberCount);
        $this->assertEquals($memberCount, $group->getMemberCount());

        // 测试群公告
        $announcement = 'This is a test announcement';
        $group->setAnnouncement($announcement);
        $this->assertEquals($announcement, $group->getAnnouncement());

        // 测试群描述
        $description = 'This is a test group description';
        $group->setDescription($description);
        $this->assertEquals($description, $group->getDescription());

        // 测试二维码URL
        $qrCodeUrl = 'https://example.com/qrcode.png';
        $group->setQrCodeUrl($qrCodeUrl);
        $this->assertEquals($qrCodeUrl, $group->getQrCodeUrl());

        // 测试加入时间
        $joinTime = new \DateTimeImmutable('2024-01-01 12:00:00');
        $group->setJoinTime($joinTime);
        $this->assertEquals($joinTime, $group->getJoinTime());

        // 测试最后活跃时间
        $lastActiveTime = new \DateTimeImmutable('2024-01-01 18:00:00');
        $group->setLastActiveTime($lastActiveTime);
        $this->assertEquals($lastActiveTime, $group->getLastActiveTime());

        // 测试是否在群中
        $group->setInGroup(false);
        $this->assertFalse($group->isInGroup());

        // 测试有效性
        $group->setValid(false);
        $this->assertFalse($group->isValid());

        // 测试备注
        $remark = 'Test remark';
        $group->setRemark($remark);
        $this->assertEquals($remark, $group->getRemark());
    }

    public function testToStringReturnsDisplayNameAndMemberCount(): void
    {
        $group = new WeChatGroup();
        $group->setGroupId('test_group_123');
        $group->setGroupName('Test Group');
        $group->setMemberCount(50);

        $result = (string) $group;
        $this->assertStringContainsString('Test Group', $result);
        $this->assertStringContainsString('50人', $result);
    }

    public function testGetDisplayNameReturnsRemarkNameFirst(): void
    {
        $group = new WeChatGroup();
        $group->setGroupId('test_group_123');
        $group->setGroupName('Test Group');
        $group->setRemarkName('My Group');

        $this->assertEquals('My Group', $group->getDisplayName());
    }

    public function testGetDisplayNameReturnsGroupNameWhenNoRemarkName(): void
    {
        $group = new WeChatGroup();
        $group->setGroupId('test_group_123');
        $group->setGroupName('Test Group');

        $this->assertEquals('Test Group', $group->getDisplayName());
    }

    public function testGetDisplayNameReturnsGroupIdWhenNoRemarkNameAndGroupName(): void
    {
        $group = new WeChatGroup();
        $group->setGroupId('test_group_123');

        $this->assertEquals('test_group_123', $group->getDisplayName());
    }

    public function testUpdateLastActiveTimeSetsCurrentTime(): void
    {
        $group = new WeChatGroup();
        $beforeUpdate = new \DateTimeImmutable();

        $group->updateLastActiveTime();

        $afterUpdate = new \DateTimeImmutable();
        $lastActiveTime = $group->getLastActiveTime();

        $this->assertNotNull($lastActiveTime);
        if ($lastActiveTime instanceof \DateTime) {
            $this->assertGreaterThanOrEqual($beforeUpdate, $lastActiveTime);
            $this->assertLessThanOrEqual($afterUpdate, $lastActiveTime);
        }
    }

    public function testLeaveGroupSetsInGroupToFalse(): void
    {
        $group = new WeChatGroup();
        $this->assertTrue($group->isInGroup());

        $group->leaveGroup();
        $this->assertFalse($group->isInGroup());
    }

    public function testRejoinGroupSetsInGroupToTrue(): void
    {
        $group = new WeChatGroup();
        $group->setInGroup(false);

        $group->rejoinGroup();
        $this->assertTrue($group->isInGroup());
    }

    public function testIncreaseMemberCountDefaultsToOne(): void
    {
        $group = new WeChatGroup();
        $initialCount = $group->getMemberCount();

        $group->increaseMemberCount();
        $this->assertEquals($initialCount + 1, $group->getMemberCount());
    }

    public function testIncreaseMemberCountWithCustomValue(): void
    {
        $group = new WeChatGroup();
        $initialCount = $group->getMemberCount();

        $group->increaseMemberCount(5);
        $this->assertEquals($initialCount + 5, $group->getMemberCount());
    }

    public function testDecreaseMemberCountDefaultsToOne(): void
    {
        $group = new WeChatGroup();
        $group->setMemberCount(10);

        $group->decreaseMemberCount();
        $this->assertEquals(9, $group->getMemberCount());
    }

    public function testDecreaseMemberCountWithCustomValue(): void
    {
        $group = new WeChatGroup();
        $group->setMemberCount(10);

        $group->decreaseMemberCount(3);
        $this->assertEquals(7, $group->getMemberCount());
    }

    public function testDecreaseMemberCountNeverGoesBelowZero(): void
    {
        $group = new WeChatGroup();
        $group->setMemberCount(5);

        $group->decreaseMemberCount(10);
        $this->assertEquals(0, $group->getMemberCount());
    }

    public function testSetterMethods(): void
    {
        $group = new WeChatGroup();

        $group->setAccount($this->account);
        $group->setGroupId('test_group');
        $group->setGroupName('Test Group');
        $group->setRemarkName('My Group');
        $group->setAvatar('https://example.com/avatar.jpg');
        $group->setOwnerId('owner_123');
        $group->setOwnerName('Group Owner');
        $group->setMemberCount(50);
        $group->setAnnouncement('Test announcement');
        $group->setDescription('Test description');
        $group->setQrCodeUrl('https://example.com/qrcode.png');
        $group->setInGroup(true);
        $group->setValid(true);
        $group->setRemark('Test remark');

        // Verify all values are set correctly
        $this->assertSame($this->account, $group->getAccount());
        $this->assertEquals('test_group', $group->getGroupId());
        $this->assertEquals('Test Group', $group->getGroupName());
        $this->assertEquals('My Group', $group->getRemarkName());
        $this->assertEquals('https://example.com/avatar.jpg', $group->getAvatar());
        $this->assertEquals('owner_123', $group->getOwnerId());
        $this->assertEquals('Group Owner', $group->getOwnerName());
        $this->assertEquals(50, $group->getMemberCount());
        $this->assertEquals('Test announcement', $group->getAnnouncement());
        $this->assertEquals('Test description', $group->getDescription());
        $this->assertEquals('https://example.com/qrcode.png', $group->getQrCodeUrl());
        $this->assertTrue($group->isInGroup());
        $this->assertTrue($group->isValid());
        $this->assertEquals('Test remark', $group->getRemark());
    }

    public function testBusinessMethods(): void
    {
        $group = new WeChatGroup();
        $group->setMemberCount(10);

        // Test updateLastActiveTime
        $oldTime = $group->getLastActiveTime();
        $group->updateLastActiveTime();
        $this->assertGreaterThan($oldTime, $group->getLastActiveTime());

        // Test leaveGroup
        $group->leaveGroup();
        $this->assertFalse($group->isInGroup());

        // Test rejoinGroup
        $group->rejoinGroup();
        $this->assertTrue($group->isInGroup());

        // Test increaseMemberCount
        $originalCount = $group->getMemberCount();
        $group->increaseMemberCount(5);
        $this->assertEquals($originalCount + 5, $group->getMemberCount());

        // Test decreaseMemberCount
        $group->decreaseMemberCount(3);
        $this->assertEquals($originalCount + 2, $group->getMemberCount());
    }

    public function testMemberCountModification(): void
    {
        $group = new WeChatGroup();
        $group->setMemberCount(10);

        $group->increaseMemberCount(5);
        $group->decreaseMemberCount(2);
        $group->increaseMemberCount(3);

        $this->assertEquals(16, $group->getMemberCount());
    }

    public function testGroupStateTransitions(): void
    {
        $group = new WeChatGroup();

        // 初始状态
        $this->assertTrue($group->isInGroup());

        // 离开群组
        $group->leaveGroup();
        $this->assertFalse($group->isInGroup());

        // 重新加入群组
        $group->rejoinGroup();
        $this->assertTrue($group->isInGroup());
    }

    public function testToStringWithZeroMembers(): void
    {
        $group = new WeChatGroup();
        $group->setGroupId('test_group');
        $group->setGroupName('Empty Group');
        $group->setMemberCount(0);

        $result = (string) $group;
        $this->assertStringContainsString('Empty Group', $result);
        $this->assertStringContainsString('0人', $result);
    }

    public function testMultipleMemberCountOperations(): void
    {
        $group = new WeChatGroup();
        $group->setMemberCount(0);

        // 增加成员
        $group->increaseMemberCount(10);
        $this->assertEquals(10, $group->getMemberCount());

        // 减少成员
        $group->decreaseMemberCount(3);
        $this->assertEquals(7, $group->getMemberCount());

        // 尝试减少过多成员
        $group->decreaseMemberCount(20);
        $this->assertEquals(0, $group->getMemberCount());

        // 再次增加成员
        $group->increaseMemberCount(5);
        $this->assertEquals(5, $group->getMemberCount());
    }

    protected function createEntity(): WeChatGroup
    {
        return new WeChatGroup();
    }

    /**
     * 提供 WeChatGroup 实体的属性数据进行自动测试。
     *
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'groupName' => ['groupName', 'Test Group'];
        yield 'remarkName' => ['remarkName', 'Test Remark'];
        yield 'avatar' => ['avatar', 'https://example.com/avatar.jpg'];
        yield 'ownerId' => ['ownerId', 'owner_123'];
        yield 'ownerName' => ['ownerName', 'Group Owner'];
        yield 'memberCount' => ['memberCount', 50];
        yield 'announcement' => ['announcement', 'Test announcement'];
        yield 'description' => ['description', 'Test description'];
        yield 'qrCodeUrl' => ['qrCodeUrl', 'https://example.com/qrcode.png'];
        yield 'joinTime' => ['joinTime', new \DateTimeImmutable('2024-01-01')];
        yield 'lastActiveTime' => ['lastActiveTime', new \DateTimeImmutable('2024-01-02')];
        // 注意：inGroup, valid 属性有特殊的 getter 方法，暂时跳过自动测试
        yield 'remark' => ['remark', 'Test remark'];
    }
}
