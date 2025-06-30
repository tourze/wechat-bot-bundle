<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Controller\QrCode;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Service\QrCodeStatusService;

/**
 * 显示微信登录二维码页面控制器
 */
class LoginPageController extends AbstractController
{
    public function __construct(
        private readonly QrCodeStatusService $statusService
    ) {}

    /**
     * 显示登录二维码页面
     */
    #[Route(path: '/wechat-bot/qrcode/login/{id}', name: 'wechat_bot_qrcode_login', methods: ['GET'])]
    public function __invoke(WeChatAccount $account): Response
    {
        return $this->render('@WechatBot/qrcode/login.html.twig', [
            'account' => $account,
            'qrCodeUrl' => $account->getQrCodeUrl(),
            'deviceId' => $account->getDeviceId(),
            'status' => $account->getStatus(),
            'status_message' => $this->statusService->getStatusMessage($account->getStatus())
        ]);
    }
}
