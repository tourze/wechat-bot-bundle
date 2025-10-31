<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Repository\WeChatAccountRepository;

/**
 * 微信账号仓储简单测试
 *
 * @template-extends AbstractRepositoryTestCase<WeChatAccount>
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(WeChatAccountRepository::class)]
final class WeChatAccountRepositorySimpleTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 仓储通过服务容器获取
    }

    protected function createNewEntity(): object
    {
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('Test API Account ' . uniqid());
        $apiAccount->setBaseUrl('http://localhost:8080');
        $apiAccount->setUsername('test_user_' . uniqid());
        $apiAccount->setPassword('test_password');
        $apiAccount->setValid(true);

        $entity = new WeChatAccount();
        $entity->setApiAccount($apiAccount);
        $entity->setDeviceId('test-device-' . uniqid());
        $entity->setNickname('Test User');
        $entity->setStatus('online');
        $entity->setValid(true);

        return $entity;
    }

    protected function getRepository(): WeChatAccountRepository
    {
        return self::getService(WeChatAccountRepository::class);
    }

    public function testCanBeInstantiated(): void
    {
        $this->assertInstanceOf(WeChatAccountRepository::class, $this->getRepository());
    }

    public function testCountByStatus(): void
    {
        $result = $this->getRepository()->countByStatus();

        $this->assertIsArray($result);
        // 验证返回的是状态统计数组
        foreach ($result as $status => $count) {
            $this->assertIsString($status);
            $this->assertIsInt($count);
            $this->assertGreaterThanOrEqual(0, $count);
        }
    }

    public function testFindActiveAccounts(): void
    {
        $accounts = $this->getRepository()->findActiveAccounts();

        $this->assertIsArray($accounts);
        foreach ($accounts as $account) {
            $this->assertInstanceOf(WeChatAccount::class, $account);
            $this->assertTrue($account->isValid());
        }
    }

    public function testFindAllValidAccounts(): void
    {
        $accounts = $this->getRepository()->findAllValidAccounts();

        $this->assertIsArray($accounts);
        foreach ($accounts as $account) {
            $this->assertInstanceOf(WeChatAccount::class, $account);
            $this->assertTrue($account->isValid());
        }
    }

    public function testFindByDeviceId(): void
    {
        $result = $this->getRepository()->findByDeviceId('non-existent-device');
        $this->assertNull($result);
    }

    public function testFindByOffline(): void
    {
        $accounts = $this->getRepository()->findByOffline();

        $this->assertIsArray($accounts);
        foreach ($accounts as $account) {
            $this->assertInstanceOf(WeChatAccount::class, $account);
            $this->assertEquals('offline', $account->getStatus());
            $this->assertTrue($account->isValid());
        }
    }

    public function testFindByOnline(): void
    {
        $accounts = $this->getRepository()->findByOnline();

        $this->assertIsArray($accounts);
        foreach ($accounts as $account) {
            $this->assertInstanceOf(WeChatAccount::class, $account);
            $this->assertEquals('online', $account->getStatus());
            $this->assertTrue($account->isValid());
        }
    }

    public function testFindByPendingLogin(): void
    {
        $accounts = $this->getRepository()->findByPendingLogin();

        $this->assertIsArray($accounts);
        foreach ($accounts as $account) {
            $this->assertInstanceOf(WeChatAccount::class, $account);
            $this->assertEquals('pending_login', $account->getStatus());
            $this->assertTrue($account->isValid());
        }
    }

    public function testFindByWechatId(): void
    {
        $result = $this->getRepository()->findByWechatId('non-existent-wechat-id');
        $this->assertNull($result);
    }

    public function testFindOnlineAccounts(): void
    {
        $accounts = $this->getRepository()->findOnlineAccounts();

        $this->assertIsArray($accounts);
        foreach ($accounts as $account) {
            $this->assertInstanceOf(WeChatAccount::class, $account);
            $this->assertEquals('online', $account->getStatus());
            $this->assertTrue($account->isValid());
        }
    }

    public function testFindValid(): void
    {
        $accounts = $this->getRepository()->findValid();

        $this->assertIsArray($accounts);
        foreach ($accounts as $account) {
            $this->assertInstanceOf(WeChatAccount::class, $account);
            $this->assertTrue($account->isValid());
        }
    }

    public function testSave(): void
    {
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('Test API Account ' . uniqid());
        $apiAccount->setBaseUrl('http://localhost:8080');
        $apiAccount->setUsername('test_user_' . uniqid());
        $apiAccount->setPassword('test_password');
        $apiAccount->setValid(true);

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('save-test-device');
        $account->setNickname('Save Test User');
        $account->setStatus('online');
        $account->setValid(true);

        self::getEntityManager()->flush();

        $this->getRepository()->save($account, true);

        $foundAccount = $this->getRepository()->findByDeviceId('save-test-device');
        $this->assertInstanceOf(WeChatAccount::class, $foundAccount);
        $this->assertSame('save-test-device', $foundAccount->getDeviceId());
    }

    public function testRemove(): void
    {
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('Test API Account ' . uniqid());
        $apiAccount->setBaseUrl('http://localhost:8080');
        $apiAccount->setUsername('test_user_' . uniqid());
        $apiAccount->setPassword('test_password');
        $apiAccount->setValid(true);

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('remove-test-device');
        $account->setNickname('Remove Test User');
        $account->setStatus('online');
        $account->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $this->getRepository()->remove($account, true);

        $foundAccount = $this->getRepository()->findByDeviceId('remove-test-device');
        $this->assertNull($foundAccount);
    }
}
