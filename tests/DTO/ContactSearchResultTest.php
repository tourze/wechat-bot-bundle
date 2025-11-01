<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\ContactSearchResult;

/**
 * ContactSearchResult DTO 单元测试
 *
 * @internal
 */
#[CoversClass(ContactSearchResult::class)]
final class ContactSearchResultTest extends RequestTestCase
{
    public function testConstructWithValidParametersSetsPropertiesCorrectly(): void
    {
        // Arrange
        $wxid = 'search_wxid_123';
        $nickname = 'Search User';
        $avatar = 'https://example.com/search_avatar.jpg';
        $sex = '1';
        $signature = 'Search Signature';
        $phone = '13800138000';
        $city = 'Shanghai';
        $province = 'Shanghai';
        $country = 'China';

        // Act
        $result = new ContactSearchResult(
            wxid: $wxid,
            nickname: $nickname,
            avatar: $avatar,
            sex: $sex,
            signature: $signature,
            phone: $phone,
            city: $city,
            province: $province,
            country: $country
        );

        // Assert
        $this->assertSame($wxid, $result->wxid);
        $this->assertSame($nickname, $result->nickname);
        $this->assertSame($avatar, $result->avatar);
        $this->assertSame($sex, $result->sex);
        $this->assertSame($signature, $result->signature);
        $this->assertSame($phone, $result->phone);
        $this->assertSame($city, $result->city);
        $this->assertSame($province, $result->province);
        $this->assertSame($country, $result->country);
    }

    public function testConstructWithEmptyStrings(): void
    {
        // Act
        $result = new ContactSearchResult(
            wxid: 'test_wxid',
            nickname: '',
            avatar: '',
            sex: '',
            signature: '',
            phone: '',
            city: '',
            province: '',
            country: ''
        );

        // Assert
        $this->assertSame('test_wxid', $result->wxid);
        $this->assertSame('', $result->nickname);
        $this->assertSame('', $result->avatar);
        $this->assertSame('', $result->sex);
        $this->assertSame('', $result->signature);
        $this->assertSame('', $result->phone);
        $this->assertSame('', $result->city);
        $this->assertSame('', $result->province);
        $this->assertSame('', $result->country);
    }

    public function testConstructWithMaleSexValue(): void
    {
        // Act
        $result = new ContactSearchResult(
            wxid: 'male_wxid',
            nickname: 'Male User',
            avatar: 'avatar.jpg',
            sex: '1',
            signature: 'Male signature',
            phone: '13800138000',
            city: 'Beijing',
            province: 'Beijing',
            country: 'China'
        );

        // Assert
        $this->assertSame('1', $result->sex);
    }

    public function testConstructWithFemaleSexValue(): void
    {
        // Act
        $result = new ContactSearchResult(
            wxid: 'female_wxid',
            nickname: 'Female User',
            avatar: 'avatar.jpg',
            sex: '2',
            signature: 'Female signature',
            phone: '13800138000',
            city: 'Beijing',
            province: 'Beijing',
            country: 'China'
        );

        // Assert
        $this->assertSame('2', $result->sex);
    }

    public function testConstructWithUnknownSexValue(): void
    {
        // Act
        $result = new ContactSearchResult(
            wxid: 'unknown_wxid',
            nickname: 'Unknown User',
            avatar: 'avatar.jpg',
            sex: '0',
            signature: 'Unknown signature',
            phone: '13800138000',
            city: 'Beijing',
            province: 'Beijing',
            country: 'China'
        );

        // Assert
        $this->assertSame('0', $result->sex);
    }

    public function testConstructWithCompleteLocationInfo(): void
    {
        // Act
        $result = new ContactSearchResult(
            wxid: 'location_wxid',
            nickname: 'Location User',
            avatar: 'avatar.jpg',
            sex: '1',
            signature: 'Location signature',
            phone: '13800138000',
            city: 'Guangzhou',
            province: 'Guangdong',
            country: 'China'
        );

        // Assert
        $this->assertSame('Guangzhou', $result->city);
        $this->assertSame('Guangdong', $result->province);
        $this->assertSame('China', $result->country);
    }

    public function testConstructWithContactDetails(): void
    {
        // Act
        $result = new ContactSearchResult(
            wxid: 'contact_wxid',
            nickname: 'Contact User',
            avatar: 'https://example.com/avatar.png',
            sex: '2',
            signature: 'Hello, this is my signature!',
            phone: '13912345678',
            city: 'Shenzhen',
            province: 'Guangdong',
            country: 'China'
        );

        // Assert
        $this->assertSame('contact_wxid', $result->wxid);
        $this->assertSame('Contact User', $result->nickname);
        $this->assertSame('https://example.com/avatar.png', $result->avatar);
        $this->assertSame('2', $result->sex);
        $this->assertSame('Hello, this is my signature!', $result->signature);
        $this->assertSame('13912345678', $result->phone);
        $this->assertSame('Shenzhen', $result->city);
        $this->assertSame('Guangdong', $result->province);
        $this->assertSame('China', $result->country);
    }
}
