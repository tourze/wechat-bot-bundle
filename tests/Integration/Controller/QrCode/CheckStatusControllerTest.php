<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Integration\Controller\QrCode;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tourze\WechatBotBundle\Controller\QrCode\CheckStatusController;
use Tourze\WechatBotBundle\DTO\WeChatDeviceStatus;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Service\QrCodeStatusService;
use Tourze\WechatBotBundle\Service\WeChatAccountService;

class CheckStatusControllerTest extends TestCase
{
    private CheckStatusController $controller;
    private WeChatAccountService $accountService;
    private EntityManagerInterface $entityManager;
    private QrCodeStatusService $statusService;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->accountService = $this->createMock(WeChatAccountService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->statusService = $this->createMock(QrCodeStatusService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->controller = new CheckStatusController(
            $this->accountService,
            $this->entityManager,
            $this->statusService,
            $this->logger
        );
    }

    public function testInvokeReturnsSuccessResponse(): void
    {
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getStatus')->willReturn('online');
        $account->method('getWechatId')->willReturn('test_wechat_id');
        $account->method('getNickname')->willReturn('Test User');
        $account->method('getAvatar')->willReturn('avatar_url');
        $account->method('getLastActiveTime')->willReturn(new \DateTime());

        $deviceStatus = new WeChatDeviceStatus('device123', true, 'online');

        $this->accountService->method('checkOnlineStatus')->willReturn($deviceStatus);
        $this->statusService->method('getStatusMessage')->willReturn('在线');

        $response = ($this->controller)($account);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('online', $data['status']);
        $this->assertTrue($data['isOnline']);
    }

    public function testInvokeHandlesException(): void
    {
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getId')->willReturn(1);

        $this->accountService->method('checkOnlineStatus')
            ->willThrowException(new \Exception('Test error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Failed to check login status');

        $response = ($this->controller)($account);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('检查状态失败', $data['message']);
    }
}