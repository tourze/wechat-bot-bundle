<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Handler;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tourze\WechatBotBundle\Entity\WeChatMessage;
use Tourze\WechatBotBundle\Service\WeChatAccountService;
use Tourze\WechatBotBundle\Service\WeChatMessageService;

/**
 * 微信消息回调处理器
 *
 * 处理来自微信API的消息回调，包括：
 * - 接收各类消息回调
 * - 消息数据解析和验证
 * - 消息处理和存储
 * - 自动回复逻辑
 * - 异常处理和日志记录
 *
 * @author AI Assistant
 */
class WeChatCallbackHandler
{
    public function __construct(
        private readonly WeChatMessageService $messageService,
        private readonly WeChatAccountService $accountService,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * 处理微信消息回调
     */
    public function handleCallback(Request $request): Response
    {
        try {
            // 验证请求方法
            if (!$request->isMethod('POST')) {
                return new JsonResponse(['error' => 'Method not allowed'], 405);
            }

            // 获取回调数据
            $content = $request->getContent();
            if (empty($content)) {
                return new JsonResponse(['error' => 'Empty request body'], 400);
            }

            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse(['error' => 'Invalid JSON'], 400);
            }

            // 验证必要字段
            if (!$this->validateCallbackData($data)) {
                return new JsonResponse(['error' => 'Invalid callback data'], 400);
            }

            // 处理不同类型的回调
            $result = $this->processCallback($data);

            if ($result) {
                return new JsonResponse(['status' => 'success', 'message' => 'Callback processed']);
            } else {
                return new JsonResponse(['status' => 'error', 'message' => 'Failed to process callback'], 500);
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to handle WeChat callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_body' => $request->getContent()
            ]);

            return new JsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * 处理回调数据
     */
    private function processCallback(array $data): bool
    {
        try {
            // 根据回调类型处理
            $type = $data['type'] ?? 'message';

            return match ($type) {
                'message' => $this->processMessageCallback($data),
                'login' => $this->processLoginCallback($data),
                'status' => $this->processStatusCallback($data),
                'friend_request' => $this->processFriendRequestCallback($data),
                'group_invite' => $this->processGroupInviteCallback($data),
                default => $this->processUnknownCallback($data)
            };
        } catch (\Exception $e) {
            $this->logger->error('Failed to process callback', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return false;
        }
    }

    /**
     * 处理消息回调
     */
    private function processMessageCallback(array $data): bool
    {
        try {
            // 处理接收到的消息
            $message = $this->messageService->processInboundMessage($data);

            if (!$message) {
                $this->logger->warning('Failed to process inbound message', ['data' => $data]);
                return false;
            }

            // 触发自动回复逻辑
            $this->triggerAutoReply($message, $data);

            $this->logger->info('Message callback processed successfully', [
                'messageId' => $message->getId(),
                'messageType' => $message->getMessageType(),
                'senderId' => $message->getSenderId()
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to process message callback', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return false;
        }
    }

    /**
     * 处理登录回调
     */
    private function processLoginCallback(array $data): bool
    {
        try {
            $deviceId = $data['deviceId'] ?? null;
            if (!$deviceId) {
                return false;
            }

            // 更新账号登录状态
            $account = $this->findAccountByDeviceId($deviceId);
            if (!$account) {
                $this->logger->warning('Account not found for login callback', [
                    'deviceId' => $deviceId
                ]);
                return false;
            }

            // 根据登录状态更新账号
            $loginStatus = $data['status'] ?? 'unknown';

            if ($loginStatus === 'success' || $loginStatus === 'online') {
                $account->markAsOnline();
                if (isset($data['wxId'])) {
                    $account->setWechatId($data['wxId']);
                }
                if (isset($data['nickname'])) {
                    $account->setNickname($data['nickname']);
                }
                if (isset($data['avatar'])) {
                    $account->setAvatar($data['avatar']);
                }
                $account->setLastLoginTime(new \DateTime());
            } elseif ($loginStatus === 'logout' || $loginStatus === 'offline') {
                $account->markAsOffline();
            }

            $account->updateLastActiveTime();

            $this->logger->info('Login callback processed', [
                'accountId' => $account->getId(),
                'deviceId' => $deviceId,
                'status' => $loginStatus
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to process login callback', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return false;
        }
    }

    /**
     * 处理状态回调
     */
    private function processStatusCallback(array $data): bool
    {
        try {
            $deviceId = $data['deviceId'] ?? null;
            if (!$deviceId) {
                return false;
            }

            $account = $this->findAccountByDeviceId($deviceId);
            if (!$account) {
                return false;
            }

            $status = $data['status'] ?? 'unknown';

            // 更新账号状态
            switch ($status) {
                case 'online':
                    $account->markAsOnline();
                    break;
                case 'offline':
                    $account->markAsOffline();
                    break;
                case 'expired':
                    $account->markAsExpired();
                    break;
            }

            $account->updateLastActiveTime();

            $this->logger->info('Status callback processed', [
                'accountId' => $account->getId(),
                'deviceId' => $deviceId,
                'status' => $status
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to process status callback', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return false;
        }
    }

    /**
     * 处理好友请求回调
     */
    private function processFriendRequestCallback(array $data): bool
    {
        try {
            $this->logger->info('Friend request callback received', [
                'fromUser' => $data['fromUser'] ?? null,
                'content' => $data['content'] ?? null
            ]);

            // 这里可以添加自动同意好友请求的逻辑
            // 或者记录好友请求供后续手动处理

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to process friend request callback', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return false;
        }
    }

    /**
     * 处理群邀请回调
     */
    private function processGroupInviteCallback(array $data): bool
    {
        try {
            $this->logger->info('Group invite callback received', [
                'groupId' => $data['groupId'] ?? null,
                'inviter' => $data['inviter'] ?? null
            ]);

            // 这里可以添加自动同意群邀请的逻辑
            // 或者记录群邀请供后续手动处理

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to process group invite callback', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return false;
        }
    }

    /**
     * 处理未知类型回调
     */
    private function processUnknownCallback(array $data): bool
    {
        $this->logger->warning('Unknown callback type received', [
            'type' => $data['type'] ?? 'unknown',
            'data' => $data
        ]);

        return true; // 返回true避免重复回调
    }

    /**
     * 触发自动回复逻辑
     */
    private function triggerAutoReply(WeChatMessage $message, array $originalData): void
    {
        try {
            // 只对接收到的文本消息进行自动回复
            if (!$message->isInbound() || !$message->isTextMessage()) {
                return;
            }

            $content = $message->getContent();
            if (!$content) {
                return;
            }

            // 简单的自动回复逻辑示例
            $autoReply = $this->generateAutoReply($content);
            if (!$autoReply) {
                return;
            }

            // 发送自动回复
            $account = $message->getAccount();
            $targetWxId = $message->getSenderId();

            if ($targetWxId) {
                $this->messageService->sendTextMessage($account, $targetWxId, $autoReply);
                $message->markAsReplied();
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to trigger auto reply', [
                'messageId' => $message->getId(),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 生成自动回复内容
     */
    private function generateAutoReply(string $content): ?string
    {
        $content = trim(strtolower($content));

        // 简单的关键词匹配自动回复
        return match (true) {
            str_contains($content, 'hello') || str_contains($content, '你好') => '你好！很高兴收到您的消息。',
            str_contains($content, 'help') || str_contains($content, '帮助') => '如需帮助，请联系我们的客服人员。',
            str_contains($content, 'time') || str_contains($content, '时间') => '当前时间：' . date('Y-m-d H:i:s'),
            default => null
        };
    }

    /**
     * 验证回调数据
     */
    private function validateCallbackData(array $data): bool
    {
        // 基本字段验证
        if (!isset($data['deviceId'])) {
            return false;
        }

        // 根据回调类型进行不同的验证
        $type = $data['type'] ?? 'message';

        return match ($type) {
            'message' => isset($data['fromUser']) || isset($data['toUser']),
            'login', 'status' => true,
            'friend_request' => isset($data['fromUser']),
            'group_invite' => isset($data['groupId']),
            default => true
        };
    }

    /**
     * 根据设备ID查找账号
     */
    private function findAccountByDeviceId(string $deviceId): ?\Tourze\WechatBotBundle\Entity\WeChatAccount
    {
        // 这里应该使用Repository，但为了简化先直接返回null
        // 实际使用时需要注入AccountRepository
        return null;
    }

    public function __toString(): string
    {
        return 'WeChatCallbackHandler';
    }
}
