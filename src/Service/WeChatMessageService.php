<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\DTO\WeChatMessageData;
use Tourze\WechatBotBundle\DTO\WeChatMessageSendResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
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
class WeChatMessageService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly WeChatApiClient $apiClient,
        private readonly WeChatMessageRepository $messageRepository,
        private readonly WeChatAccountRepository $accountRepository,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * 发送文本消息
     */
    public function sendTextMessage(
        WeChatAccount $account,
        string $targetWxId,
        string $content
    ): WeChatMessageSendResult {
        try {
            $request = new SendTextMessageRequest(
                $account->getApiAccount(),
                $account->getDeviceId(),
                $targetWxId,
                $content
            );

            $response = $this->apiClient->request($request);

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
                'messageId' => $message->getId()
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
                'error' => $e->getMessage()
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
        string $imageUrl
    ): WeChatMessageSendResult {
        try {
            $request = new SendImageMessageRequest(
                $account->getApiAccount(),
                $account->getDeviceId(),
                $targetWxId,
                $imageUrl
            );

            $response = $this->apiClient->request($request);

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
                'messageId' => $message->getId()
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
                'error' => $e->getMessage()
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
        ?string $fileName = null
    ): WeChatMessageSendResult {
        try {
            $request = new SendFileMessageRequest(
                $account->getApiAccount(),
                $account->getDeviceId(),
                $targetWxId,
                $fileUrl,
                $fileName
            );

            $response = $this->apiClient->request($request);

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
                'messageId' => $message->getId()
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
                'error' => $e->getMessage()
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
        ?string $thumbUrl = null
    ): WeChatMessageSendResult {
        try {
            $request = new SendLinkMessageRequest(
                $account->getApiAccount(),
                $account->getDeviceId(),
                $targetWxId,
                $title,
                $description,
                $url,
                $thumbUrl
            );

            $response = $this->apiClient->request($request);

            $linkContent = json_encode([
                'title' => $title,
                'description' => $description,
                'url' => $url,
                'thumbUrl' => $thumbUrl
            ]);

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
                'messageId' => $message->getId()
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
                'error' => $e->getMessage()
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
                'direction' => 'outbound'
            ]);

            if ($message === null) {
                throw new MessageException('Message not found or not outbound');
            }

            $request = new RecallMessageRequest(
                $account->getApiAccount(),
                $account->getDeviceId(),
                $message->getReceiverId(),
                $messageId,
                $messageId, // newMsgId 使用相同值
                (string)time(),
            );

            $response = $this->apiClient->request($request);

            $this->logger->info('Message recalled successfully', [
                'accountId' => $account->getId(),
                'messageId' => $messageId,
                'receiverId' => $message->getReceiverId()
            ]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to recall message', [
                'accountId' => $account->getId(),
                'messageId' => $messageId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * 处理接收到的消息（用于webhook回调）
     */
    public function processInboundMessage(array $messageData): ?WeChatMessage
    {
        try {
            // 解析消息数据
            $parsedData = $this->parseMessageData($messageData);

            // 查找对应的微信账号
            $account = $this->findAccountByDeviceId($parsedData->deviceId);
            if ($account === null) {
                $this->logger->warning('Account not found for inbound message', [
                    'deviceId' => $parsedData->deviceId
                ]);
                return null;
            }

            // 检查消息是否已存在
            if ($parsedData->messageId !== null && $parsedData->messageId !== '') {
                $existingMessage = $this->messageRepository->findOneBy([
                    'messageId' => $parsedData->messageId,
                    'account' => $account
                ]);

                if ((bool) $existingMessage) {
                    return $existingMessage;
                }
            }

            // 创建新消息记录
            $message = new WeChatMessage();
            $message->setAccount($account)
                ->setMessageId($parsedData->messageId)
                ->setMessageType($parsedData->messageType)
                ->setDirection('inbound')
                ->setSenderId($parsedData->senderId)
                ->setSenderName($parsedData->senderName)
                ->setReceiverId($parsedData->receiverId)
                ->setReceiverName($parsedData->receiverName)
                ->setGroupId($parsedData->groupId)
                ->setGroupName($parsedData->groupName)
                ->setContent($parsedData->content)
                ->setMediaUrl($parsedData->mediaUrl)
                ->setMediaFileName($parsedData->mediaFileName)
                ->setRawData(json_encode($messageData))
                ->setMessageTime($parsedData->messageTime);

            $this->entityManager->persist($message);
            $this->entityManager->flush();

            $this->logger->info('Inbound message processed', [
                'messageId' => $message->getId(),
                'accountId' => $account->getId(),
                'messageType' => $parsedData->messageType,
                'senderId' => $parsedData->senderId
            ]);

            return $message;
        } catch (\Exception $e) {
            $this->logger->error('Failed to process inbound message', [
                'messageData' => $messageData,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * 获取未读消息
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
     */
    public function getConversationMessages(
        WeChatAccount $account,
        ?string $contactId = null,
        ?string $groupId = null,
        int $limit = 50
    ): array {
        if ((bool) $groupId) {
            return $this->messageRepository->findGroupMessages($account, $groupId, $limit);
        } elseif ((bool) $contactId) {
            return $this->messageRepository->findPrivateMessages($account, $contactId, $limit);
        }

        return [];
    }

    /**
     * 获取消息统计
     */
    public function getMessageStatistics(WeChatAccount $account): array
    {
        $unreadCount = $this->messageRepository->countUnreadByAccount($account);
        $typeCounts = $this->messageRepository->countByMessageType($account);

        return [
            'unread_count' => $unreadCount,
            'type_counts' => $typeCounts,
            'total_messages' => array_sum($typeCounts)
        ];
    }

    /**
     * 创建发送消息记录
     */
    private function createOutboundMessage(
        WeChatAccount $account,
        string $targetWxId,
        string $messageType,
        ?string $content = null,
        ?array $response = null,
        ?string $mediaUrl = null,
        ?string $mediaFileName = null
    ): WeChatMessage {
        $message = new WeChatMessage();
        $message->setAccount($account)
            ->setMessageType($messageType)
            ->setDirection('outbound')
            ->setSenderId($account->getWechatId())
            ->setSenderName($account->getNickname())
            ->setReceiverId($targetWxId)
            ->setContent($content)
            ->setMediaUrl($mediaUrl)
            ->setMediaFileName($mediaFileName)
            ->setRawData($response !== null ? json_encode($response) : null);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return $message;
    }

    /**
     * 解析消息数据
     */
    private function parseMessageData(array $data): WeChatMessageData
    {
        return new WeChatMessageData(
            deviceId: $data['deviceId'] ?? '',
            messageId: $data['msgId'] ?? null,
            messageType: $this->normalizeMessageType($data['type'] ?? 'unknown'),
            senderId: $data['fromUser'] ?? null,
            senderName: $data['fromUserName'] ?? null,
            receiverId: $data['toUser'] ?? null,
            receiverName: $data['toUserName'] ?? null,
            groupId: $data['groupId'] ?? null,
            groupName: $data['groupName'] ?? null,
            content: $data['content'] ?? null,
            mediaUrl: $data['mediaUrl'] ?? null,
            mediaFileName: $data['fileName'] ?? null,
            messageTime: isset($data['time']) ? new \DateTime('@' . $data['time']) : new \DateTime()
        );
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
            default => 'unknown'
        };
    }

    /**
     * 根据设备ID查找账号
     */
    private function findAccountByDeviceId(string $deviceId): ?WeChatAccount
    {
        return $this->accountRepository->findOneBy(['deviceId' => $deviceId]);
    }

    public function __toString(): string
    {
        return 'WeChatMessageService';
    }
}
