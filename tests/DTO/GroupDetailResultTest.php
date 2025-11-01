<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\GroupDetailResult;

/**
 * GroupDetailResult DTO 单元测试
 *
 * @internal
 */
#[CoversClass(GroupDetailResult::class)]
final class GroupDetailResultTest extends RequestTestCase
{
    public function testConstructWithValidParametersSetsPropertiesCorrectly(): void
    {
        // Arrange
        $wxid = 'group_123456789@chatroom';
        $groupName = 'Test Group';
        $memberCount = 15;
        $maxMemberCount = 500;
        $ownerWxid = 'owner_wxid_123';
        $notice = 'Welcome to our group! Please follow the rules.';
        $avatar = 'https://example.com/group_avatar.jpg';
        $createTime = 1640995200; // 2022-01-01 00:00:00

        // Act
        $result = new GroupDetailResult(
            wxid: $wxid,
            groupName: $groupName,
            memberCount: $memberCount,
            maxMemberCount: $maxMemberCount,
            ownerWxid: $ownerWxid,
            notice: $notice,
            avatar: $avatar,
            createTime: $createTime
        );

        // Assert
        $this->assertSame($wxid, $result->wxid);
        $this->assertSame($groupName, $result->groupName);
        $this->assertSame($memberCount, $result->memberCount);
        $this->assertSame($maxMemberCount, $result->maxMemberCount);
        $this->assertSame($ownerWxid, $result->ownerWxid);
        $this->assertSame($notice, $result->notice);
        $this->assertSame($avatar, $result->avatar);
        $this->assertSame($createTime, $result->createTime);
    }

    public function testConstructWithEmptyNotice(): void
    {
        // Act
        $result = new GroupDetailResult(
            wxid: 'group_no_notice@chatroom',
            groupName: 'No Notice Group',
            memberCount: 5,
            maxMemberCount: 100,
            ownerWxid: 'owner_123',
            notice: '',
            avatar: 'avatar.jpg',
            createTime: 1640995200
        );

        // Assert
        $this->assertSame('', $result->notice);
        $this->assertEmpty($result->notice);
    }

    public function testConstructWithLongNotice(): void
    {
        // Arrange
        $longNotice = 'This is a very long group notice that contains detailed information about the group rules, guidelines, and expectations. It may include multiple paragraphs and various instructions for members.';

        // Act
        $result = new GroupDetailResult(
            wxid: 'group_long_notice@chatroom',
            groupName: 'Long Notice Group',
            memberCount: 50,
            maxMemberCount: 500,
            ownerWxid: 'owner_123',
            notice: $longNotice,
            avatar: 'avatar.jpg',
            createTime: 1640995200
        );

        // Assert
        $this->assertSame($longNotice, $result->notice);
        $this->assertStringContainsString('very long group notice', $result->notice);
        $this->assertStringContainsString('expectations', $result->notice);
    }

    public function testConstructWithSmallGroup(): void
    {
        // Act
        $result = new GroupDetailResult(
            wxid: 'small_group@chatroom',
            groupName: 'Small Group',
            memberCount: 3,
            maxMemberCount: 50,
            ownerWxid: 'owner_small',
            notice: 'Small group for close friends',
            avatar: 'small_avatar.jpg',
            createTime: 1640995200
        );

        // Assert
        $this->assertSame(3, $result->memberCount);
        $this->assertSame(50, $result->maxMemberCount);
        $this->assertLessThan($result->maxMemberCount, $result->memberCount);
    }

    public function testConstructWithLargeGroup(): void
    {
        // Act
        $result = new GroupDetailResult(
            wxid: 'large_group@chatroom',
            groupName: 'Large Group',
            memberCount: 450,
            maxMemberCount: 500,
            ownerWxid: 'owner_large',
            notice: 'This is a large group with many members',
            avatar: 'large_avatar.jpg',
            createTime: 1640995200
        );

        // Assert
        $this->assertSame(450, $result->memberCount);
        $this->assertSame(500, $result->maxMemberCount);
        $this->assertLessThan($result->maxMemberCount, $result->memberCount);
    }

    public function testConstructWithFullGroup(): void
    {
        // Act
        $result = new GroupDetailResult(
            wxid: 'full_group@chatroom',
            groupName: 'Full Group',
            memberCount: 500,
            maxMemberCount: 500,
            ownerWxid: 'owner_full',
            notice: 'This group is now full',
            avatar: 'full_avatar.jpg',
            createTime: 1640995200
        );

        // Assert
        $this->assertSame(500, $result->memberCount);
        $this->assertSame(500, $result->maxMemberCount);
        $this->assertEquals($result->maxMemberCount, $result->memberCount);
    }

    public function testConstructWithDifferentCreateTimes(): void
    {
        // Test with different timestamps
        $recentTime = 1704067200; // 2024-01-01 00:00:00
        $result1 = new GroupDetailResult(
            wxid: 'recent_group@chatroom',
            groupName: 'Recent Group',
            memberCount: 10,
            maxMemberCount: 100,
            ownerWxid: 'owner_recent',
            notice: 'Recently created',
            avatar: 'recent_avatar.jpg',
            createTime: $recentTime
        );

        $oldTime = 1577836800; // 2020-01-01 00:00:00
        $result2 = new GroupDetailResult(
            wxid: 'old_group@chatroom',
            groupName: 'Old Group',
            memberCount: 25,
            maxMemberCount: 200,
            ownerWxid: 'owner_old',
            notice: 'Created long ago',
            avatar: 'old_avatar.jpg',
            createTime: $oldTime
        );

        // Assert
        $this->assertSame($recentTime, $result1->createTime);
        $this->assertSame($oldTime, $result2->createTime);
        $this->assertGreaterThan($result2->createTime, $result1->createTime);
    }

    public function testConstructWithSpecialCharactersInGroupName(): void
    {
        // Act
        $result = new GroupDetailResult(
            wxid: 'special_group@chatroom',
            groupName: '测试群组-Special_Characters@#$%^&*()',
            memberCount: 20,
            maxMemberCount: 200,
            ownerWxid: 'owner_special',
            notice: 'Group with special characters in name',
            avatar: 'special_avatar.jpg',
            createTime: 1640995200
        );

        // Assert
        $this->assertSame('测试群组-Special_Characters@#$%^&*()', $result->groupName);
        $this->assertStringContainsString('测试群组', $result->groupName);
        $this->assertStringContainsString('Special_Characters', $result->groupName);
    }

    public function testConstructWithZeroMembers(): void
    {
        // Act
        $result = new GroupDetailResult(
            wxid: 'empty_group@chatroom',
            groupName: 'Empty Group',
            memberCount: 0,
            maxMemberCount: 100,
            ownerWxid: 'owner_empty',
            notice: 'This group has no members yet',
            avatar: 'empty_avatar.jpg',
            createTime: 1640995200
        );

        // Assert
        $this->assertSame(0, $result->memberCount);
        $this->assertSame(100, $result->maxMemberCount);
        $this->assertLessThan($result->maxMemberCount, $result->memberCount);
    }

    public function testConstructWithNoticeContainingNewlines(): void
    {
        // Arrange
        $noticeWithNewlines = "Welcome to our group!\n\nPlease follow these rules:\n1. Be respectful\n2. Stay on topic\n3. No spam\n\nThank you!";

        // Act
        $result = new GroupDetailResult(
            wxid: 'multiline_notice_group@chatroom',
            groupName: 'Multiline Notice Group',
            memberCount: 30,
            maxMemberCount: 300,
            ownerWxid: 'owner_multiline',
            notice: $noticeWithNewlines,
            avatar: 'multiline_avatar.jpg',
            createTime: 1640995200
        );

        // Assert
        $this->assertSame($noticeWithNewlines, $result->notice);
        $this->assertStringContainsString("Welcome to our group!\n\n", $result->notice);
        $this->assertStringContainsString("1. Be respectful\n2. Stay on topic", $result->notice);
    }
}
