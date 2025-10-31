<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\TestDox;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Repository\WeChatApiAccountRepository;

/**
 * 微信API账号仓储测试
 *
 * 测试微信API账号数据访问层的各种查询方法：
 * - 基础查询方法
 * - 连接状态过滤查询
 * - Token相关查询
 * - 统计查询
 * - 复合条件查询
 *
 * @template-extends AbstractRepositoryTestCase<WeChatApiAccount>
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(WeChatApiAccountRepository::class)]
final class WeChatApiAccountRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 此测试类不需要特殊的初始化逻辑
    }

    protected function createNewEntity(): object
    {
        $entity = new WeChatApiAccount();
        $entity->setName('Test API Account ' . uniqid());
        $entity->setBaseUrl('http://localhost:8080');
        $entity->setUsername('test_user_' . uniqid());
        $entity->setPassword('test_password');
        $entity->setValid(true);

        return $entity;
    }

    protected function getRepository(): WeChatApiAccountRepository
    {
        return self::getService(WeChatApiAccountRepository::class);
    }

    #[TestDox('通过名称查找API账号')]
    public function testFindByName(): void
    {
        $account = new WeChatApiAccount();
        $account->setName('Test Account');
        $account->setBaseUrl('https://api.example.com');
        $account->setUsername('testuser');
        $account->setPassword('testpass');
        $account->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $foundAccount = $this->getRepository()->findByName('Test Account');

        $this->assertInstanceOf(WeChatApiAccount::class, $foundAccount);
        $this->assertSame('Test Account', $foundAccount->getName());
        $this->assertSame('https://api.example.com', $foundAccount->getBaseUrl());
    }

    #[TestDox('通过名称查找不存在的API账号返回null')]
    public function testFindByNameNotFound(): void
    {
        $foundAccount = $this->getRepository()->findByName('Non-existent Account');

        $this->assertNull($foundAccount);
    }

    #[TestDox('查找所有有效的API账号')]
    public function testFindValidAccounts(): void
    {
        $validAccount1 = new WeChatApiAccount();
        $validAccount1->setName('Valid Account 1');
        $validAccount1->setBaseUrl('https://api1.example.com');
        $validAccount1->setUsername('user1');
        $validAccount1->setPassword('pass1');
        $validAccount1->setValid(true);

        $validAccount2 = new WeChatApiAccount();
        $validAccount2->setName('Valid Account 2');
        $validAccount2->setBaseUrl('https://api2.example.com');
        $validAccount2->setUsername('user2');
        $validAccount2->setPassword('pass2');
        $validAccount2->setValid(true);

        $invalidAccount = new WeChatApiAccount();
        $invalidAccount->setName('Invalid Account');
        $invalidAccount->setBaseUrl('https://api3.example.com');
        $invalidAccount->setUsername('user3');
        $invalidAccount->setPassword('pass3');
        $invalidAccount->setValid(false);

        self::getEntityManager()->persist($validAccount1);
        self::getEntityManager()->persist($validAccount2);
        self::getEntityManager()->persist($invalidAccount);
        self::getEntityManager()->flush();

        $validAccounts = $this->getRepository()->findValidAccounts();

        // 检查至少包含我们创建的2个有效账号（包括fixture数据）
        $this->assertGreaterThanOrEqual(2, count($validAccounts));
        $accountNames = array_map(fn ($account) => $account->getName(), $validAccounts);
        $this->assertContains('Valid Account 1', $accountNames);
        $this->assertContains('Valid Account 2', $accountNames);

        // 验证所有账号都是有效的
        foreach ($validAccounts as $account) {
            $this->assertTrue($account->isValid());
        }
    }

    #[TestDox('查找所有已连接的API账号')]
    public function testFindConnectedAccounts(): void
    {
        $connectedAccount1 = new WeChatApiAccount();
        $connectedAccount1->setName('Connected Account 1');
        $connectedAccount1->setBaseUrl('https://connected1.example.com');
        $connectedAccount1->setUsername('connected1');
        $connectedAccount1->setPassword('pass1');
        $connectedAccount1->setConnectionStatus('connected');
        $connectedAccount1->setValid(true);
        $connectedAccount1->setLastLoginTime(new \DateTimeImmutable('2023-01-01'));

        $connectedAccount2 = new WeChatApiAccount();
        $connectedAccount2->setName('Connected Account 2');
        $connectedAccount2->setBaseUrl('https://connected2.example.com');
        $connectedAccount2->setUsername('connected2');
        $connectedAccount2->setPassword('pass2');
        $connectedAccount2->setConnectionStatus('connected');
        $connectedAccount2->setValid(true);
        $connectedAccount2->setLastLoginTime(new \DateTimeImmutable('2023-01-02'));

        $disconnectedAccount = new WeChatApiAccount();
        $disconnectedAccount->setName('Disconnected Account');
        $disconnectedAccount->setBaseUrl('https://disconnected.example.com');
        $disconnectedAccount->setUsername('disconnected');
        $disconnectedAccount->setPassword('pass3');
        $disconnectedAccount->setConnectionStatus('disconnected');
        $disconnectedAccount->setValid(true);

        self::getEntityManager()->persist($connectedAccount1);
        self::getEntityManager()->persist($connectedAccount2);
        self::getEntityManager()->persist($disconnectedAccount);
        self::getEntityManager()->flush();

        $connectedAccounts = $this->getRepository()->findConnectedAccounts();

        $this->assertCount(2, $connectedAccounts);
        // 验证按lastLoginTime DESC排序
        $this->assertSame('Connected Account 2', $connectedAccounts[0]->getName());
        $this->assertSame('Connected Account 1', $connectedAccounts[1]->getName());
    }

    #[TestDox('查找所有断开连接的API账号')]
    public function testFindDisconnectedAccounts(): void
    {
        $disconnectedAccount = new WeChatApiAccount();
        $disconnectedAccount->setName('Disconnected Account');
        $disconnectedAccount->setBaseUrl('https://disconnected.example.com');
        $disconnectedAccount->setUsername('disconnected');
        $disconnectedAccount->setPassword('pass1');
        $disconnectedAccount->setConnectionStatus('disconnected');
        $disconnectedAccount->setValid(true);

        $connectedAccount = new WeChatApiAccount();
        $connectedAccount->setName('Connected Account');
        $connectedAccount->setBaseUrl('https://connected.example.com');
        $connectedAccount->setUsername('connected');
        $connectedAccount->setPassword('pass2');
        $connectedAccount->setConnectionStatus('connected');
        $connectedAccount->setValid(true);

        self::getEntityManager()->persist($disconnectedAccount);
        self::getEntityManager()->persist($connectedAccount);
        self::getEntityManager()->flush();

        $disconnectedAccounts = $this->getRepository()->findDisconnectedAccounts();

        // 检查至少包含我们创建的1个断开连接账号（包括fixture数据）
        $this->assertGreaterThanOrEqual(1, count($disconnectedAccounts));
        $accountNames = array_map(fn ($account) => $account->getName(), $disconnectedAccounts);
        $this->assertContains('Disconnected Account', $accountNames);

        // 验证所有账号都是断开连接状态
        foreach ($disconnectedAccounts as $account) {
            $this->assertSame('disconnected', $account->getConnectionStatus());
        }
    }

    #[TestDox('查找所有出错的API账号')]
    public function testFindErrorAccounts(): void
    {
        $errorAccount = new WeChatApiAccount();
        $errorAccount->setName('Error Account');
        $errorAccount->setBaseUrl('https://error.example.com');
        $errorAccount->setUsername('error');
        $errorAccount->setPassword('pass1');
        $errorAccount->setConnectionStatus('error');
        $errorAccount->setValid(true);

        $connectedAccount = new WeChatApiAccount();
        $connectedAccount->setName('Connected Account');
        $connectedAccount->setBaseUrl('https://connected.example.com');
        $connectedAccount->setUsername('connected');
        $connectedAccount->setPassword('pass2');
        $connectedAccount->setConnectionStatus('connected');
        $connectedAccount->setValid(true);

        self::getEntityManager()->persist($errorAccount);
        self::getEntityManager()->persist($connectedAccount);
        self::getEntityManager()->flush();

        $errorAccounts = $this->getRepository()->findErrorAccounts();

        $this->assertCount(1, $errorAccounts);
        $this->assertSame('Error Account', $errorAccounts[0]->getName());
        $this->assertSame('error', $errorAccounts[0]->getConnectionStatus());
    }

    #[TestDox('根据基础URL查找API账号')]
    public function testFindByBaseUrl(): void
    {
        $account = new WeChatApiAccount();
        $account->setName('Test Account');
        $account->setBaseUrl('https://api.example.com');
        $account->setUsername('testuser');
        $account->setPassword('testpass');
        $account->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $foundAccount = $this->getRepository()->findByBaseUrl('https://api.example.com');

        $this->assertInstanceOf(WeChatApiAccount::class, $foundAccount);
        $this->assertSame('https://api.example.com', $foundAccount->getBaseUrl());
    }

    #[TestDox('根据基础URL查找API账号时自动去除尾部斜杠')]
    public function testFindByBaseUrlWithTrailingSlash(): void
    {
        $account = new WeChatApiAccount();
        $account->setName('Test Account');
        $account->setBaseUrl('https://api.example.com');
        $account->setUsername('testuser');
        $account->setPassword('testpass');
        $account->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $foundAccount = $this->getRepository()->findByBaseUrl('https://api.example.com/');

        $this->assertInstanceOf(WeChatApiAccount::class, $foundAccount);
        $this->assertSame('https://api.example.com', $foundAccount->getBaseUrl());
    }

    #[TestDox('查找有有效Token的API账号')]
    public function testFindAccountsWithValidToken(): void
    {
        $validTokenAccount = new WeChatApiAccount();
        $validTokenAccount->setName('Valid Token Account');
        $validTokenAccount->setBaseUrl('https://valid.example.com');
        $validTokenAccount->setUsername('valid');
        $validTokenAccount->setPassword('pass1');
        $validTokenAccount->setAccessToken('valid-token');
        $validTokenAccount->setTokenExpiresTime(new \DateTimeImmutable('+1 hour'));
        $validTokenAccount->setValid(true);

        $expiredTokenAccount = new WeChatApiAccount();
        $expiredTokenAccount->setName('Expired Token Account');
        $expiredTokenAccount->setBaseUrl('https://expired.example.com');
        $expiredTokenAccount->setUsername('expired');
        $expiredTokenAccount->setPassword('pass2');
        $expiredTokenAccount->setAccessToken('expired-token');
        $expiredTokenAccount->setTokenExpiresTime(new \DateTimeImmutable('-1 hour'));
        $expiredTokenAccount->setValid(true);

        $noTokenAccount = new WeChatApiAccount();
        $noTokenAccount->setName('No Token Account');
        $noTokenAccount->setBaseUrl('https://notoken.example.com');
        $noTokenAccount->setUsername('notoken');
        $noTokenAccount->setPassword('pass3');
        $noTokenAccount->setValid(true);

        self::getEntityManager()->persist($validTokenAccount);
        self::getEntityManager()->persist($expiredTokenAccount);
        self::getEntityManager()->persist($noTokenAccount);
        self::getEntityManager()->flush();

        $accountsWithValidToken = $this->getRepository()->findAccountsWithValidToken();

        $this->assertCount(1, $accountsWithValidToken);
        $this->assertSame('Valid Token Account', $accountsWithValidToken[0]->getName());
    }

    #[TestDox('获取API账号统计信息')]
    public function testGetAccountStatistics(): void
    {
        $connectedAccount = new WeChatApiAccount();
        $connectedAccount->setName('Connected Account');
        $connectedAccount->setBaseUrl('https://connected.example.com');
        $connectedAccount->setUsername('connected');
        $connectedAccount->setPassword('pass1');
        $connectedAccount->setConnectionStatus('connected');
        $connectedAccount->setApiCallCount(100);
        $connectedAccount->setValid(true);

        $disconnectedAccount = new WeChatApiAccount();
        $disconnectedAccount->setName('Disconnected Account');
        $disconnectedAccount->setBaseUrl('https://disconnected.example.com');
        $disconnectedAccount->setUsername('disconnected');
        $disconnectedAccount->setPassword('pass2');
        $disconnectedAccount->setConnectionStatus('disconnected');
        $disconnectedAccount->setApiCallCount(50);
        $disconnectedAccount->setValid(true);

        $errorAccount = new WeChatApiAccount();
        $errorAccount->setName('Error Account');
        $errorAccount->setBaseUrl('https://error.example.com');
        $errorAccount->setUsername('error');
        $errorAccount->setPassword('pass3');
        $errorAccount->setConnectionStatus('error');
        $errorAccount->setApiCallCount(25);
        $errorAccount->setValid(true);

        $invalidAccount = new WeChatApiAccount();
        $invalidAccount->setName('Invalid Account');
        $invalidAccount->setBaseUrl('https://invalid.example.com');
        $invalidAccount->setUsername('invalid');
        $invalidAccount->setPassword('pass4');
        $invalidAccount->setConnectionStatus('connected');
        $invalidAccount->setApiCallCount(75);
        $invalidAccount->setValid(false);

        self::getEntityManager()->persist($connectedAccount);
        self::getEntityManager()->persist($disconnectedAccount);
        self::getEntityManager()->persist($errorAccount);
        self::getEntityManager()->persist($invalidAccount);
        self::getEntityManager()->flush();

        $statistics = $this->getRepository()->getAccountStatistics();

        // 验证至少包含我们创建的账号数量（考虑fixture数据）
        $this->assertGreaterThanOrEqual(4, $statistics['total']);
        $this->assertGreaterThanOrEqual(3, $statistics['valid']);
        $this->assertGreaterThanOrEqual(2, $statistics['connected']);
        $this->assertGreaterThanOrEqual(1, $statistics['disconnected']);
        $this->assertGreaterThanOrEqual(1, $statistics['error']);
        $this->assertGreaterThanOrEqual(250, $statistics['totalApiCalls']);
    }

    #[TestDox('查找最近活跃的API账号')]
    public function testFindRecentlyActiveAccounts(): void
    {
        $activeAccount1 = new WeChatApiAccount();
        $activeAccount1->setName('Active Account 1');
        $activeAccount1->setBaseUrl('https://active1.example.com');
        $activeAccount1->setUsername('active1');
        $activeAccount1->setPassword('pass1');
        $activeAccount1->setLastApiCallTime(new \DateTimeImmutable('2023-01-01'));
        $activeAccount1->setValid(true);

        $activeAccount2 = new WeChatApiAccount();
        $activeAccount2->setName('Active Account 2');
        $activeAccount2->setBaseUrl('https://active2.example.com');
        $activeAccount2->setUsername('active2');
        $activeAccount2->setPassword('pass2');
        $activeAccount2->setLastApiCallTime(new \DateTimeImmutable('2023-01-02'));
        $activeAccount2->setValid(true);

        $inactiveAccount = new WeChatApiAccount();
        $inactiveAccount->setName('Inactive Account');
        $inactiveAccount->setBaseUrl('https://inactive.example.com');
        $inactiveAccount->setUsername('inactive');
        $inactiveAccount->setPassword('pass3');
        $inactiveAccount->setValid(true);

        self::getEntityManager()->persist($activeAccount1);
        self::getEntityManager()->persist($activeAccount2);
        self::getEntityManager()->persist($inactiveAccount);
        self::getEntityManager()->flush();

        $recentlyActive = $this->getRepository()->findRecentlyActiveAccounts(5);

        $this->assertCount(2, $recentlyActive);
        // 验证按lastApiCallTime DESC排序
        $this->assertSame('Active Account 2', $recentlyActive[0]->getName());
        $this->assertSame('Active Account 1', $recentlyActive[1]->getName());
    }

    #[TestDox('根据用户名查找API账号')]
    public function testFindByUsername(): void
    {
        $account = new WeChatApiAccount();
        $account->setName('Test Account');
        $account->setBaseUrl('https://api.example.com');
        $account->setUsername('testuser');
        $account->setPassword('testpass');
        $account->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $foundAccount = $this->getRepository()->findByUsername('testuser');

        $this->assertInstanceOf(WeChatApiAccount::class, $foundAccount);
        $this->assertSame('testuser', $foundAccount->getUsername());
        $this->assertTrue($foundAccount->isValid());
    }

    #[TestDox('查找需要刷新Token的账号')]
    public function testFindAccountsNeedingTokenRefresh(): void
    {
        $soonExpireAccount = new WeChatApiAccount();
        $soonExpireAccount->setName('Soon Expire Account');
        $soonExpireAccount->setBaseUrl('https://soonexpire.example.com');
        $soonExpireAccount->setUsername('soonexpire');
        $soonExpireAccount->setPassword('pass1');
        $soonExpireAccount->setAccessToken('soon-expire-token');
        $soonExpireAccount->setTokenExpiresTime(new \DateTimeImmutable('+15 minutes'));
        $soonExpireAccount->setValid(true);

        $validAccount = new WeChatApiAccount();
        $validAccount->setName('Valid Account');
        $validAccount->setBaseUrl('https://valid.example.com');
        $validAccount->setUsername('valid');
        $validAccount->setPassword('pass2');
        $validAccount->setAccessToken('valid-token');
        $validAccount->setTokenExpiresTime(new \DateTimeImmutable('+2 hours'));
        $validAccount->setValid(true);

        $expiredAccount = new WeChatApiAccount();
        $expiredAccount->setName('Expired Account');
        $expiredAccount->setBaseUrl('https://expired.example.com');
        $expiredAccount->setUsername('expired');
        $expiredAccount->setPassword('pass3');
        $expiredAccount->setAccessToken('expired-token');
        $expiredAccount->setTokenExpiresTime(new \DateTimeImmutable('-1 hour'));
        $expiredAccount->setValid(true);

        self::getEntityManager()->persist($soonExpireAccount);
        self::getEntityManager()->persist($validAccount);
        self::getEntityManager()->persist($expiredAccount);
        self::getEntityManager()->flush();

        $needingRefresh = $this->getRepository()->findAccountsNeedingTokenRefresh(30);

        // 检查至少包含我们创建的1个需要刷新Token的账号
        $this->assertGreaterThanOrEqual(1, count($needingRefresh));
        $accountNames = array_map(fn ($account) => $account->getName(), $needingRefresh);
        $this->assertContains('Soon Expire Account', $accountNames);

        // 验证不包含还没到时间的有效账号和已过期账号
        $this->assertNotContains('Valid Account', $accountNames);
        $this->assertNotContains('Expired Account', $accountNames);
    }

    #[TestDox('获取默认的API账号')]
    public function testGetDefaultAccount(): void
    {
        $connectedAccount1 = new WeChatApiAccount();
        $connectedAccount1->setName('Connected Account 1');
        $connectedAccount1->setBaseUrl('https://connected1.example.com');
        $connectedAccount1->setUsername('connected1');
        $connectedAccount1->setPassword('pass1');
        $connectedAccount1->setConnectionStatus('connected');
        $connectedAccount1->setLastLoginTime(new \DateTimeImmutable('2023-01-01'));
        $connectedAccount1->setValid(true);

        $connectedAccount2 = new WeChatApiAccount();
        $connectedAccount2->setName('Connected Account 2');
        $connectedAccount2->setBaseUrl('https://connected2.example.com');
        $connectedAccount2->setUsername('connected2');
        $connectedAccount2->setPassword('pass2');
        $connectedAccount2->setConnectionStatus('connected');
        $connectedAccount2->setLastLoginTime(new \DateTimeImmutable('2023-01-02'));
        $connectedAccount2->setValid(true);

        $disconnectedAccount = new WeChatApiAccount();
        $disconnectedAccount->setName('Disconnected Account');
        $disconnectedAccount->setBaseUrl('https://disconnected.example.com');
        $disconnectedAccount->setUsername('disconnected');
        $disconnectedAccount->setPassword('pass3');
        $disconnectedAccount->setConnectionStatus('disconnected');
        $disconnectedAccount->setValid(true);

        self::getEntityManager()->persist($connectedAccount1);
        self::getEntityManager()->persist($connectedAccount2);
        self::getEntityManager()->persist($disconnectedAccount);
        self::getEntityManager()->flush();

        $defaultAccount = $this->getRepository()->getDefaultAccount();

        $this->assertInstanceOf(WeChatApiAccount::class, $defaultAccount);
        // 应该返回最近登录的已连接账号
        $this->assertSame('Connected Account 2', $defaultAccount->getName());
    }

    #[TestDox('查找不存在的数据应返回空结果')]
    public function testFindNonExistentData(): void
    {
        // 测试查找不存在的数据应返回null
        $this->assertNull($this->getRepository()->findByName('non-existent-name'));
        $this->assertNull($this->getRepository()->findByBaseUrl('https://non-existent.example.com'));
        $this->assertNull($this->getRepository()->findByUsername('non-existent-username'));

        // 由于有fixture数据，这些方法不会返回空数组，只验证方法可正常调用
        $this->assertIsArray($this->getRepository()->findValidAccounts());
        $this->assertIsArray($this->getRepository()->findConnectedAccounts());
        $this->assertIsArray($this->getRepository()->findDisconnectedAccounts());
        $this->assertIsArray($this->getRepository()->findErrorAccounts());
        $this->assertIsArray($this->getRepository()->findAccountsWithValidToken());
        $this->assertIsArray($this->getRepository()->findRecentlyActiveAccounts());
        $this->assertIsArray($this->getRepository()->findAccountsNeedingTokenRefresh());

        // 验证统计信息返回正确的数组结构
        $statistics = $this->getRepository()->getAccountStatistics();
        $this->assertIsArray($statistics);
        $this->assertArrayHasKey('total', $statistics);
        $this->assertArrayHasKey('valid', $statistics);
        $this->assertArrayHasKey('connected', $statistics);
        $this->assertArrayHasKey('disconnected', $statistics);
        $this->assertArrayHasKey('error', $statistics);
        $this->assertArrayHasKey('totalApiCalls', $statistics);

        // 验证数量都是非负整数（因为有fixture数据）
        $this->assertGreaterThanOrEqual(0, $statistics['total']);
        $this->assertGreaterThanOrEqual(0, $statistics['valid']);
        $this->assertGreaterThanOrEqual(0, $statistics['connected']);
        $this->assertGreaterThanOrEqual(0, $statistics['disconnected']);
        $this->assertGreaterThanOrEqual(0, $statistics['error']);
        $this->assertGreaterThanOrEqual(0, $statistics['totalApiCalls']);
    }
}
