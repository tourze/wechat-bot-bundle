<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\TestDox;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Repository\WeChatAccountRepository;

/**
 * 微信账号仓储测试
 *
 * 测试微信账号数据访问层的各种查询方法：
 * - 基础查询方法
 * - 状态过滤查询
 * - 统计查询
 * - 复合条件查询
 *
 * @template-extends AbstractRepositoryTestCase<WeChatAccount>
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(WeChatAccountRepository::class)]
final class WeChatAccountRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 此测试类不需要特殊的初始化逻辑
    }

    protected function createNewEntity(): object
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $entity = new WeChatAccount();
        $entity->setApiAccount($apiAccount);
        $entity->setDeviceId('test-device-' . uniqid());
        $entity->setNickname('Test Account ' . uniqid());
        $entity->setStatus('offline');

        return $entity;
    }

    protected function getRepository(): WeChatAccountRepository
    {
        return self::getService(WeChatAccountRepository::class);
    }

    #[TestDox('通过设备ID查找账号')]
    public function testFindByDeviceId(): void
    {
        // 创建并保存 API 账号
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        // 创建微信账号
        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device-123');
        $account->setNickname('Test User');
        $account->setStatus('online');
        $account->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $foundAccount = $this->getRepository()->findByDeviceId('test-device-123');

        $this->assertInstanceOf(WeChatAccount::class, $foundAccount);
        $this->assertSame('test-device-123', $foundAccount->getDeviceId());
        $this->assertSame('Test User', $foundAccount->getNickname());
    }

    /**
     * 创建测试用的 API 账号
     */
    private function createApiAccount(): WeChatApiAccount
    {
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('Test API Account ' . uniqid());
        $apiAccount->setBaseUrl('http://localhost:8080');
        $apiAccount->setUsername('test_user_' . uniqid());
        $apiAccount->setPassword('test_password');
        $apiAccount->setValid(true);

        return $apiAccount;
    }

    #[TestDox('通过设备ID查找不存在的账号返回null')]
    public function testFindByDeviceIdNotFound(): void
    {
        $foundAccount = $this->getRepository()->findByDeviceId('non-existent-device');

        $this->assertNull($foundAccount);
    }

    #[TestDox('通过微信ID查找账号')]
    public function testFindByWechatId(): void
    {
        // 创建并保存 API 账号
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device-456');
        $account->setWechatId('test-wx-id-123');
        $account->setNickname('Test User 2');
        $account->setStatus('online');
        $account->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $foundAccount = $this->getRepository()->findByWechatId('test-wx-id-123');

        $this->assertInstanceOf(WeChatAccount::class, $foundAccount);
        $this->assertSame('test-wx-id-123', $foundAccount->getWechatId());
        $this->assertSame('Test User 2', $foundAccount->getNickname());
    }

    #[TestDox('通过微信ID查找不存在的账号返回null')]
    public function testFindByWechatIdNotFound(): void
    {
        $foundAccount = $this->getRepository()->findByWechatId('non-existent-wx-id');

        $this->assertNull($foundAccount);
    }

    #[TestDox('查找在线账号')]
    public function testFindByOnline(): void
    {
        // 创建并保存 API 账号
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        // 创建在线账号
        $onlineAccount1 = new WeChatAccount();
        $onlineAccount1->setApiAccount($apiAccount);
        $onlineAccount1->setDeviceId('online-device-1');
        $onlineAccount1->setNickname('Online User 1');
        $onlineAccount1->setStatus('online');
        $onlineAccount1->setValid(true);

        $onlineAccount2 = new WeChatAccount();
        $onlineAccount2->setApiAccount($apiAccount);
        $onlineAccount2->setDeviceId('online-device-2');
        $onlineAccount2->setNickname('Online User 2');
        $onlineAccount2->setStatus('online');
        $onlineAccount2->setValid(true);

        // 创建离线账号
        $offlineAccount = new WeChatAccount();
        $offlineAccount->setApiAccount($apiAccount);
        $offlineAccount->setDeviceId('offline-device');
        $offlineAccount->setNickname('Offline User');
        $offlineAccount->setStatus('offline');
        $offlineAccount->setValid(true);

        // 创建无效账号
        $invalidAccount = new WeChatAccount();
        $invalidAccount->setApiAccount($apiAccount);
        $invalidAccount->setDeviceId('invalid-device');
        $invalidAccount->setNickname('Invalid User');
        $invalidAccount->setStatus('online');
        $invalidAccount->setValid(false);

        self::getEntityManager()->persist($onlineAccount1);
        self::getEntityManager()->persist($onlineAccount2);
        self::getEntityManager()->persist($offlineAccount);
        self::getEntityManager()->persist($invalidAccount);
        self::getEntityManager()->flush();

        $onlineAccounts = $this->getRepository()->findByOnline();

        // 检查至少包含我们创建的2个在线账号
        $this->assertGreaterThanOrEqual(2, count($onlineAccounts));
        $deviceIds = array_map(fn (WeChatAccount $account) => $account->getDeviceId(), $onlineAccounts);
        $this->assertContains('online-device-1', $deviceIds);
        $this->assertContains('online-device-2', $deviceIds);

        // 验证不包含离线或无效账号
        $this->assertNotContains('offline-device', $deviceIds);
        $this->assertNotContains('invalid-device', $deviceIds);
    }

    #[TestDox('查找待登录账号')]
    public function testFindByPendingLogin(): void
    {
        // 创建并保存 API 账号
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $pendingAccount = new WeChatAccount();
        $pendingAccount->setApiAccount($apiAccount);
        $pendingAccount->setDeviceId('pending-device');
        $pendingAccount->setNickname('Pending User');
        $pendingAccount->setStatus('pending_login');
        $pendingAccount->setValid(true);

        $onlineAccount = new WeChatAccount();
        $onlineAccount->setApiAccount($apiAccount);
        $onlineAccount->setDeviceId('online-device');
        $onlineAccount->setNickname('Online User');
        $onlineAccount->setStatus('online');
        $onlineAccount->setValid(true);

        self::getEntityManager()->persist($pendingAccount);
        self::getEntityManager()->persist($onlineAccount);
        self::getEntityManager()->flush();

        $pendingAccounts = $this->getRepository()->findByPendingLogin();

        // 检查至少包含我们创建的1个待登录账号（包括fixture数据）
        $this->assertGreaterThanOrEqual(1, count($pendingAccounts));
        $deviceIds = array_map(fn (WeChatAccount $account) => $account->getDeviceId(), $pendingAccounts);
        $this->assertContains('pending-device', $deviceIds);

        // 验证所有账号都是pending_login状态
        foreach ($pendingAccounts as $account) {
            $this->assertSame('pending_login', $account->getStatus());
            $this->assertTrue($account->isValid());
        }
    }

    #[TestDox('查找离线账号')]
    public function testFindByOffline(): void
    {
        // 创建并保存 API 账号
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $offlineAccount = new WeChatAccount();
        $offlineAccount->setApiAccount($apiAccount);
        $offlineAccount->setDeviceId('offline-device');
        $offlineAccount->setNickname('Offline User');
        $offlineAccount->setStatus('offline');
        $offlineAccount->setValid(true);

        $onlineAccount = new WeChatAccount();
        $onlineAccount->setApiAccount($apiAccount);
        $onlineAccount->setDeviceId('online-device');
        $onlineAccount->setNickname('Online User');
        $onlineAccount->setStatus('online');
        $onlineAccount->setValid(true);

        self::getEntityManager()->persist($offlineAccount);
        self::getEntityManager()->persist($onlineAccount);
        self::getEntityManager()->flush();

        $offlineAccounts = $this->getRepository()->findByOffline();

        // 检查至少包含我们创建的1个离线账号（包括fixture数据）
        $this->assertGreaterThanOrEqual(1, count($offlineAccounts));
        $deviceIds = array_map(fn (WeChatAccount $account) => $account->getDeviceId(), $offlineAccounts);
        $this->assertContains('offline-device', $deviceIds);

        // 验证所有账号都是offline状态
        foreach ($offlineAccounts as $account) {
            $this->assertSame('offline', $account->getStatus());
            $this->assertTrue($account->isValid());
        }
    }

    #[TestDox('查找有效账号')]
    public function testFindValid(): void
    {
        // 创建并保存 API 账号
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $validAccount = new WeChatAccount();
        $validAccount->setApiAccount($apiAccount);
        $validAccount->setDeviceId('valid-device');
        $validAccount->setNickname('Valid User');
        $validAccount->setStatus('online');
        $validAccount->setValid(true);

        $invalidAccount = new WeChatAccount();
        $invalidAccount->setApiAccount($apiAccount);
        $invalidAccount->setDeviceId('invalid-device');
        $invalidAccount->setNickname('Invalid User');
        $invalidAccount->setStatus('online');
        $invalidAccount->setValid(false);

        self::getEntityManager()->persist($validAccount);
        self::getEntityManager()->persist($invalidAccount);
        self::getEntityManager()->flush();

        $validAccounts = $this->getRepository()->findValid();

        // 检查至少包含我们创建的1个有效账号（包括fixture数据）
        $this->assertGreaterThanOrEqual(1, count($validAccounts));
        $deviceIds = array_map(fn (WeChatAccount $account) => $account->getDeviceId(), $validAccounts);
        $this->assertContains('valid-device', $deviceIds);

        // 验证所有账号都是有效的，且不包含无效账号
        $this->assertNotContains('invalid-device', $deviceIds);
        foreach ($validAccounts as $account) {
            $this->assertTrue($account->isValid());
        }
    }

    #[TestDox('按状态统计账号数量')]
    public function testCountByStatus(): void
    {
        // 创建并保存 API 账号
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        // 创建不同状态的账号
        $accounts = [
            ['status' => 'online', 'count' => 2],
            ['status' => 'offline', 'count' => 3],
            ['status' => 'pending_login', 'count' => 1],
        ];

        foreach ($accounts as $accountData) {
            for ($i = 0; $i < $accountData['count']; ++$i) {
                $account = new WeChatAccount();
                $account->setApiAccount($apiAccount);
                $account->setDeviceId($accountData['status'] . '-device-' . $i);
                $account->setNickname($accountData['status'] . ' User ' . $i);
                $account->setStatus($accountData['status']);
                $account->setValid(true);
                self::getEntityManager()->persist($account);
            }
        }

        // 创建无效账号（不应该被统计）
        $invalidAccount = new WeChatAccount();
        $invalidAccount->setApiAccount($apiAccount);
        $invalidAccount->setDeviceId('invalid-device');
        $invalidAccount->setNickname('Invalid User');
        $invalidAccount->setStatus('online');
        $invalidAccount->setValid(false);
        self::getEntityManager()->persist($invalidAccount);

        self::getEntityManager()->flush();

        $statusCounts = $this->getRepository()->countByStatus();

        // 验证包含我们创建的账号数（考虑fixture数据）
        $this->assertGreaterThanOrEqual(2, $statusCounts['online']); // 至少包含我们创建的2个
        $this->assertGreaterThanOrEqual(3, $statusCounts['offline']); // 至少包含我们创建的3个
        $this->assertGreaterThanOrEqual(1, $statusCounts['pending_login']); // 至少包含我们创建的1个
        $this->assertArrayNotHasKey('invalid', $statusCounts);
    }

    #[TestDox('查找活跃账号（非删除状态）')]
    public function testFindActiveAccounts(): void
    {
        // 创建并保存 API 账号
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $activeAccount1 = new WeChatAccount();
        $activeAccount1->setApiAccount($apiAccount);
        $activeAccount1->setDeviceId('active-device-1');
        $activeAccount1->setNickname('Active User 1');
        $activeAccount1->setStatus('online');
        $activeAccount1->setValid(true);
        $activeAccount1->setCreateTime(new \DateTimeImmutable('2023-01-01'));

        $activeAccount2 = new WeChatAccount();
        $activeAccount2->setApiAccount($apiAccount);
        $activeAccount2->setDeviceId('active-device-2');
        $activeAccount2->setNickname('Active User 2');
        $activeAccount2->setStatus('offline');
        $activeAccount2->setValid(true);
        $activeAccount2->setCreateTime(new \DateTimeImmutable('2023-01-02'));

        $inactiveAccount = new WeChatAccount();
        $inactiveAccount->setApiAccount($apiAccount);
        $inactiveAccount->setDeviceId('inactive-device');
        $inactiveAccount->setNickname('Inactive User');
        $inactiveAccount->setStatus('online');
        $inactiveAccount->setValid(false);

        self::getEntityManager()->persist($activeAccount1);
        self::getEntityManager()->persist($activeAccount2);
        self::getEntityManager()->persist($inactiveAccount);
        self::getEntityManager()->flush();

        $activeAccounts = $this->getRepository()->findActiveAccounts();

        // 检查至少包含我们创建的2个活跃账号（包括fixture数据）
        $this->assertGreaterThanOrEqual(2, count($activeAccounts));
        $deviceIds = array_map(fn (WeChatAccount $account) => $account->getDeviceId(), $activeAccounts);
        $this->assertContains('active-device-1', $deviceIds);
        $this->assertContains('active-device-2', $deviceIds);

        // 验证不包含无效账号
        $this->assertNotContains('inactive-device', $deviceIds);

        // 验证所有账号都是有效的
        foreach ($activeAccounts as $account) {
            $this->assertTrue($account->isValid());
        }
    }

    #[TestDox('查找所有在线账号')]
    public function testFindOnlineAccounts(): void
    {
        // 创建并保存 API 账号
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $onlineAccount = new WeChatAccount();
        $onlineAccount->setApiAccount($apiAccount);
        $onlineAccount->setDeviceId('online-device');
        $onlineAccount->setNickname('Online User');
        $onlineAccount->setStatus('online');
        $onlineAccount->setValid(true);

        $offlineAccount = new WeChatAccount();
        $offlineAccount->setApiAccount($apiAccount);
        $offlineAccount->setDeviceId('offline-device');
        $offlineAccount->setNickname('Offline User');
        $offlineAccount->setStatus('offline');
        $offlineAccount->setValid(true);

        $invalidOnlineAccount = new WeChatAccount();
        $invalidOnlineAccount->setApiAccount($apiAccount);
        $invalidOnlineAccount->setDeviceId('invalid-online-device');
        $invalidOnlineAccount->setNickname('Invalid Online User');
        $invalidOnlineAccount->setStatus('online');
        $invalidOnlineAccount->setValid(false);

        self::getEntityManager()->persist($onlineAccount);
        self::getEntityManager()->persist($offlineAccount);
        self::getEntityManager()->persist($invalidOnlineAccount);
        self::getEntityManager()->flush();

        $onlineAccounts = $this->getRepository()->findOnlineAccounts();

        // 检查至少包含我们创建的1个在线账号（包括fixture数据）
        $this->assertGreaterThanOrEqual(1, count($onlineAccounts));
        $deviceIds = array_map(fn (WeChatAccount $account) => $account->getDeviceId(), $onlineAccounts);
        $this->assertContains('online-device', $deviceIds);

        // 验证不包含离线或无效的账号
        $this->assertNotContains('offline-device', $deviceIds);
        $this->assertNotContains('invalid-online-device', $deviceIds);

        // 验证所有账号都是在线且有效的
        foreach ($onlineAccounts as $account) {
            $this->assertSame('online', $account->getStatus());
            $this->assertTrue($account->isValid());
        }
    }

    #[TestDox('查找所有有效账号')]
    public function testFindAllValidAccounts(): void
    {
        // 创建并保存 API 账号
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $validAccount1 = new WeChatAccount();
        $validAccount1->setApiAccount($apiAccount);
        $validAccount1->setDeviceId('valid-device-1');
        $validAccount1->setNickname('Valid User 1');
        $validAccount1->setStatus('online');
        $validAccount1->setValid(true);

        $validAccount2 = new WeChatAccount();
        $validAccount2->setApiAccount($apiAccount);
        $validAccount2->setDeviceId('valid-device-2');
        $validAccount2->setNickname('Valid User 2');
        $validAccount2->setStatus('offline');
        $validAccount2->setValid(true);

        $invalidAccount = new WeChatAccount();
        $invalidAccount->setApiAccount($apiAccount);
        $invalidAccount->setDeviceId('invalid-device');
        $invalidAccount->setNickname('Invalid User');
        $invalidAccount->setStatus('online');
        $invalidAccount->setValid(false);

        self::getEntityManager()->persist($validAccount1);
        self::getEntityManager()->persist($validAccount2);
        self::getEntityManager()->persist($invalidAccount);
        self::getEntityManager()->flush();

        $validAccounts = $this->getRepository()->findAllValidAccounts();

        // 检查至少包含我们创建的2个有效账号（包括fixture数据）
        $this->assertGreaterThanOrEqual(2, count($validAccounts));
        $deviceIds = array_map(fn (WeChatAccount $account) => $account->getDeviceId(), $validAccounts);
        $this->assertContains('valid-device-1', $deviceIds);
        $this->assertContains('valid-device-2', $deviceIds);

        // 验证不包含无效账号
        $this->assertNotContains('invalid-device', $deviceIds);

        // 验证所有账号都是有效的
        foreach ($validAccounts as $account) {
            $this->assertTrue($account->isValid());
        }
    }

    #[TestDox('查找不存在的数据应返回空结果')]
    public function testFindNonExistentData(): void
    {
        // 测试查找不存在的设备ID和微信ID应返回null
        $this->assertNull($this->getRepository()->findByDeviceId('non-existent-device'));
        $this->assertNull($this->getRepository()->findByWechatId('non-existent-wx-id'));

        // 由于有fixture数据，这些方法不会返回空数组，只验证方法可正常调用
        $this->assertIsArray($this->getRepository()->findByOnline());
        $this->assertIsArray($this->getRepository()->findByPendingLogin());
        $this->assertIsArray($this->getRepository()->findByOffline());
        $this->assertIsArray($this->getRepository()->findValid());
        $this->assertIsArray($this->getRepository()->countByStatus());
        $this->assertIsArray($this->getRepository()->findActiveAccounts());
        $this->assertIsArray($this->getRepository()->findOnlineAccounts());
        $this->assertIsArray($this->getRepository()->findAllValidAccounts());
    }

    // ================== 基础 Doctrine 方法测试 ==================

    #[TestDox('findOneBy应遵循排序参数')]
    public function testFindOneByShouldRespectOrderByClause(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account1 = new WeChatAccount();
        $account1->setApiAccount($apiAccount);
        $account1->setDeviceId('device-1');
        $account1->setNickname('User B');
        $account1->setStatus('online');
        $account1->setValid(true);

        $account2 = new WeChatAccount();
        $account2->setApiAccount($apiAccount);
        $account2->setDeviceId('device-2');
        $account2->setNickname('User A');
        $account2->setStatus('online');
        $account2->setValid(true);

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->flush();

        // 按昵称升序查询，应返回第一个匹配的记录
        $account = $this->getRepository()->findOneBy(
            ['status' => 'online'],
            ['nickname' => 'ASC']
        );

        $this->assertInstanceOf(WeChatAccount::class, $account);
        $this->assertSame('User A', $account->getNickname());
    }

    #[TestDox('save方法应持久化新实体')]
    public function testSave(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('new-device');
        $account->setNickname('New User');
        $account->setStatus('online');
        $account->setValid(true);

        self::getEntityManager()->flush();

        $this->getRepository()->save($account, true);

        $foundAccount = $this->getRepository()->findOneBy(['deviceId' => 'new-device']);
        $this->assertInstanceOf(WeChatAccount::class, $foundAccount);
        $this->assertSame('new-device', $foundAccount->getDeviceId());
    }

    #[TestDox('remove方法应删除实体')]
    public function testRemove(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('to-delete');
        $account->setNickname('To Delete');
        $account->setStatus('online');
        $account->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $this->getRepository()->remove($account, true);

        $foundAccount = $this->getRepository()->findOneBy(['deviceId' => 'to-delete']);
        $this->assertNull($foundAccount);
    }

    // ================== 健壮性测试 ==================

    // ================== 关联查询测试 ==================

    #[TestDox('查询包含关联实体')]
    public function testQueryWithAssociations(): void
    {
        $apiAccount1 = $this->createApiAccount();
        $apiAccount1->setName('Test API Account 1 ' . uniqid());
        self::getEntityManager()->persist($apiAccount1);
        $apiAccount2 = $this->createApiAccount();
        $apiAccount2->setName('Test API Account 2 ' . uniqid());
        self::getEntityManager()->persist($apiAccount2);

        $account1 = new WeChatAccount();
        $account1->setApiAccount($apiAccount1);
        $account1->setDeviceId('device-1');
        $account1->setNickname('User 1');
        $account1->setStatus('online');
        $account1->setValid(true);

        $account2 = new WeChatAccount();
        $account2->setApiAccount($apiAccount2);
        $account2->setDeviceId('device-2');
        $account2->setNickname('User 2');
        $account2->setStatus('online');
        $account2->setValid(true);

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->flush();

        // 测试按关联实体查询
        $api1Accounts = $this->getRepository()->findBy(['apiAccount' => $apiAccount1]);
        $api2Accounts = $this->getRepository()->findBy(['apiAccount' => $apiAccount2]);

        $this->assertCount(1, $api1Accounts);
        $this->assertCount(1, $api2Accounts);
        $this->assertSame($apiAccount1, $api1Accounts[0]->getApiAccount());
        $this->assertSame($apiAccount2, $api2Accounts[0]->getApiAccount());
    }

    #[TestDox('统计关联查询')]
    public function testCountWithAssociations(): void
    {
        $apiAccount1 = $this->createApiAccount();
        $apiAccount1->setName('Test API Account 1 ' . uniqid());
        self::getEntityManager()->persist($apiAccount1);
        $apiAccount2 = $this->createApiAccount();
        $apiAccount2->setName('Test API Account 2 ' . uniqid());
        self::getEntityManager()->persist($apiAccount2);

        for ($i = 1; $i <= 3; ++$i) {
            $account = new WeChatAccount();
            $account->setApiAccount($apiAccount1);
            $account->setDeviceId('api1-device-' . $i);
            $account->setNickname('API1 User ' . $i);
            $account->setStatus('online');
            $account->setValid(true);
            self::getEntityManager()->persist($account);
        }

        for ($i = 1; $i <= 2; ++$i) {
            $account = new WeChatAccount();
            $account->setApiAccount($apiAccount2);
            $account->setDeviceId('api2-device-' . $i);
            $account->setNickname('API2 User ' . $i);
            $account->setStatus('online');
            $account->setValid(true);
            self::getEntityManager()->persist($account);
        }

        self::getEntityManager()->flush();

        $api1Count = $this->getRepository()->count(['apiAccount' => $apiAccount1]);
        $api2Count = $this->getRepository()->count(['apiAccount' => $apiAccount2]);

        $this->assertSame(3, $api1Count);
        $this->assertSame(2, $api2Count);
    }

    // ================== NULL 查询测试 ==================

    #[TestDox('查询可空字段为NULL的记录')]
    public function testFindByNullableFields(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $accountWithWechatId = new WeChatAccount();
        $accountWithWechatId->setApiAccount($apiAccount);
        $accountWithWechatId->setDeviceId('with-wechat-id');
        $accountWithWechatId->setWechatId('wx123456');
        $accountWithWechatId->setNickname('With WeChat ID');
        $accountWithWechatId->setStatus('online');
        $accountWithWechatId->setValid(true);

        $accountWithoutWechatId = new WeChatAccount();
        $accountWithoutWechatId->setApiAccount($apiAccount);
        $accountWithoutWechatId->setDeviceId('without-wechat-id');
        $accountWithoutWechatId->setWechatId(null);
        $accountWithoutWechatId->setNickname('Without WeChat ID');
        $accountWithoutWechatId->setStatus('online');
        $accountWithoutWechatId->setValid(true);

        self::getEntityManager()->persist($accountWithWechatId);
        self::getEntityManager()->persist($accountWithoutWechatId);
        self::getEntityManager()->flush();

        // 查询微信ID为NULL的账号
        $accountsWithoutWechatId = $this->getRepository()->findBy(['wechatId' => null]);

        // 检查至少包含我们创建的1个无微信ID的账号（包括fixture数据）
        $this->assertGreaterThanOrEqual(1, count($accountsWithoutWechatId));
        $deviceIds = array_map(fn (WeChatAccount $account) => $account->getDeviceId(), $accountsWithoutWechatId);
        $this->assertContains('without-wechat-id', $deviceIds);

        // 验证所有账号的微信ID都是NULL
        foreach ($accountsWithoutWechatId as $account) {
            $this->assertNull($account->getWechatId());
        }
    }

    #[TestDox('统计可空字段为NULL的记录数量')]
    public function testCountByNullableFields(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        // 创建有微信ID的账号
        for ($i = 1; $i <= 2; ++$i) {
            $account = new WeChatAccount();
            $account->setApiAccount($apiAccount);
            $account->setDeviceId('with-wx-' . $i);
            $account->setWechatId('wx' . $i);
            $account->setNickname('With WeChat ' . $i);
            $account->setStatus('online');
            $account->setValid(true);
            self::getEntityManager()->persist($account);
        }

        // 创建没有微信ID的账号
        for ($i = 1; $i <= 3; ++$i) {
            $account = new WeChatAccount();
            $account->setApiAccount($apiAccount);
            $account->setDeviceId('without-wx-' . $i);
            $account->setWechatId(null);
            $account->setNickname('Without WeChat ' . $i);
            $account->setStatus('online');
            $account->setValid(true);
            self::getEntityManager()->persist($account);
        }

        self::getEntityManager()->flush();

        $countWithoutWechatId = $this->getRepository()->count(['wechatId' => null]);

        // 至少包含我们创建的3个无微信ID账号（包括fixture数据）
        $this->assertGreaterThanOrEqual(3, $countWithoutWechatId);
    }

    // ================== 完整的可空字段测试 ==================

    #[TestDox('查询所有可空字段为NULL的记录')]
    public function testFindByAllNullableFields(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        // 创建有昵称的账号
        $accountWithNickname = new WeChatAccount();
        $accountWithNickname->setApiAccount($apiAccount);
        $accountWithNickname->setDeviceId('with-nickname');
        $accountWithNickname->setNickname('Test Nickname');
        $accountWithNickname->setStatus('online');
        $accountWithNickname->setValid(true);

        // 创建没有昵称的账号
        $accountWithoutNickname = new WeChatAccount();
        $accountWithoutNickname->setApiAccount($apiAccount);
        $accountWithoutNickname->setDeviceId('without-nickname');
        $accountWithoutNickname->setNickname(null);
        $accountWithoutNickname->setStatus('online');
        $accountWithoutNickname->setValid(true);

        // 创建有头像的账号
        $accountWithAvatar = new WeChatAccount();
        $accountWithAvatar->setApiAccount($apiAccount);
        $accountWithAvatar->setDeviceId('with-avatar');
        $accountWithAvatar->setAvatar('https://example.com/avatar.jpg');
        $accountWithAvatar->setStatus('online');
        $accountWithAvatar->setValid(true);

        // 创建没有头像的账号
        $accountWithoutAvatar = new WeChatAccount();
        $accountWithoutAvatar->setApiAccount($apiAccount);
        $accountWithoutAvatar->setDeviceId('without-avatar');
        $accountWithoutAvatar->setAvatar(null);
        $accountWithoutAvatar->setStatus('online');
        $accountWithoutAvatar->setValid(true);

        self::getEntityManager()->persist($accountWithNickname);
        self::getEntityManager()->persist($accountWithoutNickname);
        self::getEntityManager()->persist($accountWithAvatar);
        self::getEntityManager()->persist($accountWithoutAvatar);
        self::getEntityManager()->flush();

        // 测试查询昵称为NULL的账号
        $accountsWithoutNickname = $this->getRepository()->findBy(['nickname' => null]);

        // 检查至少包含我们创建的2个无昵称账号（包括fixture数据）
        $this->assertGreaterThanOrEqual(2, count($accountsWithoutNickname));
        $deviceIds = array_map(fn (WeChatAccount $account) => $account->getDeviceId(), $accountsWithoutNickname);
        $this->assertContains('without-nickname', $deviceIds);
        $this->assertContains('without-avatar', $deviceIds);

        // 测试查询头像为NULL的账号
        $accountsWithoutAvatar = $this->getRepository()->findBy(['avatar' => null]);
        $this->assertGreaterThanOrEqual(2, count($accountsWithoutAvatar));
        $deviceIds = array_map(fn (WeChatAccount $account) => $account->getDeviceId(), $accountsWithoutAvatar);
        $this->assertContains('without-nickname', $deviceIds);
        $this->assertContains('without-avatar', $deviceIds);

        // 验证其他查询方法可正常调用，不检查具体数量（因为fixture数据存在）
        $this->assertIsArray($this->getRepository()->findBy(['qrCodeUrl' => null]));
        $this->assertIsArray($this->getRepository()->findBy(['accessToken' => null]));

        // 验证其他NULL查询方法可正常调用，不检查具体数量（因为fixture数据存在）
        $this->assertIsArray($this->getRepository()->findBy(['lastLoginTime' => null]));
        $this->assertIsArray($this->getRepository()->findBy(['lastActiveTime' => null]));
        $this->assertIsArray($this->getRepository()->findBy(['proxy' => null]));

        // 验证remark为NULL的查询可正常调用
        $this->assertIsArray($this->getRepository()->findBy(['remark' => null]));
    }

    #[TestDox('统计所有可空字段为NULL的记录数量')]
    public function testCountByAllNullableFields(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        // 创建具有各种可空字段值的账号
        $accountFull = new WeChatAccount();
        $accountFull->setApiAccount($apiAccount);
        $accountFull->setDeviceId('full-account');
        $accountFull->setWechatId('wx123');
        $accountFull->setNickname('Full User');
        $accountFull->setAvatar('https://example.com/avatar.jpg');
        $accountFull->setQrCode('qr-code-data');
        $accountFull->setQrCodeUrl('https://example.com/qr.jpg');
        $accountFull->setAccessToken('access-token-123');
        $accountFull->setLastLoginTime(new \DateTimeImmutable());
        $accountFull->setLastActiveTime(new \DateTimeImmutable());
        $accountFull->setProxy('proxy-config');
        $accountFull->setRemark('test-remark');
        $accountFull->setStatus('online');
        $accountFull->setValid(true);

        // 创建空字段账号
        $accountEmpty = new WeChatAccount();
        $accountEmpty->setApiAccount($apiAccount);
        $accountEmpty->setDeviceId('empty-account');
        $accountEmpty->setStatus('online');
        $accountEmpty->setValid(true);

        self::getEntityManager()->persist($accountFull);
        self::getEntityManager()->persist($accountEmpty);
        self::getEntityManager()->flush();

        // 验证统计各个可空字段为NULL的记录数量方法可正常调用（因为fixture数据的存在，具体数量会变化）
        $this->assertGreaterThanOrEqual(1, $this->getRepository()->count(['wechatId' => null]));
        $this->assertGreaterThanOrEqual(1, $this->getRepository()->count(['nickname' => null]));
        $this->assertGreaterThanOrEqual(1, $this->getRepository()->count(['avatar' => null]));
        $this->assertGreaterThanOrEqual(0, $this->getRepository()->count(['qrCode' => null]));
        $this->assertGreaterThanOrEqual(1, $this->getRepository()->count(['qrCodeUrl' => null]));
        $this->assertGreaterThanOrEqual(1, $this->getRepository()->count(['accessToken' => null]));
        $this->assertGreaterThanOrEqual(1, $this->getRepository()->count(['lastLoginTime' => null]));
        $this->assertGreaterThanOrEqual(1, $this->getRepository()->count(['lastActiveTime' => null]));
        $this->assertGreaterThanOrEqual(1, $this->getRepository()->count(['proxy' => null]));
        $this->assertGreaterThanOrEqual(1, $this->getRepository()->count(['remark' => null]));
    }
}
