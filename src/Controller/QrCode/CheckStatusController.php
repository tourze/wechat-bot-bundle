<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Controller\QrCode;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Service\QrCodeStatusService;
use Tourze\WechatBotBundle\Service\WeChatAccountService;

/**
 * 检查微信登录状态控制器
 */
#[WithMonologChannel(channel: 'wechat_bot')]
final class CheckStatusController extends AbstractController
{
    public function __construct(
        private readonly WeChatAccountService $accountService,
        private readonly EntityManagerInterface $entityManager,
        private readonly QrCodeStatusService $statusService,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * 检查登录状态
     */
    #[Route(path: '/wechat-bot/qrcode/status/{id}', name: 'wechat_bot_qrcode_status', methods: ['GET'])]
    public function __invoke(WeChatAccount $account): JsonResponse
    {
        try {
            // 刷新账号信息
            $this->entityManager->refresh($account);

            // 检查在线状态
            $deviceStatus = $this->accountService->checkOnlineStatus($account);

            return new JsonResponse([
                'success' => true,
                'status' => $account->getStatus(),
                'isOnline' => $deviceStatus->isOnline,
                'wechatId' => $account->getWechatId(),
                'nickname' => $account->getNickname(),
                'avatar' => $account->getAvatar(),
                'lastActiveTime' => $account->getLastActiveTime()?->format('Y-m-d H:i:s'),
                'message' => $this->statusService->getStatusMessage($account->getStatus()),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to check login status', [
                'accountId' => $account->getId(),
                'error' => $e->getMessage(),
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => '检查状态失败：' . $e->getMessage(),
            ], 500);
        }
    }
}
