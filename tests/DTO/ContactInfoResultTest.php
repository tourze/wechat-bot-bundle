<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\ContactInfoResult;

/**
 * ContactInfoResult DTO 单元测试
 *
 * @internal
 */
#[CoversClass(ContactInfoResult::class)]
final class ContactInfoResultTest extends RequestTestCase
{
    public function testConstructWithValidParametersSetsPropertiesCorrectly(): void
    {
        // Arrange
        $wxid = 'test_wxid_123';
        $nickname = 'Test User';
        $avatar = 'https://example.com/avatar.jpg';
        $remark = 'Test Remark';
        $sex = 1;
        $signature = 'Test Signature';
        $phone = '13800138000';
        $city = 'Beijing';
        $province = 'Beijing';
        $country = 'China';
        $tags = ['friend', 'colleague'];
        $isFriend = true;
        $corpName = 'Test Corp';
        $position = 'Manager';

        // Act
        $result = new ContactInfoResult(
            wxid: $wxid,
            nickname: $nickname,
            avatar: $avatar,
            remark: $remark,
            sex: $sex,
            signature: $signature,
            phone: $phone,
            city: $city,
            province: $province,
            country: $country,
            tags: $tags,
            isFriend: $isFriend,
            corpName: $corpName,
            position: $position
        );

        // Assert
        $this->assertSame($wxid, $result->wxid);
        $this->assertSame($nickname, $result->nickname);
        $this->assertSame($avatar, $result->avatar);
        $this->assertSame($remark, $result->remark);
        $this->assertSame($sex, $result->sex);
        $this->assertSame($signature, $result->signature);
        $this->assertSame($phone, $result->phone);
        $this->assertSame($city, $result->city);
        $this->assertSame($province, $result->province);
        $this->assertSame($country, $result->country);
        $this->assertSame($tags, $result->tags);
        $this->assertSame($isFriend, $result->isFriend);
        $this->assertSame($corpName, $result->corpName);
        $this->assertSame($position, $result->position);
    }

    public function testConstructWithMinimalParametersUsesDefaults(): void
    {
        // Act
        $result = new ContactInfoResult(
            wxid: 'test_wxid',
            nickname: 'Test',
            avatar: 'avatar.jpg',
            remark: 'remark',
            sex: 2,
            signature: 'sig',
            phone: '123',
            city: 'city',
            province: 'province',
            country: 'country',
            tags: [],
            isFriend: false
        );

        // Assert
        $this->assertSame('', $result->corpName);
        $this->assertSame('', $result->position);
    }

    public function testConstructWithEmptyTagsArray(): void
    {
        // Act
        $result = new ContactInfoResult(
            wxid: 'test_wxid',
            nickname: 'Test User',
            avatar: 'avatar.jpg',
            remark: 'remark',
            sex: 1,
            signature: 'signature',
            phone: '13800138000',
            city: 'Beijing',
            province: 'Beijing',
            country: 'China',
            tags: [],
            isFriend: true
        );

        // Assert
        $this->assertSame([], $result->tags);
        $this->assertEmpty($result->tags);
    }

    public function testConstructWithMultipleTags(): void
    {
        // Arrange
        $tags = ['friend', 'colleague', 'business', 'important'];

        // Act
        $result = new ContactInfoResult(
            wxid: 'test_wxid',
            nickname: 'Test User',
            avatar: 'avatar.jpg',
            remark: 'remark',
            sex: 1,
            signature: 'signature',
            phone: '13800138000',
            city: 'Beijing',
            province: 'Beijing',
            country: 'China',
            tags: $tags,
            isFriend: true
        );

        // Assert
        $this->assertSame($tags, $result->tags);
        $this->assertCount(4, $result->tags);
        $this->assertContains('friend', $result->tags);
        $this->assertContains('colleague', $result->tags);
        $this->assertContains('business', $result->tags);
        $this->assertContains('important', $result->tags);
    }

    public function testConstructWithFriendStatus(): void
    {
        // Test with friend status true
        $friendResult = new ContactInfoResult(
            wxid: 'friend_wxid',
            nickname: 'Friend',
            avatar: 'avatar.jpg',
            remark: 'My friend',
            sex: 1,
            signature: 'signature',
            phone: '13800138000',
            city: 'Beijing',
            province: 'Beijing',
            country: 'China',
            tags: ['friend'],
            isFriend: true
        );

        $this->assertTrue($friendResult->isFriend);

        // Test with friend status false
        $strangerResult = new ContactInfoResult(
            wxid: 'stranger_wxid',
            nickname: 'Stranger',
            avatar: 'avatar.jpg',
            remark: '',
            sex: 0,
            signature: '',
            phone: '',
            city: '',
            province: '',
            country: '',
            tags: [],
            isFriend: false
        );

        $this->assertFalse($strangerResult->isFriend);
    }

    public function testConstructWithDifferentSexValues(): void
    {
        // Test male (1)
        $maleResult = new ContactInfoResult(
            wxid: 'male_wxid',
            nickname: 'Male User',
            avatar: 'avatar.jpg',
            remark: 'remark',
            sex: 1,
            signature: 'signature',
            phone: '13800138000',
            city: 'Beijing',
            province: 'Beijing',
            country: 'China',
            tags: [],
            isFriend: true
        );

        $this->assertSame(1, $maleResult->sex);

        // Test female (2)
        $femaleResult = new ContactInfoResult(
            wxid: 'female_wxid',
            nickname: 'Female User',
            avatar: 'avatar.jpg',
            remark: 'remark',
            sex: 2,
            signature: 'signature',
            phone: '13800138000',
            city: 'Beijing',
            province: 'Beijing',
            country: 'China',
            tags: [],
            isFriend: true
        );

        $this->assertSame(2, $femaleResult->sex);

        // Test unknown (0)
        $unknownResult = new ContactInfoResult(
            wxid: 'unknown_wxid',
            nickname: 'Unknown User',
            avatar: 'avatar.jpg',
            remark: 'remark',
            sex: 0,
            signature: 'signature',
            phone: '13800138000',
            city: 'Beijing',
            province: 'Beijing',
            country: 'China',
            tags: [],
            isFriend: true
        );

        $this->assertSame(0, $unknownResult->sex);
    }
}
