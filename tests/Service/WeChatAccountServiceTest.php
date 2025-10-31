<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\DTO\WeChatDeviceStatus;
use Tourze\WechatBotBundle\DTO\WeChatLoginResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Repository\WeChatAccountRepository;
use Tourze\WechatBotBundle\Service\WeChatAccountService;

/**
 * 微信账号管理服务集成测试
 *
 * 这是一个集成测试类，使用真实的数据库和服务。
 * 只对外部API客户端进行mock，其他服务均使用真实实现。
 *
 * @internal
 */
#[CoversClass(WeChatAccountService::class)]
#[RunTestsInSeparateProcesses]
final class WeChatAccountServiceTest extends AbstractIntegrationTestCase
{
    private WeChatAccountService $service;

    private MockObject&WeChatApiClient $apiClient;

    private WeChatAccountRepository $accountRepository;

    protected function onSetUp(): void
    {
        // 获取真实服务
        $this->accountRepository = self::getService(WeChatAccountRepository::class);
        $entityManager = self::getEntityManager();
        $logger = self::getService(LoggerInterface::class);

        // Mock外部API客户端（因为我们不想在测试中调用真实的微信API）
        $this->apiClient = $this->createMock(WeChatApiClient::class);

        // 将Mock的API客户端注册到服务容器中
        self::getContainer()->set(WeChatApiClient::class, $this->apiClient);

        // 从容器中获取服务实例，这样会使用我们注入的Mock API客户端
        $this->service = self::getService(WeChatAccountService::class);
    }

    /**
     * 测试成功创建设备并开始登录流程
     */
    public function testCreateDeviceAndStartLoginSuccess(): void
    {
        // 准备测试数据 - 创建并持久化API账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);
        self::getEntityManager()->flush();

        $expectedQrCode = 'https://wx.qq.com/qr/code123';

        // Mock API客户端的两次调用：创建设备 + 获取二维码
        $this->apiClient
            ->expects($this->exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls(
                ['code' => '1000', 'message' => 'success'], // 创建设备响应
                ['code' => '1000', 'data' => ['qrCodeUrl' => $expectedQrCode]] // 获取二维码响应
            )
        ;

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

        // 验证数据库状态
        $savedAccount = $result->account;
        $this->assertNotNull($savedAccount->getId()); // 确保已保存到数据库
        $this->assertEquals($apiAccount, $savedAccount->getApiAccount());
        $this->assertEquals('pending_login', $savedAccount->getStatus());
        $this->assertEquals('test-remark', $savedAccount->getRemark());
        $this->assertNotNull($savedAccount->getDeviceId());

        // 从数据库重新查询验证
        $foundAccount = $this->accountRepository->find($savedAccount->getId());
        $this->assertNotNull($foundAccount);
        $this->assertInstanceOf(WeChatAccount::class, $foundAccount);
        $this->assertEquals($expectedQrCode, $foundAccount->getQrCodeUrl());
    }

    /**
     * 测试创建设备失败的情况
     */
    public function testCreateDeviceAndStartLoginFailure(): void
    {
        // 创建真实的API账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-fail-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);
        self::getEntityManager()->flush();

        // 模拟API调用异常
        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new \RuntimeException('API调用失败'))
        ;

        // 执行测试
        $result = $this->service->createDeviceAndStartLogin($apiAccount);

        // 验证结果
        $this->assertInstanceOf(WeChatLoginResult::class, $result);
        $this->assertFalse($result->success);
        $this->assertNull($result->qrCodeUrl);
        $this->assertNull($result->account);
        $this->assertStringStartsWith('创建设备失败', $result->message);

        // 验证数据库状态 - 失败时不应该有账号记录被创建
        $accounts = $this->accountRepository->findBy(['apiAccount' => $apiAccount]);
        $this->assertEmpty($accounts, '失败时不应该创建账号记录');
    }

    /**
     * 测试现有账号重新开始登录流程
     */
    public function testStartLoginForExistingAccount(): void
    {
        // 准备测试数据 - 创建并持久化API账号和微信账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-start-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);
        self::getEntityManager()->flush();

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device123-' . uniqid());
        $account->setStatus('offline');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        // 记录原始状态用于对比
        $originalStatus = $account->getStatus();

        $expectedQrCode = 'https://wx.qq.com/qr/newcode123';
        $mockResponse = [
            'code' => '1000',
            'data' => [
                'qrCodeUrl' => $expectedQrCode,
            ],
        ];

        // Mock API调用
        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        // 执行测试
        $result = $this->service->startLogin($account);

        // 验证结果
        $this->assertInstanceOf(WeChatLoginResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertEquals($expectedQrCode, $result->qrCodeUrl);
        $this->assertEquals($account, $result->account);
        $this->assertEquals('二维码生成成功，请扫码登录', $result->message);

        // 验证数据库状态变更
        self::getEntityManager()->refresh($account);
        $this->assertEquals('pending_login', $account->getStatus());
        $this->assertEquals($expectedQrCode, $account->getQrCodeUrl());
        $this->assertNotEquals($originalStatus, $account->getStatus(), '状态应该已更新');
    }

    /**
     * 测试确认登录成功
     */
    public function testConfirmLoginSuccess(): void
    {
        // 准备测试数据 - 创建真实账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-confirm-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);
        self::getEntityManager()->flush();

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device123-confirm-' . uniqid());
        $account->setStatus('pending_login');
        $account->setQrCodeUrl('https://wx.qq.com/qr/pending123');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $mockResponse = [
            'code' => '1000',
            'data' => [
                'login' => true,
                'wxId' => 'testuser123',
                'nickname' => '测试用户',
                'avatar' => 'https://wx.qlogo.cn/avatar.jpg',
            ],
        ];

        // Mock API调用
        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        // 执行测试
        $result = $this->service->confirmLogin($account);

        // 验证结果
        $this->assertInstanceOf(WeChatLoginResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertEquals($account, $result->account);
        $this->assertEquals('登录成功', $result->message);

        // 验证数据库状态更新
        self::getEntityManager()->refresh($account);
        $this->assertEquals('online', $account->getStatus());
        $this->assertEquals('testuser123', $account->getWechatId());
        $this->assertEquals('测试用户', $account->getNickname());
        $this->assertEquals('https://wx.qlogo.cn/avatar.jpg', $account->getAvatar());
        $this->assertNotNull($account->getLastLoginTime());
        $this->assertNotNull($account->getLastActiveTime());
    }

    /**
     * 测试确认登录失败
     */
    public function testConfirmLoginFailure(): void
    {
        // 准备测试数据 - 创建真实账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-confirm-fail-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);
        self::getEntityManager()->flush();

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device123-confirm-fail-' . uniqid());
        $account->setStatus('pending_login');
        $account->setQrCodeUrl('https://wx.qq.com/qr/pending456');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        // 记录原始状态
        $originalStatus = $account->getStatus();
        $originalQrCode = $account->getQrCodeUrl();

        $mockResponse = [
            'code' => '1000',
            'data' => [
                'login' => false,
            ],
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        // 执行测试
        $result = $this->service->confirmLogin($account);

        // 验证结果
        $this->assertInstanceOf(WeChatLoginResult::class, $result);
        $this->assertFalse($result->success);
        $this->assertEquals($account, $result->account);
        $this->assertEquals('等待扫码登录', $result->message);
        $this->assertEquals($originalQrCode, $result->qrCodeUrl);

        // 验证数据库状态未变更（失败时不更新状态）
        self::getEntityManager()->refresh($account);
        $this->assertEquals($originalStatus, $account->getStatus());
        $this->assertEquals($originalQrCode, $account->getQrCodeUrl());
        $this->assertNull($account->getWechatId()); // 失败时不应设置微信ID
    }

    /**
     * 测试检查在线状态
     */
    public function testCheckOnlineStatus(): void
    {
        // 准备测试数据 - 创建真实账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-status-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);
        self::getEntityManager()->flush();

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device123-status-' . uniqid());
        $account->setStatus('offline'); // 初始状态为离线

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $mockResponse = [
            'code' => '1000',
            'data' => [
                'online' => true,
                'last_active' => time(),
            ],
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        // 执行测试
        $result = $this->service->checkOnlineStatus($account);

        // 验证结果
        $this->assertInstanceOf(WeChatDeviceStatus::class, $result);
        $this->assertTrue($result->isOnline);
        $this->assertEquals('online', $result->status);
        $this->assertEquals($account->getDeviceId(), $result->deviceId);

        // 验证数据库状态更新
        self::getEntityManager()->refresh($account);
        $this->assertEquals('online', $account->getStatus());
        $this->assertNotNull($account->getLastActiveTime());
    }

    /**
     * 测试初始化通讯录列表
     */
    public function testInitContactList(): void
    {
        // 准备测试数据 - 创建真实账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-contact-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);
        self::getEntityManager()->flush();

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device123-contact-' . uniqid());
        $account->setStatus('online');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

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
        $result = $this->service->initContactList($account);

        // 验证结果
        $this->assertTrue($result);

        // 验证数据库状态保持不变（初始化通讯录不会改变账号状态）
        self::getEntityManager()->refresh($account);
        $this->assertEquals('online', $account->getStatus());
    }

    /**
     * 测试退出登录
     */
    public function testLogout(): void
    {
        // 准备测试数据 - 创建真实账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-logout-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);
        self::getEntityManager()->flush();

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device123-logout-' . uniqid());
        $account->setStatus('online'); // 初始状态为在线

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

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
        $result = $this->service->logout($account);

        // 验证结果
        $this->assertTrue($result);

        // 验证数据库状态更新
        self::getEntityManager()->refresh($account);
        $this->assertEquals('offline', $account->getStatus());
    }

    /**
     * 测试设置设备代理
     */
    public function testApplyDeviceProxy(): void
    {
        // 准备测试数据 - 创建真实的API账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-proxy-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);
        self::getEntityManager()->flush();

        $deviceId = 'device123-proxy-' . uniqid();
        $proxy = 'http://proxy.example.com:8080';

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
        $result = $this->service->applyDeviceProxy($apiAccount, $deviceId, $proxy);

        // 验证结果
        $this->assertTrue($result);

        // 注意：applyDeviceProxy 方法不会更新数据库状态，只进行API调用
        // 所以这里不需要检查数据库变更
    }

    /**
     * 测试获取账号统计信息
     */
    public function testGetAccountsStatistics(): void
    {
        // 准备测试数据 - 创建不同状态的账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-stats-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);
        self::getEntityManager()->flush();

        // 创建不同状态的账号进行测试
        $onlineAccount = new WeChatAccount();
        $onlineAccount->setApiAccount($apiAccount);
        $onlineAccount->setDeviceId('device-online-' . uniqid());
        $onlineAccount->setStatus('online');

        $offlineAccount = new WeChatAccount();
        $offlineAccount->setApiAccount($apiAccount);
        $offlineAccount->setDeviceId('device-offline-' . uniqid());
        $offlineAccount->setStatus('offline');

        $pendingAccount = new WeChatAccount();
        $pendingAccount->setApiAccount($apiAccount);
        $pendingAccount->setDeviceId('device-pending-' . uniqid());
        $pendingAccount->setStatus('pending_login');

        self::getEntityManager()->persist($onlineAccount);
        self::getEntityManager()->persist($offlineAccount);
        self::getEntityManager()->persist($pendingAccount);
        self::getEntityManager()->flush();

        // 执行测试
        $result = $this->service->getAccountsStatistics();

        // 验证结果结构
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('online', $result);
        $this->assertArrayHasKey('offline', $result);
        $this->assertArrayHasKey('pending_login', $result);
        $this->assertArrayHasKey('expired', $result);

        // 验证至少包含我们创建的账号（可能有其他测试的数据）
        $this->assertGreaterThanOrEqual(3, $result['total']);
        $this->assertGreaterThanOrEqual(1, $result['online']);
        $this->assertGreaterThanOrEqual(1, $result['offline']);
        $this->assertGreaterThanOrEqual(1, $result['pending_login']);
        $this->assertGreaterThanOrEqual(0, $result['expired']);
    }

    /**
     * 测试批量检查所有账号状态
     */
    public function testCheckAllAccountsStatus(): void
    {
        // 准备测试数据 - 创建真实账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-check-all-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);
        self::getEntityManager()->flush();

        $account1 = new WeChatAccount();
        $account1->setApiAccount($apiAccount);
        $account1->setDeviceId('device1-checkall-' . uniqid());
        $account1->setStatus('offline');

        $account2 = new WeChatAccount();
        $account2->setApiAccount($apiAccount);
        $account2->setDeviceId('device2-checkall-' . uniqid());
        $account2->setStatus('offline');

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->flush();

        // 配置API客户端返回在线状态
        $this->apiClient
            ->expects($this->atLeastOnce()) // 少于我们创建的账号数量，因为可能有其他测试的账号
            ->method('request')
            ->willReturn([
                'code' => '1000',
                'data' => [
                    'online' => true,
                    'last_active' => time(),
                ],
            ])
        ;

        // 执行测试
        $results = $this->service->checkAllAccountsStatus();

        // 验证结果
        $this->assertGreaterThanOrEqual(2, count($results)); // 至少包含我们创建的两个账号

        // 验证我们创建的账号存在于结果中
        $account1Id = $account1->getId();
        $account2Id = $account2->getId();
        $this->assertNotNull($account1Id);
        $this->assertNotNull($account2Id);
        $this->assertArrayHasKey($account1Id, $results);
        $this->assertArrayHasKey($account2Id, $results);
        $this->assertInstanceOf(WeChatDeviceStatus::class, $results[$account1Id]);
        $this->assertInstanceOf(WeChatDeviceStatus::class, $results[$account2Id]);

        // 验证数据库状态更新
        self::getEntityManager()->refresh($account1);
        self::getEntityManager()->refresh($account2);
        $this->assertEquals('online', $account1->getStatus());
        $this->assertEquals('online', $account2->getStatus());
    }

    /**
     * 测试__toString方法
     */
    public function testToString(): void
    {
        $result = $this->service->__toString();

        $this->assertEquals('WeChatAccountService', $result);
    }

    /**
     * 测试清理 - 确保每个测试后清理数据
     */
    protected function onTearDown(): void
    {
        // 清理测试数据，确保测试间无相互影响
        // 由于使用了 uniqid()，一般不会有冲突，但为了保险起见还是清理

        // 注意：AbstractIntegrationTestCase 会自动处理数据库事务回滚
        // 所以这里不需要手动清理，但保留此方法以备将来需要
        parent::onTearDown();
    }
}
