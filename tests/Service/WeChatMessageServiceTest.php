<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\DTO\WeChatMessageSendResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Entity\WeChatMessage;
use Tourze\WechatBotBundle\Repository\WeChatAccountRepository;
use Tourze\WechatBotBundle\Repository\WeChatMessageRepository;
use Tourze\WechatBotBundle\Service\WeChatMessageService;

/**
 * 微信消息服务测试
 */
class WeChatMessageServiceTest extends TestCase
{
    private WeChatMessageService $service;
    private EntityManagerInterface&MockObject $entityManager;
    private WeChatApiClient&MockObject $apiClient;
    private WeChatMessageRepository&MockObject $messageRepository;
    private WeChatAccountRepository&MockObject $accountRepository;
    private LoggerInterface&MockObject $logger;

    /**
     * 测试成功发送文本消息
     */
    public function testSendTextMessageSuccess(): void
    {
        // 准备测试数据
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');
        $account->method('getId')->willReturn(1);

        $targetWxId = 'target_user123';
        $content = '测试消息内容';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
            'data' => [
                'messageId' => 'msg123'
            ]
        ];

        // 配置模拟对象
        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(WeChatMessage::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->logger
            ->expects($this->once())
            ->method('info');

        // 执行测试
        $result = $this->service->sendTextMessage($account, $targetWxId, $content);

        // 验证结果
        $this->assertInstanceOf(WeChatMessageSendResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertNotNull($result->message);
        $this->assertEquals($mockResponse, $result->apiResponse);
        $this->assertNull($result->errorMessage);
    }

    /**
     * 测试发送文本消息失败
     */
    public function testSendTextMessageFailure(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');
        $account->method('getId')->willReturn(1);

        // 模拟API调用异常
        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new \RuntimeException('API调用失败'));

        $this->logger
            ->expects($this->once())
            ->method('error');

        // 执行测试
        $result = $this->service->sendTextMessage($account, 'target123', '测试');

        // 验证结果
        $this->assertInstanceOf(WeChatMessageSendResult::class, $result);
        $this->assertFalse($result->success);
        $this->assertNull($result->message);
        $this->assertNull($result->apiResponse);
        $this->assertEquals('API调用失败', $result->errorMessage);
    }

    /**
     * 测试发送图片消息
     */
    public function testSendImageMessage(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');
        $account->method('getId')->willReturn(1);

        $targetWxId = 'target_user123';
        $imageUrl = 'https://example.com/image.jpg';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success'
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->logger
            ->expects($this->once())
            ->method('info');

        // 执行测试
        $result = $this->service->sendImageMessage($account, $targetWxId, $imageUrl);

        // 验证结果
        $this->assertInstanceOf(WeChatMessageSendResult::class, $result);
        $this->assertTrue($result->success);
    }

    /**
     * 测试发送文件消息
     */
    public function testSendFileMessage(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');
        $account->method('getId')->willReturn(1);

        $targetWxId = 'target_user123';
        $fileUrl = 'https://example.com/file.pdf';
        $fileName = 'test.pdf';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success'
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->logger
            ->expects($this->once())
            ->method('info');

        // 执行测试
        $result = $this->service->sendFileMessage($account, $targetWxId, $fileUrl, $fileName);

        // 验证结果
        $this->assertInstanceOf(WeChatMessageSendResult::class, $result);
        $this->assertTrue($result->success);
    }

    /**
     * 测试发送链接消息
     */
    public function testSendLinkMessage(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');
        $account->method('getId')->willReturn(1);

        $targetWxId = 'target_user123';
        $title = '测试链接标题';
        $description = '测试链接描述';
        $url = 'https://example.com';
        $thumbUrl = 'https://example.com/thumb.jpg';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success'
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->logger
            ->expects($this->once())
            ->method('info');

        // 执行测试
        $result = $this->service->sendLinkMessage(
            $account,
            $targetWxId,
            $title,
            $description,
            $url,
            $thumbUrl
        );

        // 验证结果
        $this->assertInstanceOf(WeChatMessageSendResult::class, $result);
        $this->assertTrue($result->success);
    }

    /**
     * 测试撤回消息
     */
    public function testRecallMessage(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $messageId = 'msg123';

        // 模拟数据库中的消息
        $message = $this->createMock(WeChatMessage::class);
        $message->method('getReceiverId')->willReturn('receiver123');

        $this->messageRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'account' => $account,
                'messageId' => $messageId,
                'direction' => 'outbound'
            ])
            ->willReturn($message);

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn(['success' => true]);

        $this->logger
            ->expects($this->once())
            ->method('info');

        // 执行测试
        $result = $this->service->recallMessage($account, $messageId);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试处理接收的消息
     */
    public function testProcessInboundMessage(): void
    {
        $messageData = [
            'deviceId' => 'device123',
            'msgId' => 'incoming_msg123',
            'type' => '1', // text message
            'fromUser' => 'sender123',
            'fromUserName' => '发送者',
            'toUser' => 'device123',
            'content' => '收到的消息',
            'time' => time()
        ];

        $account = $this->createMock(WeChatAccount::class);
        $account->method('getId')->willReturn(1);

        // 模拟 accountRepository
        $this->accountRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['deviceId' => 'device123'])
            ->willReturn($account);

        // 模拟消息不存在（第一次查找）
        $this->messageRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(WeChatMessage::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->logger
            ->expects($this->once())
            ->method('info');

        // 执行测试
        $result = $this->service->processInboundMessage($messageData);

        // 验证结果
        $this->assertInstanceOf(WeChatMessage::class, $result);
    }

    /**
     * 测试获取未读消息
     */
    public function testGetUnreadMessages(): void
    {
        $account = $this->createMock(WeChatAccount::class);

        $expectedMessages = [
            $this->createMock(WeChatMessage::class),
            $this->createMock(WeChatMessage::class)
        ];

        $this->messageRepository
            ->expects($this->once())
            ->method('findUnreadMessages')
            ->with($account)
            ->willReturn($expectedMessages);

        // 执行测试
        $result = $this->service->getUnreadMessages($account);

        // 验证结果
        $this->assertCount(2, $result);
    }

    /**
     * 测试标记消息为已读
     */
    public function testMarkAsRead(): void
    {
        $message = $this->createMock(WeChatMessage::class);

        $message
            ->expects($this->once())
            ->method('markAsRead')
            ->willReturnSelf();

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        // 执行测试
        $this->service->markAsRead($message);
    }

    /**
     * 测试获取对话消息
     */
    public function testGetConversationMessages(): void
    {
        $account = $this->createMock(WeChatAccount::class);
        $contactId = 'contact123';

        $expectedMessages = [
            $this->createMock(WeChatMessage::class),
            $this->createMock(WeChatMessage::class)
        ];

        $this->messageRepository
            ->expects($this->once())
            ->method('findPrivateMessages')
            ->with($account, $contactId, 50)
            ->willReturn($expectedMessages);

        // 执行测试
        $result = $this->service->getConversationMessages($account, $contactId);

        // 验证结果
        $this->assertCount(2, $result);
    }

    /**
     * 测试获取消息统计
     */
    public function testGetMessageStatistics(): void
    {
        $account = $this->createMock(WeChatAccount::class);

        $this->messageRepository
            ->expects($this->once())
            ->method('countUnreadByAccount')
            ->with($account)
            ->willReturn(5);

        $typeCounts = ['text' => 100, 'image' => 20, 'voice' => 10];
        $this->messageRepository
            ->expects($this->once())
            ->method('countByMessageType')
            ->with($account)
            ->willReturn($typeCounts);

        // 执行测试
        $result = $this->service->getMessageStatistics($account);

        // 验证结果
        $this->assertEquals(5, $result['unread_count']);
        $this->assertEquals($typeCounts, $result['type_counts']);
        $this->assertEquals(130, $result['total_messages']);
    }

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->apiClient = $this->createMock(WeChatApiClient::class);
        $this->messageRepository = $this->createMock(WeChatMessageRepository::class);
        $this->accountRepository = $this->createMock(WeChatAccountRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new WeChatMessageService(
            $this->entityManager,
            $this->apiClient,
            $this->messageRepository,
            $this->accountRepository,
            $this->logger
        );
    }
}
