<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Service\WeChatAccountService;

/**
 * 微信登录二维码控制器
 *
 * 提供微信账号二维码登录的页面和API：
 * - 显示登录二维码页面
 * - 获取二维码状态API
 * - 刷新二维码API
 * - 登录状态检查API
 *
 * @author AI Assistant
 */
#[Route('/wechat-bot/qrcode', name: 'wechat_bot_qrcode_')]
class QrCodeController extends AbstractController
{
    public function __construct(
        private readonly WeChatAccountService $accountService,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * 显示登录二维码页面
     */
    #[Route('/login/{id}', name: 'login', methods: ['GET'])]
    public function loginPage(WeChatAccount $account): Response
    {
        return $this->render('@WechatBot/qrcode/login.html.twig', [
            'account' => $account,
            'qrCodeUrl' => $account->getQrCodeUrl(),
            'deviceId' => $account->getDeviceId(),
            'status' => $account->getStatus(),
            'status_message' => $this->getStatusMessage($account->getStatus())
        ]);
    }

    /**
     * 生成新的登录二维码
     */
    #[Route('/generate/{id}', name: 'generate', methods: ['POST'])]
    public function generateQrCode(WeChatAccount $account, Request $request): JsonResponse
    {
        try {
            // 获取省市参数
            $province = $request->request->get('province', '广东');
            $city = $request->request->get('city', '深圳');
            $proxy = $request->request->get('proxy');

            // 开始登录流程
            $result = $this->accountService->startLogin($account, $province, $city, $proxy);

            if ($result->isSuccess()) {
                return new JsonResponse([
                    'success' => true,
                    'qrCodeUrl' => $result->qrCodeUrl,
                    'message' => '二维码生成成功'
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'message' => $result->message,
                    'error' => $result->error
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to generate QR code', [
                'accountId' => $account->getId(),
                'error' => $e->getMessage()
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => '生成二维码失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 检查登录状态
     */
    #[Route('/status/{id}', name: 'status', methods: ['GET'])]
    public function checkStatus(WeChatAccount $account): JsonResponse
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
                'message' => $this->getStatusMessage($account->getStatus())
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to check login status', [
                'accountId' => $account->getId(),
                'error' => $e->getMessage()
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => '检查状态失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 确认登录
     */
    #[Route('/confirm/{id}', name: 'confirm', methods: ['POST'])]
    public function confirmLogin(WeChatAccount $account): JsonResponse
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
                        'avatar' => $result->account?->getAvatar()
                    ]
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'message' => $result->message
                ], 400);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to confirm login', [
                'accountId' => $account->getId(),
                'error' => $e->getMessage()
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => '确认登录失败：' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 退出登录
     */
    #[Route('/logout/{id}', name: 'logout', methods: ['POST'])]
    public function logout(WeChatAccount $account): JsonResponse
    {
        try {
            $success = $this->accountService->logout($account);

            if ($success) {
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

    /**
     * 获取状态消息
     */
    private function getStatusMessage(string $status): string
    {
        return match ($status) {
            'pending_login' => '等待扫码登录',
            'online' => '已登录，设备在线',
            'offline' => '设备离线',
            'expired' => '登录已过期，需要重新登录',
            default => '状态未知'
        };
    }
}
