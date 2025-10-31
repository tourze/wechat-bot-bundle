<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\GroupMemberInfo;

/**
 * GroupMemberInfo DTO 单元测试
 *
 * @internal
 */
#[CoversClass(GroupMemberInfo::class)]
final class GroupMemberInfoTest extends RequestTestCase
{
    public function testConstructWithValidParametersSetsPropertiesCorrectly(): void
    {
        // Arrange
        $wxid = 'member_wxid_123';
        $nickname = 'Member User';
        $displayName = 'Member Display Name';
        $avatar = 'https://example.com/member_avatar.jpg';
        $inviterWxid = 'inviter_wxid_456';
        $joinTime = 1640995200; // 2022-01-01 00:00:00
        $isAdmin = true;

        // Act
        $result = new GroupMemberInfo(
            wxid: $wxid,
            nickname: $nickname,
            displayName: $displayName,
            avatar: $avatar,
            inviterWxid: $inviterWxid,
            joinTime: $joinTime,
            isAdmin: $isAdmin
        );

        // Assert
        $this->assertSame($wxid, $result->wxid);
        $this->assertSame($nickname, $result->nickname);
        $this->assertSame($displayName, $result->displayName);
        $this->assertSame($avatar, $result->avatar);
        $this->assertSame($inviterWxid, $result->inviterWxid);
        $this->assertSame($joinTime, $result->joinTime);
        $this->assertSame($isAdmin, $result->isAdmin);
    }

    public function testConstructWithAdminMember(): void
    {
        // Act
        $result = new GroupMemberInfo(
            wxid: 'admin_wxid',
            nickname: 'Admin User',
            displayName: 'Group Admin',
            avatar: 'admin_avatar.jpg',
            inviterWxid: 'owner_wxid',
            joinTime: 1640995200,
            isAdmin: true
        );

        // Assert
        $this->assertTrue($result->isAdmin);
        $this->assertSame('Group Admin', $result->displayName);
    }

    public function testConstructWithRegularMember(): void
    {
        // Act
        $result = new GroupMemberInfo(
            wxid: 'regular_wxid',
            nickname: 'Regular User',
            displayName: 'Regular Member',
            avatar: 'regular_avatar.jpg',
            inviterWxid: 'admin_wxid',
            joinTime: 1641081600, // 2022-01-02 00:00:00
            isAdmin: false
        );

        // Assert
        $this->assertFalse($result->isAdmin);
        $this->assertSame('Regular Member', $result->displayName);
    }

    public function testConstructWithEmptyDisplayName(): void
    {
        // Act
        $result = new GroupMemberInfo(
            wxid: 'no_display_name_wxid',
            nickname: 'User Without Display Name',
            displayName: '',
            avatar: 'avatar.jpg',
            inviterWxid: 'inviter_wxid',
            joinTime: 1640995200,
            isAdmin: false
        );

        // Assert
        $this->assertSame('', $result->displayName);
        $this->assertEmpty($result->displayName);
        $this->assertSame('User Without Display Name', $result->nickname);
    }

    public function testConstructWithEmptyInviterWxid(): void
    {
        // Act
        $result = new GroupMemberInfo(
            wxid: 'self_joined_wxid',
            nickname: 'Self Joined User',
            displayName: 'Self Joined',
            avatar: 'self_avatar.jpg',
            inviterWxid: '',
            joinTime: 1640995200,
            isAdmin: false
        );

        // Assert
        $this->assertSame('', $result->inviterWxid);
        $this->assertEmpty($result->inviterWxid);
    }

    public function testConstructWithDifferentJoinTimes(): void
    {
        // Test with different join times
        $earlierTime = 1640995200; // 2022-01-01 00:00:00
        $laterTime = 1641081600;   // 2022-01-02 00:00:00

        $earlyMember = new GroupMemberInfo(
            wxid: 'early_member_wxid',
            nickname: 'Early Member',
            displayName: 'Early Joiner',
            avatar: 'early_avatar.jpg',
            inviterWxid: 'owner_wxid',
            joinTime: $earlierTime,
            isAdmin: false
        );

        $lateMember = new GroupMemberInfo(
            wxid: 'late_member_wxid',
            nickname: 'Late Member',
            displayName: 'Late Joiner',
            avatar: 'late_avatar.jpg',
            inviterWxid: 'admin_wxid',
            joinTime: $laterTime,
            isAdmin: false
        );

        // Assert
        $this->assertSame($earlierTime, $earlyMember->joinTime);
        $this->assertSame($laterTime, $lateMember->joinTime);
        $this->assertLessThan($lateMember->joinTime, $earlyMember->joinTime);
    }

    public function testConstructWithSpecialCharactersInDisplayName(): void
    {
        // Act
        $result = new GroupMemberInfo(
            wxid: 'special_display_wxid',
            nickname: 'Special User',
            displayName: '特殊显示名称-Special_Display@#$%^&*()',
            avatar: 'special_avatar.jpg',
            inviterWxid: 'inviter_wxid',
            joinTime: 1640995200,
            isAdmin: false
        );

        // Assert
        $this->assertSame('特殊显示名称-Special_Display@#$%^&*()', $result->displayName);
        $this->assertStringContainsString('特殊显示名称', $result->displayName);
        $this->assertStringContainsString('Special_Display', $result->displayName);
    }

    public function testConstructWithSpecialCharactersInNickname(): void
    {
        // Act
        $result = new GroupMemberInfo(
            wxid: 'special_nickname_wxid',
            nickname: '特殊昵称-Special_Nickname@#$%^&*()',
            displayName: 'Display Name',
            avatar: 'special_avatar.jpg',
            inviterWxid: 'inviter_wxid',
            joinTime: 1640995200,
            isAdmin: false
        );

        // Assert
        $this->assertSame('特殊昵称-Special_Nickname@#$%^&*()', $result->nickname);
        $this->assertStringContainsString('特殊昵称', $result->nickname);
        $this->assertStringContainsString('Special_Nickname', $result->nickname);
    }

    public function testConstructWithLongDisplayName(): void
    {
        // Arrange
        $longDisplayName = 'This is a very long display name that contains many characters to test how the DTO handles longer display names in group members';

        // Act
        $result = new GroupMemberInfo(
            wxid: 'long_display_name_wxid',
            nickname: 'Long Display Name User',
            displayName: $longDisplayName,
            avatar: 'long_avatar.jpg',
            inviterWxid: 'inviter_wxid',
            joinTime: 1640995200,
            isAdmin: false
        );

        // Assert
        $this->assertSame($longDisplayName, $result->displayName);
        $this->assertStringContainsString('very long display name', $result->displayName);
        $this->assertStringContainsString('longer display names', $result->displayName);
    }

    public function testConstructWithSelfAsInviter(): void
    {
        // Act - 模拟群主自己邀请自己的情况
        $result = new GroupMemberInfo(
            wxid: 'owner_wxid',
            nickname: 'Group Owner',
            displayName: 'Owner',
            avatar: 'owner_avatar.jpg',
            inviterWxid: 'owner_wxid', // 自己邀请自己
            joinTime: 1640995200,
            isAdmin: true
        );

        // Assert
        $this->assertSame('owner_wxid', $result->wxid);
        $this->assertSame('owner_wxid', $result->inviterWxid);
        $this->assertSame($result->wxid, $result->inviterWxid);
        $this->assertTrue($result->isAdmin);
    }

    public function testConstructWithRecentJoinTime(): void
    {
        // Arrange
        $recentTime = 1704067200; // 2024-01-01 00:00:00

        // Act
        $result = new GroupMemberInfo(
            wxid: 'recent_member_wxid',
            nickname: 'Recent Member',
            displayName: 'New Member',
            avatar: 'recent_avatar.jpg',
            inviterWxid: 'admin_wxid',
            joinTime: $recentTime,
            isAdmin: false
        );

        // Assert
        $this->assertSame($recentTime, $result->joinTime);
        $this->assertSame('New Member', $result->displayName);
    }
}
