<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
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
 *
 * @internal
 */
#[CoversClass(WeChatMessageService::class)]
#[RunTestsInSeparateProcesses]
final class WeChatMessageServiceTest extends AbstractIntegrationTestCase
{
    private WeChatMessageService $service;

    /** @var WeChatApiClient&MockObject */
    private WeChatApiClient $apiClient;

    /**
     * 测试成功发送文本消息
     */
    public function testSendTextMessageSuccess(): void
    {
        // 准备测试数据 - 创建真实的API账户和账户
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device123');
        $account->setWechatId('test_wx_id');
        $account->setNickname('Test User');
        $account->setStatus('active');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $targetWxId = 'target_user123';
        $content = '测试消息内容';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
            'data' => [
                'messageId' => 'msg123',
            ],
        ];

        // 配置模拟对象
        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        // 执行测试
        $result = $this->service->sendTextMessage($account, $targetWxId, $content);

        // 验证结果
        $this->assertInstanceOf(WeChatMessageSendResult::class, $result);
        if (!$result->success) {
            self::fail(sprintf(
                'Expected success but got failure. Error: %s, Response: %s',
                $result->errorMessage ?? 'unknown',
                json_encode($result->apiResponse ?? [])
            ));
        }
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
        // 创建真实的API账户和账户
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device123');
        $account->setWechatId('test_wx_id');
        $account->setNickname('Test User');
        $account->setStatus('active');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        // 模拟API调用异常
        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new \RuntimeException('API调用失败'))
        ;

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
        // 创建真实的API账户和账户
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device123');
        $account->setWechatId('test_wx_id');
        $account->setNickname('Test User');
        $account->setStatus('active');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $targetWxId = 'target_user123';
        $imageUrl = 'https://example.com/image.jpg';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        // 执行测试
        $result = $this->service->sendImageMessage($account, $targetWxId, $imageUrl);

        // 验证结果
        $this->assertInstanceOf(WeChatMessageSendResult::class, $result);
        if (!$result->success) {
            self::fail(sprintf(
                'Expected success but got failure. Error: %s, Response: %s',
                $result->errorMessage ?? 'unknown',
                json_encode($result->apiResponse ?? [])
            ));
        }
        $this->assertTrue($result->success);
    }

    /**
     * 测试发送文件消息
     */
    public function testSendFileMessage(): void
    {
        // 创建真实的API账户和账户
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device123');
        $account->setWechatId('test_wx_id');
        $account->setNickname('Test User');
        $account->setStatus('active');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $targetWxId = 'target_user123';
        $fileUrl = 'https://example.com/file.pdf';
        $fileName = 'test.pdf';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

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
        // 创建真实的API账户和账户
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device123');
        $account->setWechatId('test_wx_id');
        $account->setNickname('Test User');
        $account->setStatus('active');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $targetWxId = 'target_user123';
        $title = '测试链接标题';
        $description = '测试链接描述';
        $url = 'https://example.com';
        $thumbUrl = 'https://example.com/thumb.jpg';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

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

        // 如果失败，输出错误信息以便调试
        if (!$result->success) {
            self::fail(sprintf(
                'Expected success but got failure. Error: %s, Response: %s',
                $result->errorMessage ?? 'unknown',
                json_encode($result->apiResponse ?? [])
            ));
        }

        $this->assertTrue($result->success);
    }

    /**
     * 测试撤回消息
     */
    public function testRecallMessage(): void
    {
        // 创建真实的API账户和账户
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device123');
        $account->setWechatId('test_wx_id');
        $account->setNickname('Test User');
        $account->setStatus('active');

        self::getEntityManager()->persist($account);

        $messageId = 'msg123';

        // 创建真实的消息记录
        $message = new WeChatMessage();
        $message->setAccount($account);
        $message->setMessageId($messageId);
        $message->setDirection('outbound');
        $message->setMessageType('text');
        $message->setSenderId($account->getDeviceId());
        $message->setReceiverId('receiver123');
        $message->setContent('测试撤回消息');
        $message->setMessageTime(new \DateTimeImmutable());
        $message->setIsRead(true);

        self::getEntityManager()->persist($message);
        self::getEntityManager()->flush();

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn(['success' => true])
        ;

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
        // 创建真实的API账户和账户
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device123');
        $account->setWechatId('test_wx_id');
        $account->setNickname('Test User');
        $account->setStatus('active');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $messageData = [
            'deviceId' => 'device123',
            'msgId' => 'incoming_msg123',
            'type' => '1', // text message
            'fromUser' => 'sender123',
            'fromUserName' => '发送者',
            'toUser' => 'device123',
            'content' => '收到的消息',
            'time' => time(),
        ];

        // 执行测试
        $result = $this->service->processInboundMessage($messageData);

        // 验证结果
        $this->assertInstanceOf(WeChatMessage::class, $result);
        $this->assertEquals('incoming_msg123', $result->getMessageId());
        $this->assertEquals('inbound', $result->getDirection());
    }

    /**
     * 测试获取未读消息
     */
    public function testGetUnreadMessages(): void
    {
        // 创建真实的API账户和账户
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device456');
        $account->setWechatId('test_wx_id_2');
        $account->setNickname('Test User 2');
        $account->setStatus('active');

        self::getEntityManager()->persist($account);

        // 创建一些未读消息
        $message1 = new WeChatMessage();
        $message1->setAccount($account);
        $message1->setMessageId('msg1');
        $message1->setDirection('inbound');
        $message1->setMessageType('text');
        $message1->setSenderId('sender1');
        $message1->setReceiverId($account->getDeviceId());
        $message1->setContent('未读消恡1');
        $message1->setMessageTime(new \DateTimeImmutable());
        $message1->setIsRead(false);

        $message2 = new WeChatMessage();
        $message2->setAccount($account);
        $message2->setMessageId('msg2');
        $message2->setDirection('inbound');
        $message2->setMessageType('text');
        $message2->setSenderId('sender2');
        $message2->setReceiverId($account->getDeviceId());
        $message2->setContent('未读消恡2');
        $message2->setMessageTime(new \DateTimeImmutable());
        $message2->setIsRead(false);

        self::getEntityManager()->persist($message1);
        self::getEntityManager()->persist($message2);
        self::getEntityManager()->flush();

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
        /**
         * 使用 WeChatMessage 具体类进行 Mock 的原因：
         * 1. 实体特性：WeChatMessage 是消息聚合根，封装了消息的完整生命周期
         * 2. 业务逻辑：需要测试消息状态转换、关联关系查询等核心业务行为
         * 3. 架构约束：作为 Doctrine 实体，没有对应的抽象接口
         * 4. 测试有效性：Mock 允许精确控制消息状态，确保测试的可重复性和隔离性
         */
        /** @var WeChatMessage&MockObject $message */
        $message = $this->createMock(WeChatMessage::class);

        $message
            ->expects($this->once())
            ->method('markAsRead')
        ;

        $message
            ->method('isRead')
            ->willReturn(true)
        ;

        // 执行测试 - 此方法主要验证没有异常抛出
        $this->service->markAsRead($message);

        // 验证消息被标记为已读
        $this->assertTrue($message->isRead());
    }

    /**
     * 测试获取对话消息
     */
    public function testGetConversationMessages(): void
    {
        // 创建真实的API账户和账户
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device789');
        $account->setWechatId('test_wx_id_3');
        $account->setNickname('Test User 3');
        $account->setStatus('active');

        self::getEntityManager()->persist($account);

        $contactId = 'contact123';

        // 创建一些对话消息
        $message1 = new WeChatMessage();
        $message1->setAccount($account);
        $message1->setMessageId('conv_msg1');
        $message1->setDirection('inbound');
        $message1->setMessageType('text');
        $message1->setSenderId($contactId);
        $message1->setReceiverId($account->getDeviceId());
        $message1->setContent('对话消恡1');
        $message1->setMessageTime(new \DateTimeImmutable());
        $message1->setIsRead(true);

        $message2 = new WeChatMessage();
        $message2->setAccount($account);
        $message2->setMessageId('conv_msg2');
        $message2->setDirection('outbound');
        $message2->setMessageType('text');
        $message2->setSenderId($account->getDeviceId());
        $message2->setReceiverId($contactId);
        $message2->setContent('对话消恡2');
        $message2->setMessageTime(new \DateTimeImmutable());
        $message2->setIsRead(true);

        self::getEntityManager()->persist($message1);
        self::getEntityManager()->persist($message2);
        self::getEntityManager()->flush();

        // 执行测试
        $result = $this->service->getConversationMessages($account, $contactId);

        // 验证结果 - 应该返回2条消息
        $this->assertCount(2, $result);
    }

    /**
     * 测试获取消息统计
     */
    public function testGetMessageStatistics(): void
    {
        // 创建真实的API账户和账户
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device999');
        $account->setWechatId('test_wx_id_4');
        $account->setNickname('Test User 4');
        $account->setStatus('active');

        self::getEntityManager()->persist($account);

        // 创建一些测试消息
        $messages = [
            ['type' => 'text', 'read' => false],
            ['type' => 'text', 'read' => false],
            ['type' => 'text', 'read' => true],
            ['type' => 'image', 'read' => true],
            ['type' => 'voice', 'read' => true],
        ];

        foreach ($messages as $index => $msgData) {
            $message = new WeChatMessage();
            $message->setAccount($account);
            $message->setMessageId('stat_msg' . $index);
            $message->setDirection('inbound');
            $message->setMessageType($msgData['type']);
            $message->setSenderId('sender_' . $index);
            $message->setReceiverId($account->getDeviceId());
            $message->setContent('统计消息' . $index);
            $message->setMessageTime(new \DateTimeImmutable());
            $message->setIsRead($msgData['read']);

            self::getEntityManager()->persist($message);
        }

        self::getEntityManager()->flush();

        // 执行测试
        $result = $this->service->getMessageStatistics($account);

        // 验证结果
        $this->assertEquals(2, $result['unread_count']); // 2条未读
        $this->assertEquals(5, $result['total_messages']); // 总共5条
        $this->assertIsArray($result['type_counts']);
        $this->assertEquals(3, $result['type_counts']['text']); // 3条文本消息
        $this->assertEquals(1, $result['type_counts']['image']); // 1条图片消息
        $this->assertEquals(1, $result['type_counts']['voice']); // 1条语音消息
    }

    /**
     * 测试批量标记消息为已读
     */
    public function testMarkMultipleAsRead(): void
    {
        /*
         * 使用具体类创建 Mock 对象的原因：
         * 1) WeChatMessage 是实体类，没有对应的接口，只能使用具体类进行 Mock
         * 2) 在单元测试中模拟消息实体是必要的，用于测试批量标记已读功能
         * 3) 该实体类封装了消息的完整生命周期，Mock 提供可控的测试环境
         */
        /** @var WeChatMessage&MockObject $message1 */
        $message1 = $this->createMock(WeChatMessage::class);
        /*
         * 使用具体类创建 Mock 对象的原因：
         * 1) WeChatMessage 是实体类，没有对应的接口，只能使用具体类进行 Mock
         * 2) 在单元测试中模拟消息实体是必要的，用于测试批量标记已读功能
         * 3) 该实体类封装了消息的完整生命周期，Mock 提供可控的测试环境
         */
        /** @var WeChatMessage&MockObject $message2 */
        $message2 = $this->createMock(WeChatMessage::class);
        $message3 = 'invalid_message'; // 非 WeChatMessage 对象

        $messages = ['msg1' => $message1, 'msg2' => $message2, 'invalid' => $message3];

        $message1
            ->expects($this->once())
            ->method('markAsRead')
        ;

        $message1
            ->method('isRead')
            ->willReturn(true)
        ;

        $message2
            ->expects($this->once())
            ->method('markAsRead')
        ;

        $message2
            ->method('isRead')
            ->willReturn(true)
        ;

        // 执行测试 - 此方法主要验证没有异常抛出
        $this->service->markMultipleAsRead($messages);

        // 验证所有 WeChatMessage 对象都被标记为已读
        $this->assertTrue($message1->isRead());
        $this->assertTrue($message2->isRead());
    }

    protected function onSetUp(): void
    {
        // 获取真实服务
        $messageRepository = self::getService(WeChatMessageRepository::class);
        $accountRepository = self::getService(WeChatAccountRepository::class);
        $entityManager = self::getEntityManager();
        $logger = self::getService(LoggerInterface::class);

        // Mock外部API客户端
        $this->apiClient = $this->createMock(WeChatApiClient::class);

        // 使用容器获取服务实例
        $this->service = self::getService(WeChatMessageService::class);
    }
}
