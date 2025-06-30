<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Controller\QrCode;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Service\WeChatAccountService;

/**
 * 微信退出登录控制器
 */
class LogoutController extends AbstractController
{
    public function __construct(
        private readonly WeChatAccountService $accountService,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * 退出登录
     */
    #[Route(path: '/wechat-bot/qrcode/logout/{id}', name: 'wechat_bot_qrcode_logout', methods: ['POST'])]
    public function __invoke(WeChatAccount $account): JsonResponse
    {
        try {
            $success = $this->accountService->logout($account);

            if ((bool) $success) {
                return new JsonResponse([
                    'success' => true,
                    'message' => '退出登录成功'
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'message' => '退出登录失败'
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to logout', [
                'accountId' => $account->getId(),
                'error' => $e->getMessage()
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => '退出登录失败：' . $e->getMessage()
            ], 500);
        }
    }
}
