<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Integration\Controller\QrCode;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Tourze\WechatBotBundle\Controller\QrCode\GenerateQrCodeController;
use Tourze\WechatBotBundle\DTO\WeChatLoginResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Service\WeChatAccountService;

class GenerateQrCodeControllerTest extends TestCase
{
    private GenerateQrCodeController $controller;
    private WeChatAccountService $accountService;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->accountService = $this->createMock(WeChatAccountService::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->controller = new GenerateQrCodeController(
            $this->accountService,
            $this->logger
        );
    }

    public function testInvokeReturnsSuccessResponse(): void
    {
        $account = $this->createMock(WeChatAccount::class);
        $request = new Request();
        $request->request->set('province', '北京');
        $request->request->set('city', '北京');
        $request->request->set('proxy', 'http://proxy.example.com');

        $result = new WeChatLoginResult(null, 'https://example.com/qrcode.png', true, '二维码生成成功');

        $this->accountService->method('startLogin')
            ->with($account, '北京', '北京', 'http://proxy.example.com')
            ->willReturn($result);

        $response = ($this->controller)($account, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('https://example.com/qrcode.png', $data['qrCodeUrl']);
        $this->assertEquals('二维码生成成功', $data['message']);
    }

    public function testInvokeWithDefaultParameters(): void
    {
        $account = $this->createMock(WeChatAccount::class);
        $request = new Request();

        $result = new WeChatLoginResult(null, 'https://example.com/qrcode.png', true, '二维码生成成功');

        $this->accountService->method('startLogin')
            ->with($account, '广东', '深圳', null)
            ->willReturn($result);

        $response = ($this->controller)($account, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInvokeReturnsFailureResponse(): void
    {
        $account = $this->createMock(WeChatAccount::class);
        $request = new Request();

        $result = new WeChatLoginResult(null, null, false, 'Failed to generate QR code', 'API_ERROR');

        $this->accountService->method('startLogin')->willReturn($result);

        $response = ($this->controller)($account, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Failed to generate QR code', $data['message']);
        $this->assertEquals('API_ERROR', $data['error']);
    }

    public function testInvokeHandlesException(): void
    {
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getId')->willReturn(1);
        $request = new Request();

        $this->accountService->method('startLogin')
            ->willThrowException(new \Exception('Test error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Failed to generate QR code');

        $response = ($this->controller)($account, $request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('生成二维码失败', $data['message']);
    }
}