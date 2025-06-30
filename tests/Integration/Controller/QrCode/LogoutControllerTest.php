<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Integration\Controller\QrCode;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tourze\WechatBotBundle\Controller\QrCode\LogoutController;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Service\WeChatAccountService;

class LogoutControllerTest extends TestCase
{
    private LogoutController $controller;
    private WeChatAccountService $accountService;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->accountService = $this->createMock(WeChatAccountService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->controller = new LogoutController(
            $this->accountService,
            $this->logger
        );
    }

    public function testInvokeReturnsSuccessResponse(): void
    {
        $account = $this->createMock(WeChatAccount::class);

        $this->accountService->method('logout')
            ->with($account)
            ->willReturn(true);

        $response = ($this->controller)($account);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('退出登录成功', $data['message']);
    }

    public function testInvokeReturnsFailureResponse(): void
    {
        $account = $this->createMock(WeChatAccount::class);

        $this->accountService->method('logout')
            ->with($account)
            ->willReturn(false);

        $response = ($this->controller)($account);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('退出登录失败', $data['message']);
    }

    public function testInvokeHandlesException(): void
    {
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getId')->willReturn(1);

        $this->accountService->method('logout')
            ->willThrowException(new \Exception('Test error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Failed to logout');

        $response = ($this->controller)($account);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('退出登录失败', $data['message']);
    }
}