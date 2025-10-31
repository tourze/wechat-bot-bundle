<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Handler;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatMessage;
use Tourze\WechatBotBundle\Handler\WeChatCallbackHandler;
use Tourze\WechatBotBundle\Repository\WeChatAccountRepository;
use Tourze\WechatBotBundle\Service\WeChatMessageService;

/**
 * 微信回调处理器测试
 *
 * 测试微信API回调处理的各种场景：
 * - 不同类型的回调处理
 * - 请求验证和错误处理
 * - 消息处理和自动回复
 * - 登录状态回调处理
 * - 异常情况处理
 *
 * @internal
 */
#[CoversClass(WeChatCallbackHandler::class)]
#[RunTestsInSeparateProcesses]
final class WeChatCallbackHandlerTest extends AbstractIntegrationTestCase
{
    private WeChatCallbackHandler $handler;

    private WeChatMessageService&MockObject $messageService;

    private LoggerInterface&MockObject $logger;

    private WeChatAccountRepository&MockObject $accountRepository;

    protected function onSetUp(): void
    {
        // Mock 外部依赖
        // Mock WeChatMessageService 具体类的原因：
        // 1. 消息处理服务：封装消息的创建、发送、存储等复杂业务逻辑
        // 2. 业务隔离：避免测试时触发实际的消息处理和API调用
        // 3. 架构约束：作为应用服务层具体类，没有对应的抽象接口
        $this->messageService = $this->createMock(WeChatMessageService::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        // Mock WeChatAccountRepository 具体类的原因：
        // 1. 账户数据访问：管理微信账户的查询、状态更新等数据库操作
        // 2. 数据隔离：避免测试中的实际数据库访问，确保测试独立性
        // 3. Doctrine限制：继承自EntityRepository，没有对应的接口抽象
        $this->accountRepository = $this->createMock(WeChatAccountRepository::class);

        // 先替换容器中的依赖为Mock对象（必须在获取服务之前）
        self::getContainer()->set(WeChatMessageService::class, $this->messageService);
        // 不要替换LoggerInterface，因为它是全局服务，使用特定的channel logger
        self::getContainer()->set('monolog.logger.wechat_bot', $this->logger);
        self::getContainer()->set(WeChatAccountRepository::class, $this->accountRepository);

        // 从容器获取测试服务，此时会使用上面设置的mock依赖
        $handler = self::getContainer()->get(WeChatCallbackHandler::class);
        if (!$handler instanceof WeChatCallbackHandler) {
            throw new \RuntimeException('Failed to get WeChatCallbackHandler from container');
        }
        $this->handler = $handler;
    }

    #[TestDox('处理POST请求的消息回调')]
    public function testHandleCallbackWithValidMessageRequest(): void
    {
        $data = [
            'type' => 'message',
            'deviceId' => 'test-device',
            'fromUser' => 'test-user',
            'content' => 'Hello World',
            'messageType' => 'text',
        ];

        $request = new Request([], [], [], [], [], [], false !== json_encode($data) ? json_encode($data) : '');
        $request->setMethod('POST');

        /*
         * 使用具体类创建 Mock 对象的原因：
         * 1) WeChatMessage 是实体类，没有对应的接口
         * 2) 为了测试消息处理的返回值，需要 mock 消息实体
         * 3) 实体类通常不会有接口，直接 mock 具体类是合理的
         */
        /** @var MockObject|WeChatMessage $message */
        $message = $this->createMock(WeChatMessage::class);
        $message->method('getId')->willReturn(123);
        $message->method('getMessageType')->willReturn('text');
        $message->method('getSenderId')->willReturn('test-user');
        $message->method('isInbound')->willReturn(true);
        $message->method('isTextMessage')->willReturn(true);
        $message->method('getContent')->willReturn('Hello World');
        /*
         * 使用具体类创建 Mock 对象的原因：
         * 1) WeChatAccount 是实体类，没有对应的接口
         * 2) 为了测试消息关联的账户信息，需要 mock 账户实体
         * 3) 实体类通常不会有接口，直接 mock 具体类是合理的
         */
        $message->method('getAccount')->willReturn($this->createMock(WeChatAccount::class));

        $this->messageService
            ->expects($this->once())
            ->method('processInboundMessage')
            ->with($data)
            ->willReturn($message)
        ;

        $response = $this->handler->handleCallback($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $responseData = json_decode(false !== $content ? $content : '', true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertSame('success', $responseData['status']);
        $this->assertSame('Callback processed', $responseData['message']);
    }

    #[TestDox('处理非POST请求返回405错误')]
    public function testHandleCallbackWithInvalidMethod(): void
    {
        $request = new Request();
        $request->setMethod('GET');

        $response = $this->handler->handleCallback($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(405, $response->getStatusCode());

        $content = $response->getContent();
        $responseData = json_decode(false !== $content ? $content : '', true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertSame('Method not allowed', $responseData['error']);
    }

    #[TestDox('处理空请求体返回400错误')]
    public function testHandleCallbackWithEmptyBody(): void
    {
        $request = new Request([], [], [], [], [], [], '');
        $request->setMethod('POST');

        $response = $this->handler->handleCallback($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(400, $response->getStatusCode());

        $content = $response->getContent();
        $responseData = json_decode(false !== $content ? $content : '', true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertSame('Empty request body', $responseData['error']);
    }

    #[TestDox('处理无效JSON返回400错误')]
    public function testHandleCallbackWithInvalidJson(): void
    {
        $request = new Request([], [], [], [], [], [], '{invalid json}');
        $request->setMethod('POST');

        $response = $this->handler->handleCallback($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(400, $response->getStatusCode());

        $content = $response->getContent();
        $responseData = json_decode(false !== $content ? $content : '', true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertSame('Invalid JSON', $responseData['error']);
    }

    #[TestDox('处理无效回调数据返回400错误')]
    public function testHandleCallbackWithInvalidData(): void
    {
        $data = [
            'type' => 'message',
            // 缺少必需的 deviceId 字段
        ];

        $request = new Request([], [], [], [], [], [], false !== json_encode($data) ? json_encode($data) : '');
        $request->setMethod('POST');

        $response = $this->handler->handleCallback($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(400, $response->getStatusCode());

        $content = $response->getContent();
        $responseData = json_decode(false !== $content ? $content : '', true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertSame('Invalid callback data', $responseData['error']);
    }

    #[TestDox('处理登录成功回调')]
    public function testHandleLoginSuccessCallback(): void
    {
        $data = [
            'type' => 'login',
            'deviceId' => 'test-device',
            'status' => 'success',
            'wxId' => 'test-wx-id',
            'nickname' => 'Test User',
            'avatar' => 'https://example.com/avatar.jpg',
        ];

        $request = new Request([], [], [], [], [], [], false !== json_encode($data) ? json_encode($data) : '');
        $request->setMethod('POST');

        /*
         * 使用具体类创建 Mock 对象的原因：
         * 1) WeChatAccount 是实体类，没有对应的接口
         * 2) 为了测试登录成功回调的账户更新逻辑，需要 mock 账户实体
         * 3) 实体类通常不会有接口，直接 mock 具体类是合理的
         */
        /** @var MockObject|WeChatAccount $account */
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getId')->willReturn(123);
        $account->expects($this->once())->method('markAsOnline');
        $account->expects($this->once())->method('setWechatId')->with('test-wx-id');
        $account->expects($this->once())->method('setNickname')->with('Test User');
        $account->expects($this->once())->method('setAvatar')->with('https://example.com/avatar.jpg');
        $account->expects($this->once())->method('setLastLoginTime')->with(self::isInstanceOf(\DateTimeImmutable::class));
        $account->expects($this->once())->method('updateLastActiveTime');

        $this->accountRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['deviceId' => 'test-device'])
            ->willReturn($account)
        ;

        $response = $this->handler->handleCallback($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    #[TestDox('处理登录失败回调')]
    public function testHandleLoginFailureCallback(): void
    {
        $data = [
            'type' => 'login',
            'deviceId' => 'test-device',
            'status' => 'logout',
        ];

        $request = new Request([], [], [], [], [], [], false !== json_encode($data) ? json_encode($data) : '');
        $request->setMethod('POST');

        /*
         * 使用具体类创建 Mock 对象的原因：
         * 1) WeChatAccount 是实体类，没有对应的接口
         * 2) 为了测试登录失败回调的账户状态更新逻辑，需要 mock 账户实体
         * 3) 实体类通常不会有接口，直接 mock 具体类是合理的
         */
        /** @var MockObject|WeChatAccount $account */
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getId')->willReturn(123);
        $account->expects($this->once())->method('markAsOffline');
        $account->expects($this->once())->method('updateLastActiveTime');

        $this->accountRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['deviceId' => 'test-device'])
            ->willReturn($account)
        ;

        $response = $this->handler->handleCallback($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    #[TestDox('处理状态回调')]
    public function testHandleStatusCallback(): void
    {
        $data = [
            'type' => 'status',
            'deviceId' => 'test-device',
            'status' => 'online',
        ];

        $request = new Request([], [], [], [], [], [], false !== json_encode($data) ? json_encode($data) : '');
        $request->setMethod('POST');

        /*
         * 使用具体类创建 Mock 对象的原因：
         * 1) WeChatAccount 是实体类，没有对应的接口
         * 2) 为了测试状态回调的账户状态更新逻辑，需要 mock 账户实体
         * 3) 实体类通常不会有接口，直接 mock 具体类是合理的
         */
        /** @var MockObject|WeChatAccount $account */
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getId')->willReturn(123);
        $account->expects($this->once())->method('markAsOnline');
        $account->expects($this->once())->method('updateLastActiveTime');

        $this->accountRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['deviceId' => 'test-device'])
            ->willReturn($account)
        ;

        $response = $this->handler->handleCallback($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    #[TestDox('处理好友请求回调')]
    public function testHandleFriendRequestCallback(): void
    {
        $data = [
            'type' => 'friend_request',
            'deviceId' => 'test-device',
            'fromUser' => 'test-user',
            'content' => 'Hello, add me as friend',
        ];

        $request = new Request([], [], [], [], [], [], false !== json_encode($data) ? json_encode($data) : '');
        $request->setMethod('POST');

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Friend request callback received', [
                'fromUser' => 'test-user',
                'content' => 'Hello, add me as friend',
            ])
        ;

        $response = $this->handler->handleCallback($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    #[TestDox('处理群邀请回调')]
    public function testHandleGroupInviteCallback(): void
    {
        $data = [
            'type' => 'group_invite',
            'deviceId' => 'test-device',
            'groupId' => 'test-group',
            'inviter' => 'test-inviter',
        ];

        $request = new Request([], [], [], [], [], [], false !== json_encode($data) ? json_encode($data) : '');
        $request->setMethod('POST');

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Group invite callback received', [
                'groupId' => 'test-group',
                'inviter' => 'test-inviter',
            ])
        ;

        $response = $this->handler->handleCallback($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    #[TestDox('处理未知类型回调')]
    public function testHandleUnknownCallback(): void
    {
        $data = [
            'type' => 'unknown_type',
            'deviceId' => 'test-device',
        ];

        $request = new Request([], [], [], [], [], [], false !== json_encode($data) ? json_encode($data) : '');
        $request->setMethod('POST');

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with('Unknown callback type received', [
                'type' => 'unknown_type',
                'data' => $data,
            ])
        ;

        $response = $this->handler->handleCallback($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }

    #[TestDox('处理异常情况返回500错误')]
    public function testHandleCallbackWithException(): void
    {
        $data = [
            'type' => 'message',
            'deviceId' => 'test-device',
            'fromUser' => 'test-user',
        ];

        $request = new Request([], [], [], [], [], [], false !== json_encode($data) ? json_encode($data) : '');
        $request->setMethod('POST');

        $this->messageService
            ->expects($this->once())
            ->method('processInboundMessage')
            ->willThrowException(new \Exception('Test exception'))
        ;

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Failed to process message callback', self::callback(fn ($context) => is_array($context)))
        ;

        $response = $this->handler->handleCallback($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(500, $response->getStatusCode());

        $content = $response->getContent();
        $responseData = json_decode(false !== $content ? $content : '', true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Failed to process callback', $responseData['message']);
    }

    #[TestDox('处理消息处理失败的情况')]
    public function testHandleCallbackWithFailedMessageProcessing(): void
    {
        $data = [
            'type' => 'message',
            'deviceId' => 'test-device',
            'fromUser' => 'test-user',
        ];

        $request = new Request([], [], [], [], [], [], false !== json_encode($data) ? json_encode($data) : '');
        $request->setMethod('POST');

        $this->messageService
            ->expects($this->once())
            ->method('processInboundMessage')
            ->willReturn(null)
        ;

        $response = $this->handler->handleCallback($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(500, $response->getStatusCode());

        $content = $response->getContent();
        $responseData = json_decode(false !== $content ? $content : '', true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertSame('error', $responseData['status']);
        $this->assertSame('Failed to process callback', $responseData['message']);
    }

    #[TestDox('处理找不到账号的登录回调')]
    public function testHandleLoginCallbackWithAccountNotFound(): void
    {
        $data = [
            'type' => 'login',
            'deviceId' => 'non-existent-device',
            'status' => 'success',
        ];

        $request = new Request([], [], [], [], [], [], false !== json_encode($data) ? json_encode($data) : '');
        $request->setMethod('POST');

        $this->accountRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['deviceId' => 'non-existent-device'])
            ->willReturn(null)
        ;

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with('Account not found for login callback', [
                'deviceId' => 'non-existent-device',
            ])
        ;

        $response = $this->handler->handleCallback($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(500, $response->getStatusCode());
    }

    #[TestDox('toString方法返回正确的字符串')]
    public function testToString(): void
    {
        $this->assertSame('WeChatCallbackHandler', (string) $this->handler);
    }
}
