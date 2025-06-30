<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Integration\Controller\QrCode;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Tourze\WechatBotBundle\Controller\QrCode\LoginPageController;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Service\QrCodeStatusService;

class LoginPageControllerTest extends TestCase
{
    private LoginPageController $controller;
    private QrCodeStatusService $statusService;

    protected function setUp(): void
    {
        $this->statusService = $this->createMock(QrCodeStatusService::class);
        $this->controller = new LoginPageController($this->statusService);

        // Mock Twig environment
        $twig = $this->createMock(Environment::class);
        $twig->method('render')->willReturn('<html>Login Page</html>');
        
        // Set up container with twig service
        $container = $this->createMock(\Psr\Container\ContainerInterface::class);
        $container->method('has')->willReturn(true);
        $container->method('get')->willReturn($twig);
        $this->controller->setContainer($container);
    }

    public function testInvokeRendersLoginPage(): void
    {
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getQrCodeUrl')->willReturn('https://example.com/qr.png');
        $account->method('getDeviceId')->willReturn('device123');
        $account->method('getStatus')->willReturn('pending');

        $this->statusService->method('getStatusMessage')
            ->with('pending')
            ->willReturn('等待扫码');

        $response = ($this->controller)($account);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('<html>Login Page</html>', $response->getContent());
    }
}