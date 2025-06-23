<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\DTO\WeChatDeviceStatus;
use Tourze\WechatBotBundle\DTO\WeChatLoginResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Repository\WeChatAccountRepository;
use Tourze\WechatBotBundle\Service\WeChatAccountService;

/**
 * 微信账号管理服务测试
 */
class WeChatAccountServiceTest extends TestCase
{
    private WeChatAccountService $service;
    private EntityManagerInterface&MockObject $entityManager;
    private WeChatApiClient&MockObject $apiClient;
    private WeChatAccountRepository&MockObject $accountRepository;
    private LoggerInterface&MockObject $logger;

    /**
     * 测试成功创建设备并开始登录流程
     */
    public function testCreateDeviceAndStartLoginSuccess(): void
    {
        // 准备测试数据
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $apiAccount->method('getName')->willReturn('test-api-account');

        $expectedQrCode = 'https://wx.qq.com/qr/code123';
        $mockResponse = [
            'code' => '1000',
            'data' => [
                'qrCodeUrl' => $expectedQrCode
            ]
        ];

        // 配置模拟对象
        $this->apiClient
            ->expects($this->exactly(2))
            ->method('request')
            ->willReturn($mockResponse);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(WeChatAccount::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->logger
            ->expects($this->exactly(2))
            ->method('info');

        // 执行测试
        $result = $this->service->createDeviceAndStartLogin(
            $apiAccount,
            'test-remark',
            null,
            '北京',
            '朝阳'
        );

        // 验证结果
        $this->assertInstanceOf(WeChatLoginResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertEquals($expectedQrCode, $result->qrCodeUrl);
        $this->assertNotNull($result->account);
        $this->assertEquals('设备创建成功，请扫码登录', $result->message);
    }

    /**
     * 测试创建设备失败的情况
     */
    public function testCreateDeviceAndStartLoginFailure(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);

        // 模拟API调用异常
        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new \RuntimeException('API调用失败'));

        $this->logger
            ->expects($this->once())
            ->method('error');

        // 执行测试
        $result = $this->service->createDeviceAndStartLogin($apiAccount);

        // 验证结果
        $this->assertInstanceOf(WeChatLoginResult::class, $result);
        $this->assertFalse($result->success);
        $this->assertNull($result->qrCodeUrl);
        $this->assertNull($result->account);
        $this->assertStringStartsWith('创建设备失败', $result->message);
    }

    /**
     * 测试现有账号重新开始登录流程
     */
    public function testStartLoginForExistingAccount(): void
    {
        // 准备测试数据
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');
        $account->method('getId')->willReturn(1);

        $expectedQrCode = 'https://wx.qq.com/qr/newcode123';
        $mockResponse = [
            'code' => '1000',
            'data' => [
                'qrCodeUrl' => $expectedQrCode
            ]
        ];

        // 配置模拟对象
        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $account
            ->expects($this->once())
            ->method('setQrCodeUrl')
            ->with($expectedQrCode)
            ->willReturnSelf();

        $account
            ->expects($this->once())
            ->method('setStatus')
            ->with('pending_login')
            ->willReturnSelf();

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->logger
            ->expects($this->once())
            ->method('info');

        // 执行测试
        $result = $this->service->startLogin($account);

        // 验证结果
        $this->assertInstanceOf(WeChatLoginResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertEquals($expectedQrCode, $result->qrCodeUrl);
        $this->assertEquals($account, $result->account);
    }

    /**
     * 测试确认登录成功
     */
    public function testConfirmLoginSuccess(): void
    {
        // 准备测试数据
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');
        $account->method('getId')->willReturn(1);

        $mockResponse = [
            'code' => '1000',
            'data' => [
                'login' => true,
                'wx_id' => 'testuser123',
                'nickname' => '测试用户',
                'avatar' => 'https://wx.qlogo.cn/avatar.jpg'
            ]
        ];

        // 配置模拟对象
        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $this->logger
            ->expects($this->once())
            ->method('info');

        // 执行测试
        $result = $this->service->confirmLogin($account);

        // 验证结果
        $this->assertInstanceOf(WeChatLoginResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertEquals($account, $result->account);
    }

    /**
     * 测试确认登录失败
     */
    public function testConfirmLoginFailure(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockResponse = [
            'code' => '1000',
            'data' => [
                'login' => false
            ]
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        // 执行测试
        $result = $this->service->confirmLogin($account);

        // 验证结果
        $this->assertInstanceOf(WeChatLoginResult::class, $result);
        $this->assertFalse($result->success);
        $this->assertEquals($account, $result->account);
    }

    /**
     * 测试检查在线状态
     */
    public function testCheckOnlineStatus(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockResponse = [
            'code' => '1000',
            'data' => [
                'online' => true,
                'last_active' => time()
            ]
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        // 执行测试
        $result = $this->service->checkOnlineStatus($account);

        // 验证结果
        $this->assertInstanceOf(WeChatDeviceStatus::class, $result);
        $this->assertTrue($result->isOnline);
    }

    /**
     * 测试初始化通讯录列表
     */
    public function testInitContactList(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockResponse = [
            'code' => '1000',
            'message' => 'success'
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $this->logger
            ->expects($this->once())
            ->method('info');

        // 执行测试
        $result = $this->service->initContactList($account);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试退出登录
     */
    public function testLogout(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');
        $account->method('getId')->willReturn(1);

        $mockResponse = [
            'code' => '1000',
            'message' => 'success'
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $account
            ->expects($this->once())
            ->method('markAsOffline')
            ->willReturnSelf();

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->logger
            ->expects($this->once())
            ->method('info');

        // 执行测试
        $result = $this->service->logout($account);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试设置设备代理
     */
    public function testSetDeviceProxy(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $deviceId = 'device123';
        $proxy = 'http://proxy.example.com:8080';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success'
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $this->logger
            ->expects($this->once())
            ->method('info');

        // 执行测试
        $result = $this->service->setDeviceProxy($apiAccount, $deviceId, $proxy);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试获取账号统计信息
     */
    public function testGetAccountsStatistics(): void
    {
        $mockStatusCounts = [
            'online' => 5,
            'offline' => 3,
            'pending_login' => 2
        ];

        $this->accountRepository
            ->expects($this->once())
            ->method('countByStatus')
            ->willReturn($mockStatusCounts);

        // 执行测试
        $result = $this->service->getAccountsStatistics();

        // 验证结果
        $this->assertEquals(10, $result['total']);
        $this->assertEquals(5, $result['online']);
        $this->assertEquals(3, $result['offline']);
        $this->assertEquals(2, $result['pending_login']);
        $this->assertEquals(0, $result['expired']);
    }

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->apiClient = $this->createMock(WeChatApiClient::class);
        $this->accountRepository = $this->createMock(WeChatAccountRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new WeChatAccountService(
            $this->entityManager,
            $this->apiClient,
            $this->accountRepository,
            $this->logger
        );
    }
} 