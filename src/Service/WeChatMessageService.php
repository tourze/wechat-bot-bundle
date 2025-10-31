<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\DTO\WeChatMessageData;
use Tourze\WechatBotBundle\DTO\WeChatMessageSendResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Entity\WeChatMessage;
use Tourze\WechatBotBundle\Exception\MessageException;
use Tourze\WechatBotBundle\Repository\WeChatAccountRepository;
use Tourze\WechatBotBundle\Repository\WeChatMessageRepository;
use Tourze\WechatBotBundle\Request\RecallMessageRequest;
use Tourze\WechatBotBundle\Request\SendFileMessageRequest;
use Tourze\WechatBotBundle\Request\SendImageMessageRequest;
use Tourze\WechatBotBundle\Request\SendLinkMessageRequest;
use Tourze\WechatBotBundle\Request\SendTextMessageRequest;

/**
 * 微信消息处理服务
 *
 * 提供微信消息的完整处理功能，包括：
 * - 各类消息发送（文本、图片、文件、视频、语音等）
 * - 接收消息的处理和存储
 * - 消息查询和管理
 * - 消息统计和分析
 *
 * @author AI Assistant
 */
#[WithMonologChannel(channel: 'wechat_bot')]
#[Autoconfigure(public: true)]
readonly class WeChatMessageService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WeChatApiClient $apiClient,
        private WeChatMessageRepository $messageRepository,
        private WeChatAccountRepository $accountRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * 发送文本消息
     */
    public function sendTextMessage(
        WeChatAccount $account,
        string $targetWxId,
        string $content,
    ): WeChatMessageSendResult {
        try {
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();

            if (null === $apiAccount) {
                return new WeChatMessageSendResult(
                    success: false,
                    message: null,
                    apiResponse: null,
                    errorMessage: 'API账号不可用'
                );
            }

            if (null === $deviceId) {
                return new WeChatMessageSendResult(
                    success: false,
                    message: null,
                    apiResponse: null,
                    errorMessage: '设备ID不可用'
                );
            }

            $request = new SendTextMessageRequest(
                $apiAccount,
                $deviceId,
                $targetWxId,
                $content
            );

            $response = $this->apiClient->request($request);
            assert(is_array($response));
            /** @var array<string, mixed> $response */

            // 创建发送记录
            $message = $this->createOutboundMessage(
                account: $account,
                targetWxId: $targetWxId,
                messageType: 'text',
                content: $content,
                response: $response
            );

            $this->logger->info('Text message sent successfully', [
                'accountId' => $account->getId(),
                'targetWxId' => $targetWxId,
                'messageId' => $message->getId(),
            ]);

            return new WeChatMessageSendResult(
                success: true,
                message: $message,
                apiResponse: $response,
                errorMessage: null
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to send text message', [
                'accountId' => $account->getId(),
                'targetWxId' => $targetWxId,
                'content' => mb_substr($content, 0, 100),
                'error' => $e->getMessage(),
            ]);

            return new WeChatMessageSendResult(
                success: false,
                message: null,
                apiResponse: null,
                errorMessage: $e->getMessage()
            );
        }
    }

    /**
     * 发送图片消息
     */
    public function sendImageMessage(
        WeChatAccount $account,
        string $targetWxId,
        string $imageUrl,
    ): WeChatMessageSendResult {
        try {
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();

            if (null === $apiAccount) {
                return new WeChatMessageSendResult(
                    success: false,
                    message: null,
                    apiResponse: null,
                    errorMessage: 'API账号不可用'
                );
            }

            if (null === $deviceId) {
                return new WeChatMessageSendResult(
                    success: false,
                    message: null,
                    apiResponse: null,
                    errorMessage: '设备ID不可用'
                );
            }

            $request = new SendImageMessageRequest(
                $apiAccount,
                $deviceId,
                $targetWxId,
                $imageUrl
            );

            $response = $this->apiClient->request($request);
            assert(is_array($response));
            /** @var array<string, mixed> $response */
            $message = $this->createOutboundMessage(
                account: $account,
                targetWxId: $targetWxId,
                messageType: 'image',
                content: null,
                response: $response,
                mediaUrl: $imageUrl
            );

            $this->logger->info('Image message sent successfully', [
                'accountId' => $account->getId(),
                'targetWxId' => $targetWxId,
                'imageUrl' => $imageUrl,
                'messageId' => $message->getId(),
            ]);

            return new WeChatMessageSendResult(
                success: true,
                message: $message,
                apiResponse: $response,
                errorMessage: null
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to send image message', [
                'accountId' => $account->getId(),
                'targetWxId' => $targetWxId,
                'imageUrl' => $imageUrl,
                'error' => $e->getMessage(),
            ]);

            return new WeChatMessageSendResult(
                success: false,
                message: null,
                apiResponse: null,
                errorMessage: $e->getMessage()
            );
        }
    }

    /**
     * 发送文件消息
     */
    public function sendFileMessage(
        WeChatAccount $account,
        string $targetWxId,
        string $fileUrl,
        ?string $fileName = null,
    ): WeChatMessageSendResult {
        try {
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();

            if (null === $apiAccount) {
                return new WeChatMessageSendResult(
                    success: false,
                    message: null,
                    apiResponse: null,
                    errorMessage: 'API账号不可用'
                );
            }

            if (null === $deviceId) {
                return new WeChatMessageSendResult(
                    success: false,
                    message: null,
                    apiResponse: null,
                    errorMessage: '设备ID不可用'
                );
            }

            $request = new SendFileMessageRequest(
                $apiAccount,
                $deviceId,
                $targetWxId,
                $fileUrl,
                $fileName
            );

            $response = $this->apiClient->request($request);
            assert(is_array($response));
            /** @var array<string, mixed> $response */
            $message = $this->createOutboundMessage(
                account: $account,
                targetWxId: $targetWxId,
                messageType: 'file',
                content: null,
                response: $response,
                mediaUrl: $fileUrl,
                mediaFileName: $fileName
            );

            $this->logger->info('File message sent successfully', [
                'accountId' => $account->getId(),
                'targetWxId' => $targetWxId,
                'fileUrl' => $fileUrl,
                'fileName' => $fileName,
                'messageId' => $message->getId(),
            ]);

            return new WeChatMessageSendResult(
                success: true,
                message: $message,
                apiResponse: $response,
                errorMessage: null
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to send file message', [
                'accountId' => $account->getId(),
                'targetWxId' => $targetWxId,
                'fileUrl' => $fileUrl,
                'error' => $e->getMessage(),
            ]);

            return new WeChatMessageSendResult(
                success: false,
                message: null,
                apiResponse: null,
                errorMessage: $e->getMessage()
            );
        }
    }

    /**
     * 发送链接消息
     */
    public function sendLinkMessage(
        WeChatAccount $account,
        string $targetWxId,
        string $title,
        string $description,
        string $url,
        ?string $thumbUrl = null,
    ): WeChatMessageSendResult {
        try {
            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();

            if (null === $apiAccount) {
                return new WeChatMessageSendResult(
                    success: false,
                    message: null,
                    apiResponse: null,
                    errorMessage: 'API账号不可用'
                );
            }

            if (null === $deviceId) {
                return new WeChatMessageSendResult(
                    success: false,
                    message: null,
                    apiResponse: null,
                    errorMessage: '设备ID不可用'
                );
            }

            $request = new SendLinkMessageRequest(
                $apiAccount,
                $deviceId,
                $targetWxId,
                $title,
                $description,
                $url,
                $thumbUrl
            );

            $response = $this->apiClient->request($request);
            assert(is_array($response));
            /** @var array<string, mixed> $response */
            $linkContent = json_encode([
                'title' => $title,
                'description' => $description,
                'url' => $url,
                'thumbUrl' => $thumbUrl,
            ]);

            if (false === $linkContent) {
                $linkContent = null;
            }

            $message = $this->createOutboundMessage(
                account: $account,
                targetWxId: $targetWxId,
                messageType: 'link',
                content: $linkContent,
                response: $response
            );

            $this->logger->info('Link message sent successfully', [
                'accountId' => $account->getId(),
                'targetWxId' => $targetWxId,
                'title' => $title,
                'url' => $url,
                'messageId' => $message->getId(),
            ]);

            return new WeChatMessageSendResult(
                success: true,
                message: $message,
                apiResponse: $response,
                errorMessage: null
            );
        } catch (\Exception $e) {
            $this->logger->error('Failed to send link message', [
                'accountId' => $account->getId(),
                'targetWxId' => $targetWxId,
                'title' => $title,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return new WeChatMessageSendResult(
                success: false,
                message: null,
                apiResponse: null,
                errorMessage: $e->getMessage()
            );
        }
    }

    /**
     * 撤回消息
     */
    public function recallMessage(WeChatAccount $account, string $messageId): bool
    {
        try {
            // 从数据库查找消息记录
            $message = $this->messageRepository->findOneBy([
                'account' => $account,
                'messageId' => $messageId,
                'direction' => 'outbound',
            ]);

            if (null === $message) {
                throw new MessageException('Message not found or not outbound');
            }

            // $message 已经由 PHPDoc 声明为 WeChatMessage|null，检查 null 后确保类型

            $apiAccount = $account->getApiAccount();
            $deviceId = $account->getDeviceId();
            $receiverId = $message->getReceiverId();

            if (null === $apiAccount || null === $deviceId || null === $receiverId) {
                throw new MessageException('API账号、设备ID或接收者ID不可用');
            }

            $request = new RecallMessageRequest(
                $apiAccount,
                $deviceId,
                $receiverId,
                $messageId,
                $messageId, // newMsgId 使用相同值
                (string) time(),
            );

            $response = $this->apiClient->request($request);
            assert(is_array($response));
            /** @var array<string, mixed> $response */
            $this->logger->info('Message recalled successfully', [
                'accountId' => $account->getId(),
                'messageId' => $messageId,
                'receiverId' => $message->getReceiverId(),
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to recall message', [
                'accountId' => $account->getId(),
                'messageId' => $messageId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * 处理接收到的消息（用于webhook回调）
     */
    /**
     * @param array<string, mixed> $messageData
     */
    public function processInboundMessage(array $messageData): ?WeChatMessage
    {
        try {
            // 解析消息数据
            $parsedData = $this->parseMessageData($messageData);

            // 查找对应的微信账号
            $account = $this->findAccountByDeviceId($parsedData->deviceId);
            if (null === $account) {
                $this->logger->warning('Account not found for inbound message', [
                    'deviceId' => $parsedData->deviceId,
                ]);

                return null;
            }

            // 检查消息是否已存在
            if (null !== $parsedData->messageId && '' !== $parsedData->messageId) {
                $existingMessage = $this->messageRepository->findOneBy([
                    'messageId' => $parsedData->messageId,
                    'account' => $account,
                ]);

                if (null !== $existingMessage) {
                    // $existingMessage 已经由 PHPDoc 声明为 WeChatMessage|null，检查 null 后确保类型

                    return $existingMessage;
                }
            }

            // 创建新消息记录
            $message = new WeChatMessage();
            $message->setAccount($account);
            $message->setMessageId($parsedData->messageId);
            $message->setMessageType($parsedData->messageType);
            $message->setDirection('inbound');
            $message->setSenderId($parsedData->senderId);
            $message->setSenderName($parsedData->senderName);
            $message->setReceiverId($parsedData->receiverId);
            $message->setReceiverName($parsedData->receiverName);
            $message->setGroupId($parsedData->groupId);
            $message->setGroupName($parsedData->groupName);
            $message->setContent($parsedData->content);
            $message->setMediaUrl($parsedData->mediaUrl);
            $message->setMediaFileName($parsedData->mediaFileName);
            $message->setRawData(false !== json_encode($messageData) ? json_encode($messageData) : null);
            $message->setMessageTime($parsedData->messageTime);

            $this->entityManager->persist($message);
            $this->entityManager->flush();

            $this->logger->info('Inbound message processed', [
                'messageId' => $message->getId(),
                'accountId' => $account->getId(),
                'messageType' => $parsedData->messageType,
                'senderId' => $parsedData->senderId,
            ]);

            return $message;
        } catch (\Exception $e) {
            $this->logger->error('Failed to process inbound message', [
                'messageData' => $messageData,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * 获取未读消息
     * @return WeChatMessage[]
     */
    public function getUnreadMessages(WeChatAccount $account): array
    {
        return $this->messageRepository->findUnreadMessages($account);
    }

    /**
     * 标记消息为已读
     */
    public function markAsRead(WeChatMessage $message): void
    {
        $message->markAsRead();
        $this->entityManager->flush();
    }

    /**
     * 批量标记消息为已读
     */
    /**
     * @param array<string, mixed> $messages
     */
    public function markMultipleAsRead(array $messages): void
    {
        foreach ($messages as $message) {
            if ($message instanceof WeChatMessage) {
                $message->markAsRead();
            }
        }
        $this->entityManager->flush();
    }

    /**
     * 获取对话消息（私聊或群聊）
     * @return WeChatMessage[]
     */
    public function getConversationMessages(
        WeChatAccount $account,
        ?string $contactId = null,
        ?string $groupId = null,
        int $limit = 50,
    ): array {
        if ((bool) $groupId) {
            return $this->messageRepository->findGroupMessages($account, $groupId, $limit);
        }
        if ((bool) $contactId) {
            return $this->messageRepository->findPrivateMessages($account, $contactId, $limit);
        }

        return [];
    }

    /**
     * 获取消息统计
     */
    /**
     * @return array<string, mixed>
     */
    public function getMessageStatistics(WeChatAccount $account): array
    {
        $unreadCount = $this->messageRepository->countUnreadByAccount($account);
        $typeCounts = $this->messageRepository->countByMessageType($account);

        return [
            'unread_count' => $unreadCount,
            'type_counts' => $typeCounts,
            'total_messages' => array_sum($typeCounts),
        ];
    }

    /**
     * 创建发送消息记录
     */
    /**
     * @param array<string, mixed> $response
     */
    private function createOutboundMessage(
        WeChatAccount $account,
        string $targetWxId,
        string $messageType,
        ?string $content = null,
        ?array $response = null,
        ?string $mediaUrl = null,
        ?string $mediaFileName = null,
    ): WeChatMessage {
        $message = new WeChatMessage();
        $message->setAccount($account);
        $message->setMessageType($messageType);
        $message->setDirection('outbound');
        $message->setSenderId($account->getWechatId());
        $message->setSenderName($account->getNickname());
        $message->setReceiverId($targetWxId);
        $message->setContent($content);
        $message->setMediaUrl($mediaUrl);
        $message->setMediaFileName($mediaFileName);
        $message->setRawData(null !== $response ? (false !== json_encode($response) ? json_encode($response) : null) : null);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    /**
     * 解析消息数据
     */
    /**
     * @param array<string, mixed> $data
     */
    private function parseMessageData(array $data): WeChatMessageData
    {
        return new WeChatMessageData(
            deviceId: $this->parseStringValue($data, 'deviceId', ''),
            messageId: $this->parseOptionalStringValue($data, 'msgId'),
            messageType: $this->normalizeMessageType($this->parseStringValue($data, 'type', 'unknown')),
            senderId: $this->parseOptionalStringValue($data, 'fromUser'),
            senderName: $this->parseOptionalStringValue($data, 'fromUserName'),
            receiverId: $this->parseOptionalStringValue($data, 'toUser'),
            receiverName: $this->parseOptionalStringValue($data, 'toUserName'),
            groupId: $this->parseOptionalStringValue($data, 'groupId'),
            groupName: $this->parseOptionalStringValue($data, 'groupName'),
            content: $this->parseOptionalStringValue($data, 'content'),
            mediaUrl: $this->parseOptionalStringValue($data, 'mediaUrl'),
            mediaFileName: $this->parseOptionalStringValue($data, 'fileName'),
            messageTime: $this->parseMessageTime($data)
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function parseStringValue(array $data, string $key, string $default): string
    {
        $value = $data[$key] ?? $default;

        return is_string($value) ? $value : $default;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function parseOptionalStringValue(array $data, string $key): ?string
    {
        if (!isset($data[$key])) {
            return null;
        }

        $value = $data[$key];

        return is_string($value) ? $value : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function parseMessageTime(array $data): \DateTimeImmutable
    {
        $timeValue = $data['time'] ?? null;

        if (null === $timeValue) {
            return new \DateTimeImmutable();
        }

        if (is_numeric($timeValue) || is_string($timeValue)) {
            try {
                return new \DateTimeImmutable('@' . (string) $timeValue);
            } catch (\Exception) {
                // Use default time if parsing fails
            }
        }

        return new \DateTimeImmutable();
    }

    /**
     * 规范化消息类型
     */
    private function normalizeMessageType(string $type): string
    {
        return match ($type) {
            '1' => 'text',
            '3' => 'image',
            '34' => 'voice',
            '43' => 'video',
            '49' => 'link',
            '42' => 'card',
            '47' => 'emoji',
            default => 'unknown',
        };
    }

    /**
     * 根据设备ID查找账号
     */
    private function findAccountByDeviceId(string $deviceId): ?WeChatAccount
    {
        return $this->accountRepository->findOneBy(['deviceId' => $deviceId]);

        // $account 已经由 PHPDoc 声明为 WeChatAccount|null，检查 null 后确保类型
    }

    public function __toString(): string
    {
        return 'WeChatMessageService';
    }
}
