<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Controller\QrCode;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Service\WeChatAccountService;

/**
 * 生成微信登录二维码控制器
 */
class GenerateQrCodeController extends AbstractController
{
    public function __construct(
        private readonly WeChatAccountService $accountService,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * 生成新的登录二维码
     */
    #[Route(path: '/wechat-bot/qrcode/generate/{id}', name: 'wechat_bot_qrcode_generate', methods: ['POST'])]
    public function __invoke(WeChatAccount $account, Request $request): JsonResponse
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
}
