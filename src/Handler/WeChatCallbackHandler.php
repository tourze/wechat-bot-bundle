<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Handler;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatMessage;
use Tourze\WechatBotBundle\Repository\WeChatAccountRepository;
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
#[WithMonologChannel(channel: 'wechat_bot')]
#[Autoconfigure(public: true)]
readonly class WeChatCallbackHandler
{
    public function __construct(
        private WeChatMessageService $messageService,
        private LoggerInterface $logger,
        private WeChatAccountRepository $accountRepository,
    ) {
    }

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
            if ('' === $content) {
                return new JsonResponse(['error' => 'Empty request body'], 400);
            }

            $data = json_decode($content, true);
            if (0 !== json_last_error()) {
                return new JsonResponse(['error' => 'Invalid JSON'], 400);
            }

            // 确保 $data 是数组类型
            if (!is_array($data)) {
                return new JsonResponse(['error' => 'Invalid data format'], 400);
            }

            // 转换为 array<string, mixed> 类型
            /** @var array<string, mixed> $validatedData */
            $validatedData = array_filter($data, static fn ($key) => is_string($key), ARRAY_FILTER_USE_KEY);

            // 验证必要字段
            if (!$this->validateCallbackData($validatedData)) {
                return new JsonResponse(['error' => 'Invalid callback data'], 400);
            }

            // 处理不同类型的回调
            $result = $this->processCallback($validatedData);

            if ($result) {
                return new JsonResponse(['status' => 'success', 'message' => 'Callback processed']);
            }

            return new JsonResponse(['status' => 'error', 'message' => 'Failed to process callback'], 500);
        } catch (\Exception $e) {
            $this->logger->error('Failed to handle WeChat callback', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_body' => $request->getContent(),
            ]);

            return new JsonResponse(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * 处理回调数据
     */
    /**
     * @param array<string, mixed> $data
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
                default => $this->processUnknownCallback($data),
            };
        } catch (\Exception $e) {
            $this->logger->error('Failed to process callback', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return false;
        }
    }

    /**
     * 处理消息回调
     */
    /**
     * @param array<string, mixed> $data
     */
    private function processMessageCallback(array $data): bool
    {
        try {
            // 处理接收到的消息
            $message = $this->messageService->processInboundMessage($data);

            if (null === $message) {
                $this->logger->warning('Failed to process inbound message', ['data' => $data]);

                return false;
            }

            // 触发自动回复逻辑
            $this->triggerAutoReply($message, $data);

            $this->logger->info('Message callback processed successfully', [
                'messageId' => $message->getId(),
                'messageType' => $message->getMessageType(),
                'senderId' => $message->getSenderId(),
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to process message callback', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return false;
        }
    }

    /**
     * 处理登录回调
     */
    /**
     * @param array<string, mixed> $data
     */
    private function processLoginCallback(array $data): bool
    {
        try {
            $deviceId = $data['deviceId'] ?? null;
            if (!is_string($deviceId) || '' === $deviceId) {
                return false;
            }

            $account = $this->findAccountByDeviceId($deviceId);
            if (null === $account) {
                $this->logger->warning('Account not found for login callback', [
                    'deviceId' => $deviceId,
                ]);

                return false;
            }

            $loginStatus = $data['status'] ?? 'unknown';
            if (!is_string($loginStatus)) {
                $loginStatus = 'unknown';
            }
            $this->updateAccountByLoginStatus($account, $loginStatus, $data);
            $account->updateLastActiveTime();

            $this->logger->info('Login callback processed', [
                'accountId' => $account->getId(),
                'deviceId' => $deviceId,
                'status' => $loginStatus,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to process login callback', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return false;
        }
    }

    /**
     * 根据登录状态更新账号信息
     */
    /**
     * @param array<string, mixed> $data
     */
    private function updateAccountByLoginStatus(WeChatAccount $account, string $loginStatus, array $data): void
    {
        if ($this->isOnlineStatus($loginStatus)) {
            $this->updateAccountAsOnline($account, $data);
        } elseif ($this->isOfflineStatus($loginStatus)) {
            $account->markAsOffline();
        }
    }

    /**
     * 检查是否为在线状态
     */
    private function isOnlineStatus(string $status): bool
    {
        return 'success' === $status || 'online' === $status;
    }

    /**
     * 检查是否为离线状态
     */
    private function isOfflineStatus(string $status): bool
    {
        return 'logout' === $status || 'offline' === $status;
    }

    /**
     * 更新账号为在线状态
     */
    /**
     * @param array<string, mixed> $data
     */
    private function updateAccountAsOnline(WeChatAccount $account, array $data): void
    {
        $account->markAsOnline();

        if (array_key_exists('wxId', $data)) {
            $wxId = $data['wxId'];
            if (is_string($wxId)) {
                $account->setWechatId($wxId);
            }
        }
        if (array_key_exists('nickname', $data)) {
            $nickname = $data['nickname'];
            if (is_string($nickname)) {
                $account->setNickname($nickname);
            }
        }
        if (array_key_exists('avatar', $data)) {
            $avatar = $data['avatar'];
            if (is_string($avatar)) {
                $account->setAvatar($avatar);
            }
        }

        $account->setLastLoginTime(new \DateTimeImmutable());
    }

    /**
     * 处理状态回调
     */
    /**
     * @param array<string, mixed> $data
     */
    private function processStatusCallback(array $data): bool
    {
        try {
            $deviceId = $data['deviceId'] ?? null;
            if (!is_string($deviceId) || '' === $deviceId) {
                return false;
            }

            $account = $this->findAccountByDeviceId($deviceId);
            if (null === $account) {
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
                'status' => $status,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to process status callback', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return false;
        }
    }

    /**
     * 处理好友请求回调
     */
    /**
     * @param array<string, mixed> $data
     */
    private function processFriendRequestCallback(array $data): bool
    {
        try {
            $this->logger->info('Friend request callback received', [
                'fromUser' => $data['fromUser'] ?? null,
                'content' => $data['content'] ?? null,
            ]);

            // 这里可以添加自动同意好友请求的逻辑
            // 或者记录好友请求供后续手动处理

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to process friend request callback', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return false;
        }
    }

    /**
     * 处理群邀请回调
     */
    /**
     * @param array<string, mixed> $data
     */
    private function processGroupInviteCallback(array $data): bool
    {
        try {
            $this->logger->info('Group invite callback received', [
                'groupId' => $data['groupId'] ?? null,
                'inviter' => $data['inviter'] ?? null,
            ]);

            // 这里可以添加自动同意群邀请的逻辑
            // 或者记录群邀请供后续手动处理

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to process group invite callback', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return false;
        }
    }

    /**
     * 处理未知类型回调
     */
    /**
     * @param array<string, mixed> $data
     */
    private function processUnknownCallback(array $data): bool
    {
        $this->logger->warning('Unknown callback type received', [
            'type' => $data['type'] ?? 'unknown',
            'data' => $data,
        ]);

        return true; // 返回true避免重复回调
    }

    /**
     * 触发自动回复逻辑
     */
    /**
     * @param array<string, mixed> $originalData
     */
    private function triggerAutoReply(WeChatMessage $message, array $originalData): void
    {
        try {
            // 只对接收到的文本消息进行自动回复
            if (!$message->isInbound() || !$message->isTextMessage()) {
                return;
            }

            $content = $message->getContent();
            if (null === $content || '' === $content) {
                return;
            }

            // 简单的自动回复逻辑示例
            $autoReply = $this->generateAutoReply($content);
            if (null === $autoReply || '' === $autoReply) {
                return;
            }

            // 发送自动回复
            $account = $message->getAccount();
            $targetWxId = $message->getSenderId();

            if ((bool) $targetWxId) {
                $this->messageService->sendTextMessage($account, $targetWxId, $autoReply);
                $message->markAsReplied();
            }
        } catch (\Exception $e) {
            $this->logger->error('Failed to trigger auto reply', [
                'messageId' => $message->getId(),
                'error' => $e->getMessage(),
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
            default => null,
        };
    }

    /**
     * 验证回调数据
     */
    /**
     * @param array<string, mixed> $data
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
            default => true,
        };
    }

    /**
     * 根据设备ID查找账号
     */
    private function findAccountByDeviceId(string $deviceId): ?WeChatAccount
    {
        $account = $this->accountRepository->findOneBy(['deviceId' => $deviceId]);

        return $account instanceof WeChatAccount ? $account : null;
    }

    public function __toString(): string
    {
        return 'WeChatCallbackHandler';
    }
}
