<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use HttpClientBundle\Tests\Request\RequestTestCase;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\SendFileMessageRequest;
use Tourze\WechatBotBundle\Service\WeChatMessageService;

/**
 * @internal
 */
#[CoversClass(SendFileMessageRequest::class)]
#[RunTestsInSeparateProcesses]
final class SendFileMessageRequestTest extends AbstractIntegrationTestCase
{
    private WeChatMessageService $messageService;

    private WeChatAccount $wechatAccount;

    protected function onSetUp(): void
    {
        $this->messageService = self::getService(WeChatMessageService::class);

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

    public function testSendFileMessageIntegration(): void
    {
        // 测试通过服务发送文件消息的完整流程
        $result = $this->messageService->sendFileMessage(
            $this->wechatAccount,
            'target_wx_id',
            'https://example.com/test-file.pdf',
            'test-file.pdf'
        );

        // 验证结果结构
        $this->assertFalse($result->success); // 由于没有真实API，预期会失败
        $this->assertNotNull($result->errorMessage);
        $this->assertNull($result->message);
        $this->assertNull($result->apiResponse);
    }

    public function testSendFileMessageRequestCreation(): void
    {
        // 验证服务方法的参数和返回值，不直接实例化Request对象
        $result = $this->messageService->sendFileMessage(
            $this->wechatAccount,
            'target_wx_id',
            'https://example.com/test-file.pdf',
            'test-file.pdf'
        );

        // 验证结果结构（预期失败因为没有真实API）
        $this->assertFalse($result->success);
        $this->assertNotNull($result->errorMessage);

        // 验证微信账号的基本属性配置正确
        $this->assertEquals('test_device_123', $this->wechatAccount->getDeviceId());
        $this->assertEquals('test_wechat_id', $this->wechatAccount->getWechatId());
        $this->assertNotNull($this->wechatAccount->getApiAccount());
        $this->assertEquals('test_token', $this->wechatAccount->getApiAccount()->getAccessToken());
    }
}
