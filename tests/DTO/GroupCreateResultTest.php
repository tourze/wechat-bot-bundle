<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\GroupCreateResult;

/**
 * GroupCreateResult DTO 单元测试
 *
 * @internal
 */
#[CoversClass(GroupCreateResult::class)]
final class GroupCreateResultTest extends RequestTestCase
{
    public function testConstructWithValidParametersSetsPropertiesCorrectly(): void
    {
        // Arrange
        $groupWxid = 'group_123456789@chatroom';
        $groupName = 'Test Group';
        $memberWxids = ['user1_wxid', 'user2_wxid', 'user3_wxid'];

        // Act
        $result = new GroupCreateResult(
            groupWxid: $groupWxid,
            groupName: $groupName,
            memberWxids: $memberWxids
        );

        // Assert
        $this->assertSame($groupWxid, $result->groupWxid);
        $this->assertSame($groupName, $result->groupName);
        $this->assertSame($memberWxids, $result->memberWxids);
    }

    public function testConstructWithEmptyMemberList(): void
    {
        // Act
        $result = new GroupCreateResult(
            groupWxid: 'empty_group@chatroom',
            groupName: 'Empty Group',
            memberWxids: []
        );

        // Assert
        $this->assertSame('empty_group@chatroom', $result->groupWxid);
        $this->assertSame('Empty Group', $result->groupName);
        $this->assertSame([], $result->memberWxids);
        $this->assertEmpty($result->memberWxids);
    }

    public function testConstructWithSingleMember(): void
    {
        // Act
        $result = new GroupCreateResult(
            groupWxid: 'single_member_group@chatroom',
            groupName: 'Single Member Group',
            memberWxids: ['only_member_wxid']
        );

        // Assert
        $this->assertSame('single_member_group@chatroom', $result->groupWxid);
        $this->assertSame('Single Member Group', $result->groupName);
        $this->assertSame(['only_member_wxid'], $result->memberWxids);
        $this->assertCount(1, $result->memberWxids);
    }

    public function testConstructWithMultipleMembers(): void
    {
        // Arrange
        $memberWxids = [
            'creator_wxid',
            'member1_wxid',
            'member2_wxid',
            'member3_wxid',
            'member4_wxid',
        ];

        // Act
        $result = new GroupCreateResult(
            groupWxid: 'multi_member_group@chatroom',
            groupName: 'Multi Member Group',
            memberWxids: $memberWxids
        );

        // Assert
        $this->assertSame('multi_member_group@chatroom', $result->groupWxid);
        $this->assertSame('Multi Member Group', $result->groupName);
        $this->assertSame($memberWxids, $result->memberWxids);
        $this->assertCount(5, $result->memberWxids);
        $this->assertContains('creator_wxid', $result->memberWxids);
        $this->assertContains('member1_wxid', $result->memberWxids);
        $this->assertContains('member4_wxid', $result->memberWxids);
    }

    public function testConstructWithLongGroupName(): void
    {
        // Act
        $result = new GroupCreateResult(
            groupWxid: 'long_name_group@chatroom',
            groupName: 'This is a very long group name that contains many characters to test how the DTO handles longer names',
            memberWxids: ['user1_wxid', 'user2_wxid']
        );

        // Assert
        $this->assertSame('long_name_group@chatroom', $result->groupWxid);
        $this->assertSame('This is a very long group name that contains many characters to test how the DTO handles longer names', $result->groupName);
        $this->assertSame(['user1_wxid', 'user2_wxid'], $result->memberWxids);
    }

    public function testConstructWithSpecialCharactersInGroupName(): void
    {
        // Act
        $result = new GroupCreateResult(
            groupWxid: 'special_chars_group@chatroom',
            groupName: '测试群组-Special_Characters@#$%^&*()',
            memberWxids: ['user1_wxid', 'user2_wxid', 'user3_wxid']
        );

        // Assert
        $this->assertSame('special_chars_group@chatroom', $result->groupWxid);
        $this->assertSame('测试群组-Special_Characters@#$%^&*()', $result->groupName);
        $this->assertSame(['user1_wxid', 'user2_wxid', 'user3_wxid'], $result->memberWxids);
    }

    public function testConstructWithEmptyGroupName(): void
    {
        // Act
        $result = new GroupCreateResult(
            groupWxid: 'unnamed_group@chatroom',
            groupName: '',
            memberWxids: ['user1_wxid', 'user2_wxid']
        );

        // Assert
        $this->assertSame('unnamed_group@chatroom', $result->groupWxid);
        $this->assertSame('', $result->groupName);
        $this->assertSame(['user1_wxid', 'user2_wxid'], $result->memberWxids);
    }

    public function testConstructWithDuplicateMemberWxids(): void
    {
        // Arrange - 模拟可能出现的重复成员ID情况
        $memberWxids = ['user1_wxid', 'user2_wxid', 'user1_wxid', 'user3_wxid'];

        // Act
        $result = new GroupCreateResult(
            groupWxid: 'duplicate_members_group@chatroom',
            groupName: 'Duplicate Members Group',
            memberWxids: $memberWxids
        );

        // Assert
        $this->assertSame('duplicate_members_group@chatroom', $result->groupWxid);
        $this->assertSame('Duplicate Members Group', $result->groupName);
        $this->assertSame($memberWxids, $result->memberWxids);
        $this->assertCount(4, $result->memberWxids);
    }

    public function testConstructWithDifferentChatroomFormats(): void
    {
        // Test with different chatroom ID formats
        $result1 = new GroupCreateResult(
            groupWxid: '123456789@chatroom',
            groupName: 'Format 1',
            memberWxids: ['user1_wxid']
        );

        $result2 = new GroupCreateResult(
            groupWxid: 'abcdef123@chatroom',
            groupName: 'Format 2',
            memberWxids: ['user2_wxid']
        );

        $result3 = new GroupCreateResult(
            groupWxid: 'group_with_underscores_123@chatroom',
            groupName: 'Format 3',
            memberWxids: ['user3_wxid']
        );

        // Assert
        $this->assertSame('123456789@chatroom', $result1->groupWxid);
        $this->assertSame('abcdef123@chatroom', $result2->groupWxid);
        $this->assertSame('group_with_underscores_123@chatroom', $result3->groupWxid);
    }
}
