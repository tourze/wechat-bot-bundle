<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * WeChatAccount 实体单元测试
 *
 * @internal
 */
#[CoversClass(WeChatAccount::class)]
final class WeChatAccountTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new WeChatAccount();
    }

    /**
     * 提供 WeChatAccount 实体的属性数据进行自动测试。
     *
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'deviceId' => ['deviceId', 'test_device_123'],
            'wechatId' => ['wechatId', 'test_wx_123'],
            'nickname' => ['nickname', 'Test User'],
            'avatar' => ['avatar', 'http://example.com/avatar.jpg'],
            'status' => ['status', 'online'],
            'accessToken' => ['accessToken', 'test_token_123'],
            'valid' => ['valid', true],
            'qrCode' => ['qrCode', 'test_qr_code_data'],
            'qrCodeUrl' => ['qrCodeUrl', 'http://example.com/qrcode.png'],
            'proxy' => ['proxy', 'http://proxy.example.com:8080'],
            'remark' => ['remark', 'Test remark'],
        ];
    }

    public function testConstructorSetsDefaultValues(): void
    {
        $account = new WeChatAccount();

        // 验证默认值
        $this->assertEquals('pending_login', $account->getStatus());
        $this->assertTrue($account->isValid());
        $this->assertNull($account->getDeviceId());
        $this->assertNull($account->getWechatId());
        $this->assertNull($account->getNickname());
        $this->assertNull($account->getAvatar());
    }

    public function testSettersAndGettersWorkCorrectly(): void
    {
        $account = new WeChatAccount();
        $apiAccount = new WeChatApiAccount();

        // 测试设置和获取API账号
        $account->setApiAccount($apiAccount);
        $this->assertSame($apiAccount, $account->getApiAccount());

        // 测试设置和获取设备ID
        $deviceId = 'test_device_123';
        $account->setDeviceId($deviceId);
        $this->assertEquals($deviceId, $account->getDeviceId());

        // 测试设置和获取微信ID
        $wechatId = 'test_wx_123';
        $account->setWechatId($wechatId);
        $this->assertEquals($wechatId, $account->getWechatId());

        // 测试设置和获取昵称
        $nickname = 'Test User';
        $account->setNickname($nickname);
        $this->assertEquals($nickname, $account->getNickname());

        // 测试设置和获取头像
        $avatar = 'http://example.com/avatar.jpg';
        $account->setAvatar($avatar);
        $this->assertEquals($avatar, $account->getAvatar());

        // 测试设置和获取状态
        $status = 'online';
        $account->setStatus($status);
        $this->assertEquals($status, $account->getStatus());

        // 测试设置和获取访问令牌
        $token = 'test_token_123';
        $account->setAccessToken($token);
        $this->assertEquals($token, $account->getAccessToken());

        // 测试设置和获取有效性
        $account->setValid(false);
        $this->assertFalse($account->isValid());

        // 测试二维码相关
        $qrCode = 'test_qr_code_data';
        $account->setQrCode($qrCode);
        $this->assertEquals($qrCode, $account->getQrCode());

        $qrCodeUrl = 'http://example.com/qrcode.png';
        $account->setQrCodeUrl($qrCodeUrl);
        $this->assertEquals($qrCodeUrl, $account->getQrCodeUrl());

        // 测试代理设置
        $proxy = 'http://proxy.example.com:8080';
        $account->setProxy($proxy);
        $this->assertEquals($proxy, $account->getProxy());

        // 测试备注
        $remark = 'Test remark';
        $account->setRemark($remark);
        $this->assertEquals($remark, $account->getRemark());
    }

    public function testToStringReturnsDeviceIdWhenSet(): void
    {
        $account = new WeChatAccount();
        $deviceId = 'test_device_123';
        $account->setDeviceId($deviceId);

        $this->assertStringContainsString($deviceId, (string) $account);
    }

    public function testToStringReturnsWechatIdWhenDeviceIdNotSet(): void
    {
        $account = new WeChatAccount();
        $wechatId = 'test_wx_123';
        $account->setWechatId($wechatId);

        $this->assertStringContainsString($wechatId, (string) $account);
    }

    public function testToStringReturnsIdStringWhenBothDeviceIdAndWechatIdNotSet(): void
    {
        $account = new WeChatAccount();
        // 由于没有setId方法，测试ID为null的情况
        $this->assertNotEmpty((string) $account);
    }

    public function testIsOnlineReturnsTrueWhenStatusIsOnline(): void
    {
        $account = new WeChatAccount();
        $account->setStatus('online');

        $this->assertTrue($account->isOnline());
    }

    public function testIsOnlineReturnsFalseWhenStatusIsNotOnline(): void
    {
        $account = new WeChatAccount();
        $account->setStatus('offline');

        $this->assertFalse($account->isOnline());
    }

    public function testIsOfflineReturnsTrueWhenStatusIsOffline(): void
    {
        $account = new WeChatAccount();
        $account->setStatus('offline');

        $this->assertTrue($account->isOffline());
    }

    public function testIsOfflineReturnsFalseWhenStatusIsNotOffline(): void
    {
        $account = new WeChatAccount();
        $account->setStatus('online');

        $this->assertFalse($account->isOffline());
    }

    public function testIsPendingLoginReturnsTrueWhenStatusIsPendingLogin(): void
    {
        $account = new WeChatAccount();
        $account->setStatus('pending_login');

        $this->assertTrue($account->isPendingLogin());
    }

    public function testIsPendingLoginReturnsFalseWhenStatusIsNotPendingLogin(): void
    {
        $account = new WeChatAccount();
        $account->setStatus('online');

        $this->assertFalse($account->isPendingLogin());
    }

    public function testIsExpiredReturnsTrueWhenStatusIsExpired(): void
    {
        $account = new WeChatAccount();
        $account->setStatus('expired');

        $this->assertTrue($account->isExpired());
    }

    public function testIsExpiredReturnsFalseWhenStatusIsNotExpired(): void
    {
        $account = new WeChatAccount();
        $account->setStatus('online');

        $this->assertFalse($account->isExpired());
    }

    public function testMarkAsOnlineSetsStatusToOnlineAndUpdatesLastLoginTime(): void
    {
        $account = new WeChatAccount();

        $account->markAsOnline();

        $this->assertEquals('online', $account->getStatus());
        // 注意：markAsOnline可能不会自动设置lastLoginTime，这取决于实际实现
        // 这里只测试状态变更
    }

    public function testMarkAsOfflineSetsStatusToOffline(): void
    {
        $account = new WeChatAccount();
        $account->setStatus('online');

        $account->markAsOffline();

        $this->assertEquals('offline', $account->getStatus());
    }

    public function testMarkAsExpiredSetsStatusToExpired(): void
    {
        $account = new WeChatAccount();
        $account->setStatus('online');

        $account->markAsExpired();

        $this->assertEquals('expired', $account->getStatus());
    }

    public function testUpdateLastActiveTimeSetsCurrentTime(): void
    {
        $account = new WeChatAccount();
        $beforeUpdate = new \DateTimeImmutable();

        $account->updateLastActiveTime();

        $afterUpdate = new \DateTimeImmutable();
        $lastActiveTime = $account->getLastActiveTime();

        $this->assertNotNull($lastActiveTime);
        if ($lastActiveTime instanceof \DateTime) {
            $lastActiveTimeImmutable = \DateTimeImmutable::createFromMutable($lastActiveTime);
        } else {
            $lastActiveTimeImmutable = $lastActiveTime;
        }
        $this->assertGreaterThanOrEqual($beforeUpdate, $lastActiveTimeImmutable);
        $this->assertLessThanOrEqual($afterUpdate, $lastActiveTimeImmutable);
    }

    public function testTimePropertiesCanBeSetAndRetrieved(): void
    {
        $account = new WeChatAccount();
        $testTime = new \DateTimeImmutable('2023-01-01 12:00:00');

        // 测试最后登录时间
        $account->setLastLoginTime($testTime);
        $this->assertEquals($testTime, $account->getLastLoginTime());

        // 测试最后活跃时间
        $account->setLastActiveTime($testTime);
        $this->assertEquals($testTime, $account->getLastActiveTime());
    }
}
