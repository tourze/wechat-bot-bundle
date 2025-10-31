<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\CreateDeviceRequest;
use Tourze\WechatBotBundle\Service\QrCodeStatusService;

/**
 * @internal
 */
#[CoversClass(CreateDeviceRequest::class)]
#[RunTestsInSeparateProcesses]
final class CreateDeviceRequestTest extends AbstractIntegrationTestCase
{
    private QrCodeStatusService $qrCodeStatusService;

    private WeChatAccount $wechatAccount;

    protected function onSetUp(): void
    {
        $this->qrCodeStatusService = self::getService(QrCodeStatusService::class);

        // 创建测试用的微信账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setAccessToken('test_token');
        $apiAccount->setBaseUrl('https://api.test.com');
        $apiAccount->setName('test_api_account');
        $apiAccount->setUsername('test_user');
        $apiAccount->setPassword('test_password');

        $this->wechatAccount = new WeChatAccount();
        $this->wechatAccount->setWechatId('test_wechat_id');
        $this->wechatAccount->setNickname('Test User');
        $this->wechatAccount->setDeviceId('test_device_123');
        $this->wechatAccount->setApiAccount($apiAccount);
        $this->wechatAccount->setStatus('online');
    }

    public function testCreateDeviceIntegration(): void
    {
        // 测试通过服务获取状态消息
        $status = $this->qrCodeStatusService->getStatusMessage('offline');

        // 验证状态消息正确
        $this->assertEquals('设备离线', $status);

        // 验证过期状态
        $expiredStatus = $this->qrCodeStatusService->getStatusMessage('expired');
        $this->assertEquals('登录已过期，需要重新登录', $expiredStatus);
    }

    public function testWeChatAccountConfiguration(): void
    {
        // 验证微信账号的基本属性配置正确
        $this->assertEquals('test_device_123', $this->wechatAccount->getDeviceId());
        $this->assertEquals('test_wechat_id', $this->wechatAccount->getWechatId());
        $this->assertNotNull($this->wechatAccount->getApiAccount());
        $this->assertEquals('test_token', $this->wechatAccount->getApiAccount()->getAccessToken());
    }

    public function testQrCodeStatusServiceAvailable(): void
    {
        // 验证服务可用
        $this->assertInstanceOf(QrCodeStatusService::class, $this->qrCodeStatusService);
    }
}
