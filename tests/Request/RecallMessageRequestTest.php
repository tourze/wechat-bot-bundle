<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\RecallMessageRequest;
use Tourze\WechatBotBundle\Service\WeChatMessageService;

/**
 * @internal
 */
#[CoversClass(RecallMessageRequest::class)]
#[RunTestsInSeparateProcesses]
final class RecallMessageRequestTest extends AbstractIntegrationTestCase
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

    public function testRecallMessageIntegration(): void
    {
        // 测试通过服务撤回消息的完整流程（预期失败，因为没有实际消息）
        $result = $this->messageService->recallMessage(
            $this->wechatAccount,
            'test_message_id'
        );

        // 验证结果（应该失败因为没有找到消息）
        $this->assertFalse($result);
    }

    public function testRecallMessageRequestCreation(): void
    {
        // 验证服务方法的参数和返回值，不直接实例化Request对象
        $result = $this->messageService->recallMessage(
            $this->wechatAccount,
            'test_message_id'
        );

        // 验证结果（应该失败因为没有找到消息）
        $this->assertFalse($result);

        // 验证微信账号的基本属性配置正确
        $this->assertEquals('test_device_123', $this->wechatAccount->getDeviceId());
        $this->assertEquals('test_wechat_id', $this->wechatAccount->getWechatId());
        $this->assertNotNull($this->wechatAccount->getApiAccount());
        $this->assertEquals('test_token', $this->wechatAccount->getApiAccount()->getAccessToken());
    }
}
