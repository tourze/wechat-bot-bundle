<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Controller\QrCode;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Service\WeChatAccountService;

/**
 * 确认微信登录控制器
 */
#[WithMonologChannel(channel: 'wechat_bot')]
final class ConfirmLoginController extends AbstractController
{
    public function __construct(
        private readonly WeChatAccountService $accountService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * 确认登录
     */
    #[Route(path: '/wechat-bot/qrcode/confirm/{id}', name: 'wechat_bot_qrcode_confirm', methods: ['POST'])]
    public function __invoke(WeChatAccount $account): JsonResponse
    {
        try {
            $result = $this->accountService->confirmLogin($account);

            if ($result->isSuccess()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => '登录确认成功',
                    'account' => [
                        'wechatId' => $result->account?->getWechatId(),
                        'nickname' => $result->account?->getNickname(),
                        'avatar' => $result->account?->getAvatar(),
                    ],
                ]);
            }

            return new JsonResponse([
                'success' => false,
                'message' => $result->message,
            ], 400);
        } catch (\Exception $e) {
            $this->logger->error('Failed to confirm login', [
                'accountId' => $account->getId(),
                'error' => $e->getMessage(),
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => '确认登录失败：' . $e->getMessage(),
            ], 500);
        }
    }
}
