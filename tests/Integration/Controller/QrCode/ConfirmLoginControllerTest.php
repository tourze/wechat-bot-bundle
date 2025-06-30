<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Integration\Controller\QrCode;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tourze\WechatBotBundle\Controller\QrCode\ConfirmLoginController;
use Tourze\WechatBotBundle\DTO\WeChatLoginResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Service\WeChatAccountService;

class ConfirmLoginControllerTest extends TestCase
{
    private ConfirmLoginController $controller;
    private WeChatAccountService $accountService;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->accountService = $this->createMock(WeChatAccountService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->controller = new ConfirmLoginController(
            $this->accountService,
            $this->logger
        );
    }

    public function testInvokeReturnsSuccessResponse(): void
    {
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getWechatId')->willReturn('test_wechat_id');
        $account->method('getNickname')->willReturn('Test User');
        $account->method('getAvatar')->willReturn('avatar_url');

        $result = new WeChatLoginResult($account, null, true, '登录确认成功');

        $this->accountService->method('confirmLogin')->willReturn($result);

        $response = ($this->controller)($account);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('登录确认成功', $data['message']);
        $this->assertEquals('test_wechat_id', $data['account']['wechatId']);
    }

    public function testInvokeReturnsFailureResponse(): void
    {
        $account = $this->createMock(WeChatAccount::class);

        $result = new WeChatLoginResult(null, null, false, 'Login failed');

        $this->accountService->method('confirmLogin')->willReturn($result);

        $response = ($this->controller)($account);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Login failed', $data['message']);
    }

    public function testInvokeHandlesException(): void
    {
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getId')->willReturn(1);

        $this->accountService->method('confirmLogin')
            ->willThrowException(new \Exception('Test error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Failed to confirm login');

        $response = ($this->controller)($account);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('确认登录失败', $data['message']);
    }
}