<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\WeChatDeviceStatus;

/**
 * WeChatDeviceStatus DTO 单元测试
 *
 * @internal
 */
#[CoversClass(WeChatDeviceStatus::class)]
final class WeChatDeviceStatusTest extends RequestTestCase
{
    public function testConstructWithValidParametersSetsPropertiesCorrectly(): void
    {
        // Arrange
        $deviceId = 'device_123456';
        $isOnline = true;
        $status = 'online';
        $lastActiveTime = new \DateTime('2022-01-01 12:00:00');
        $error = null;

        // Act
        $result = new WeChatDeviceStatus(
            deviceId: $deviceId,
            isOnline: $isOnline,
            status: $status,
            lastActiveTime: $lastActiveTime,
            error: $error
        );

        // Assert
        $this->assertSame($deviceId, $result->deviceId);
        $this->assertSame($isOnline, $result->isOnline);
        $this->assertSame($status, $result->status);
        $this->assertSame($lastActiveTime, $result->lastActiveTime);
        $this->assertSame($error, $result->error);
    }

    public function testConstructWithDefaultValues(): void
    {
        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'device_default',
            isOnline: false,
            status: 'offline'
        );

        // Assert
        $this->assertSame('device_default', $result->deviceId);
        $this->assertFalse($result->isOnline);
        $this->assertSame('offline', $result->status);
        $this->assertNull($result->lastActiveTime);
        $this->assertNull($result->error);
    }

    public function testIsOfflineWithOnlineDevice(): void
    {
        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'online_device',
            isOnline: true,
            status: 'online'
        );

        // Assert
        $this->assertFalse($result->isOffline());
        $this->assertTrue($result->isOnline);
    }

    public function testIsOfflineWithOfflineDevice(): void
    {
        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'offline_device',
            isOnline: false,
            status: 'offline'
        );

        // Assert
        $this->assertTrue($result->isOffline());
        $this->assertFalse($result->isOnline);
    }

    public function testHasErrorWithError(): void
    {
        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'error_device',
            isOnline: false,
            status: 'offline',
            lastActiveTime: null,
            error: 'Connection timeout'
        );

        // Assert
        $this->assertTrue($result->hasError());
        $this->assertSame('Connection timeout', $result->error);
    }

    public function testHasErrorWithoutError(): void
    {
        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'no_error_device',
            isOnline: true,
            status: 'online',
            lastActiveTime: null,
            error: null
        );

        // Assert
        $this->assertFalse($result->hasError());
        $this->assertNull($result->error);
    }

    public function testGetStatusTextWithOnlineStatus(): void
    {
        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'online_device',
            isOnline: true,
            status: 'online'
        );

        // Assert
        $this->assertSame('在线', $result->getStatusText());
    }

    public function testGetStatusTextWithOfflineStatus(): void
    {
        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'offline_device',
            isOnline: false,
            status: 'offline'
        );

        // Assert
        $this->assertSame('离线', $result->getStatusText());
    }

    public function testGetStatusTextWithPendingLoginStatus(): void
    {
        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'pending_device',
            isOnline: false,
            status: 'pending_login'
        );

        // Assert
        $this->assertSame('等待登录', $result->getStatusText());
    }

    public function testGetStatusTextWithExpiredStatus(): void
    {
        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'expired_device',
            isOnline: false,
            status: 'expired'
        );

        // Assert
        $this->assertSame('已过期', $result->getStatusText());
    }

    public function testGetStatusTextWithUnknownStatus(): void
    {
        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'unknown_device',
            isOnline: false,
            status: 'unknown_status'
        );

        // Assert
        $this->assertSame('未知状态', $result->getStatusText());
    }

    public function testToStringWithOnlineDevice(): void
    {
        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'online_device_123',
            isOnline: true,
            status: 'online'
        );

        // Assert
        $stringResult = (string) $result;
        $this->assertStringContainsString('WeChatDeviceStatus', $stringResult);
        $this->assertStringContainsString('deviceId=online_device_123', $stringResult);
        $this->assertStringContainsString('status=online', $stringResult);
        $this->assertStringContainsString('online=true', $stringResult);
    }

    public function testToStringWithOfflineDevice(): void
    {
        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'offline_device_456',
            isOnline: false,
            status: 'offline'
        );

        // Assert
        $stringResult = (string) $result;
        $this->assertStringContainsString('WeChatDeviceStatus', $stringResult);
        $this->assertStringContainsString('deviceId=offline_device_456', $stringResult);
        $this->assertStringContainsString('status=offline', $stringResult);
        $this->assertStringContainsString('online=false', $stringResult);
    }

    public function testConstructWithDateTimeImmutable(): void
    {
        // Arrange
        $lastActiveTime = new \DateTimeImmutable('2022-01-01 15:30:00');

        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'immutable_device',
            isOnline: true,
            status: 'online',
            lastActiveTime: $lastActiveTime
        );

        // Assert
        $this->assertSame($lastActiveTime, $result->lastActiveTime);
        $this->assertInstanceOf(\DateTimeInterface::class, $result->lastActiveTime);
    }

    public function testConstructWithRecentLastActiveTime(): void
    {
        // Arrange
        $recentTime = new \DateTime('2024-01-01 10:00:00');

        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'recent_device',
            isOnline: true,
            status: 'online',
            lastActiveTime: $recentTime
        );

        // Assert
        $this->assertSame($recentTime, $result->lastActiveTime);
        $this->assertInstanceOf(\DateTimeInterface::class, $result->lastActiveTime);
    }

    public function testConstructWithLongErrorMessage(): void
    {
        // Arrange
        $longError = 'This is a very long error message that describes a complex network connectivity issue that occurred during the device synchronization process';

        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'error_device',
            isOnline: false,
            status: 'offline',
            lastActiveTime: null,
            error: $longError
        );

        // Assert
        $this->assertTrue($result->hasError());
        $this->assertSame($longError, $result->error);
        $this->assertStringContainsString('very long error message', $result->error);
    }

    public function testConstructWithEmptyErrorString(): void
    {
        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'empty_error_device',
            isOnline: false,
            status: 'offline',
            lastActiveTime: null,
            error: ''
        );

        // Assert
        $this->assertTrue($result->hasError()); // 空字符串也被认为是有错误
        $this->assertSame('', $result->error);
    }

    public function testConstructWithSpecialCharactersInDeviceId(): void
    {
        // Act
        $result = new WeChatDeviceStatus(
            deviceId: 'device_测试_123@#$%',
            isOnline: true,
            status: 'online'
        );

        // Assert
        $this->assertSame('device_测试_123@#$%', $result->deviceId);
        $this->assertStringContainsString('device_测试_123@#$%', (string) $result);
    }

    public function testAllStatusTransitions(): void
    {
        // Test all possible status transitions
        $statuses = [
            'online' => '在线',
            'offline' => '离线',
            'pending_login' => '等待登录',
            'expired' => '已过期',
            'connecting' => '未知状态',
            'error' => '未知状态',
        ];

        foreach ($statuses as $status => $expectedText) {
            $result = new WeChatDeviceStatus(
                deviceId: 'test_device',
                isOnline: 'online' === $status,
                status: $status
            );

            $this->assertSame($expectedText, $result->getStatusText(), "Status text for '{$status}' should be '{$expectedText}'");
        }
    }
}
