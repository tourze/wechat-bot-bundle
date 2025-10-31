<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\WeChatLoginResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;

/**
 * WeChatLoginResult DTO 单元测试
 *
 * @internal
 */
#[CoversClass(WeChatLoginResult::class)]
final class WeChatLoginResultTest extends RequestTestCase
{
    public function testConstructWithValidParametersSetsPropertiesCorrectly(): void
    {
        // Arrange
        $account = new WeChatAccount();
        $qrCodeUrl = 'http://example.com/qrcode.png';
        $success = true;
        $message = 'Login successful';

        // Act
        $result = new WeChatLoginResult(
            account: $account,
            qrCodeUrl: $qrCodeUrl,
            success: $success,
            message: $message
        );

        // Assert
        $this->assertSame($account, $result->account);
        $this->assertEquals($qrCodeUrl, $result->qrCodeUrl);
        $this->assertTrue($result->success);
        $this->assertEquals($message, $result->message);
    }

    public function testConstructWithNullAccountAllowsNullAccount(): void
    {
        // Act
        $result = new WeChatLoginResult(
            account: null,
            qrCodeUrl: null,
            success: false,
            message: 'Failed'
        );

        // Assert
        $this->assertNull($result->account);
        $this->assertNull($result->qrCodeUrl);
        $this->assertFalse($result->success);
        $this->assertEquals('Failed', $result->message);
    }

    public function testConstructWithSuccessfulLoginReturnsSuccessfulResult(): void
    {
        // Arrange
        $account = new WeChatAccount();
        $account->setDeviceId('test_device');
        $qrCodeUrl = 'http://example.com/success_qr.png';

        // Act
        $result = new WeChatLoginResult(
            account: $account,
            qrCodeUrl: $qrCodeUrl,
            success: true,
            message: 'QR code generated successfully'
        );

        // Assert
        $this->assertTrue($result->success);
        $this->assertNotNull($result->account);
        $this->assertNotNull($result->qrCodeUrl);
        $this->assertEquals('test_device', $result->account->getDeviceId());
    }

    public function testConstructWithFailedLoginReturnsFailedResult(): void
    {
        // Act
        $result = new WeChatLoginResult(
            account: null,
            qrCodeUrl: null,
            success: false,
            message: 'API error occurred'
        );

        // Assert
        $this->assertFalse($result->success);
        $this->assertNull($result->account);
        $this->assertNull($result->qrCodeUrl);
        $this->assertEquals('API error occurred', $result->message);
    }

    public function testIsSuccessfulWithTrueSuccessReturnsTrue(): void
    {
        // Arrange
        $result = new WeChatLoginResult(
            account: new WeChatAccount(),
            qrCodeUrl: 'http://example.com/qr.png',
            success: true,
            message: 'Success'
        );

        // Act & Assert
        $this->assertTrue($result->success);
    }

    public function testIsSuccessfulWithFalseSuccessReturnsFalse(): void
    {
        // Arrange
        $result = new WeChatLoginResult(
            account: null,
            qrCodeUrl: null,
            success: false,
            message: 'Failed'
        );

        // Act & Assert
        $this->assertFalse($result->success);
    }

    public function testToStringWithAccountReturnsFormattedString(): void
    {
        // Arrange
        $account = new WeChatAccount();
        $account->setDeviceId('test_device_123');

        $result = new WeChatLoginResult(
            account: $account,
            qrCodeUrl: 'http://example.com/qr.png',
            success: true,
            message: 'Login successful'
        );

        // Act
        $stringResult = (string) $result;

        // Assert
        $this->assertStringContainsString('Login successful', $stringResult);
        $this->assertStringContainsString('success=true', $stringResult);
    }

    public function testToStringWithNullAccountReturnsFormattedString(): void
    {
        // Arrange
        $result = new WeChatLoginResult(
            account: null,
            qrCodeUrl: null,
            success: false,
            message: 'Failed to create account'
        );

        // Act
        $stringResult = (string) $result;

        // Assert
        $this->assertStringContainsString('Failed to create account', $stringResult);
        $this->assertStringContainsString('false', strtolower($stringResult));
    }
}
