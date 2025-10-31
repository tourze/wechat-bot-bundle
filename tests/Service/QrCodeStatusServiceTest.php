<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\WechatBotBundle\Service\QrCodeStatusService;

/**
 * 二维码状态服务测试
 *
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(QrCodeStatusService::class)]
final class QrCodeStatusServiceTest extends AbstractIntegrationTestCase
{
    private QrCodeStatusService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(QrCodeStatusService::class);
    }

    /**
     * 测试获取状态消息 - 等待扫码登录
     */
    public function testGetStatusMessagePendingLogin(): void
    {
        $result = $this->service->getStatusMessage('pending_login');
        $this->assertEquals('等待扫码登录', $result);
    }

    /**
     * 测试获取状态消息 - 已登录在线
     */
    public function testGetStatusMessageOnline(): void
    {
        $result = $this->service->getStatusMessage('online');
        $this->assertEquals('已登录，设备在线', $result);
    }

    /**
     * 测试获取状态消息 - 设备离线
     */
    public function testGetStatusMessageOffline(): void
    {
        $result = $this->service->getStatusMessage('offline');
        $this->assertEquals('设备离线', $result);
    }

    /**
     * 测试获取状态消息 - 登录过期
     */
    public function testGetStatusMessageExpired(): void
    {
        $result = $this->service->getStatusMessage('expired');
        $this->assertEquals('登录已过期，需要重新登录', $result);
    }

    /**
     * 测试获取状态消息 - 未知状态
     */
    public function testGetStatusMessageUnknown(): void
    {
        $result = $this->service->getStatusMessage('unknown_status');
        $this->assertEquals('状态未知', $result);
    }

    /**
     * 测试获取状态消息 - 空字符串
     */
    public function testGetStatusMessageEmptyString(): void
    {
        $result = $this->service->getStatusMessage('');
        $this->assertEquals('状态未知', $result);
    }
}
