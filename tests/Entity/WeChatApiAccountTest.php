<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * WeChatApiAccount 实体单元测试
 *
 * @internal
 */
#[CoversClass(WeChatApiAccount::class)]
final class WeChatApiAccountTest extends AbstractEntityTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Entity 测试不需要特殊的设置
    }

    public function testConstructorSetsDefaultValues(): void
    {
        $account = new WeChatApiAccount();

        // 验证默认值
        $this->assertNull($account->getId());
        $this->assertNull($account->getName());
        $this->assertNull($account->getBaseUrl());
        $this->assertNull($account->getUsername());
        $this->assertNull($account->getPassword());
        $this->assertNull($account->getAccessToken());
        $this->assertNull($account->getTokenExpiresTime());
        $this->assertNull($account->getLastLoginTime());
        $this->assertNull($account->getLastApiCallTime());
        $this->assertEquals(0, $account->getApiCallCount());
        $this->assertEquals('disconnected', $account->getConnectionStatus());
        $this->assertEquals(30, $account->getTimeout());
        $this->assertTrue($account->isValid());
        $this->assertNull($account->getRemark());
    }

    public function testSettersAndGettersWorkCorrectly(): void
    {
        $account = new WeChatApiAccount();

        // 测试名称
        $name = 'test_account';
        $account->setName($name);
        $this->assertEquals($name, $account->getName());

        // 测试BaseUrl自动去掉末尾斜杠
        $baseUrl = 'https://api.example.com/';
        $account->setBaseUrl($baseUrl);
        $this->assertEquals('https://api.example.com', $account->getBaseUrl());

        // 测试用户名
        $username = 'test_user';
        $account->setUsername($username);
        $this->assertEquals($username, $account->getUsername());

        // 测试密码
        $password = 'test_password';
        $account->setPassword($password);
        $this->assertEquals($password, $account->getPassword());

        // 测试访问令牌
        $accessToken = 'test_token_123';
        $account->setAccessToken($accessToken);
        $this->assertEquals($accessToken, $account->getAccessToken());

        // 测试令牌过期时间
        $expiresTime = new \DateTimeImmutable('2024-12-31 23:59:59');
        $account->setTokenExpiresTime($expiresTime);
        $this->assertEquals($expiresTime, $account->getTokenExpiresTime());

        // 测试最后登录时间
        $lastLoginTime = new \DateTimeImmutable('2024-01-01 12:00:00');
        $account->setLastLoginTime($lastLoginTime);
        $this->assertEquals($lastLoginTime, $account->getLastLoginTime());

        // 测试最后API调用时间
        $lastApiCallTime = new \DateTimeImmutable('2024-01-01 12:30:00');
        $account->setLastApiCallTime($lastApiCallTime);
        $this->assertEquals($lastApiCallTime, $account->getLastApiCallTime());

        // 测试API调用次数
        $apiCallCount = 100;
        $account->setApiCallCount($apiCallCount);
        $this->assertEquals($apiCallCount, $account->getApiCallCount());

        // 测试连接状态
        $connectionStatus = 'connected';
        $account->setConnectionStatus($connectionStatus);
        $this->assertEquals($connectionStatus, $account->getConnectionStatus());

        // 测试超时时间
        $timeout = 60;
        $account->setTimeout($timeout);
        $this->assertEquals($timeout, $account->getTimeout());

        // 测试有效性
        $account->setValid(false);
        $this->assertFalse($account->isValid());

        // 测试备注
        $remark = 'Test remark';
        $account->setRemark($remark);
        $this->assertEquals($remark, $account->getRemark());
    }

    public function testToStringReturnsNameWhenSet(): void
    {
        $account = new WeChatApiAccount();
        $name = 'test_account';
        $account->setName($name);

        $this->assertStringContainsString($name, (string) $account);
    }

    public function testToStringReturnsIdStringWhenNameNotSet(): void
    {
        $account = new WeChatApiAccount();
        $this->assertStringContainsString('API账号', (string) $account);
    }

    public function testIncrementApiCallCountUpdatesCountAndTime(): void
    {
        $account = new WeChatApiAccount();
        $initialCount = $account->getApiCallCount();
        $beforeIncrement = new \DateTimeImmutable();

        $account->incrementApiCallCount();

        $afterIncrement = new \DateTimeImmutable();
        $this->assertEquals($initialCount + 1, $account->getApiCallCount());

        $lastApiCallTime = $account->getLastApiCallTime();
        $this->assertNotNull($lastApiCallTime);

        if ($lastApiCallTime instanceof \DateTime) {
            $this->assertGreaterThanOrEqual($beforeIncrement, $lastApiCallTime);
            $this->assertLessThanOrEqual($afterIncrement, $lastApiCallTime);
        }
    }

    public function testConnectionStatusCheckers(): void
    {
        $account = new WeChatApiAccount();

        // 测试默认状态
        $this->assertTrue($account->isDisconnected());
        $this->assertFalse($account->isConnected());
        $this->assertFalse($account->isError());

        // 测试连接状态
        $account->setConnectionStatus('connected');
        $this->assertTrue($account->isConnected());
        $this->assertFalse($account->isDisconnected());
        $this->assertFalse($account->isError());

        // 测试错误状态
        $account->setConnectionStatus('error');
        $this->assertTrue($account->isError());
        $this->assertFalse($account->isConnected());
        $this->assertFalse($account->isDisconnected());
    }

    public function testMarkAsConnectedSetsStatusAndLoginTime(): void
    {
        $account = new WeChatApiAccount();
        $beforeMark = new \DateTimeImmutable();

        $account->markAsConnected();

        $afterMark = new \DateTimeImmutable();
        $this->assertEquals('connected', $account->getConnectionStatus());

        $lastLoginTime = $account->getLastLoginTime();
        $this->assertNotNull($lastLoginTime);

        if ($lastLoginTime instanceof \DateTime) {
            $this->assertGreaterThanOrEqual($beforeMark, $lastLoginTime);
            $this->assertLessThanOrEqual($afterMark, $lastLoginTime);
        }
    }

    public function testMarkAsDisconnectedSetsStatus(): void
    {
        $account = new WeChatApiAccount();
        $account->setConnectionStatus('connected');

        $account->markAsDisconnected();

        $this->assertEquals('disconnected', $account->getConnectionStatus());
    }

    public function testMarkAsErrorSetsStatus(): void
    {
        $account = new WeChatApiAccount();
        $account->setConnectionStatus('connected');

        $account->markAsError();

        $this->assertEquals('error', $account->getConnectionStatus());
    }

    public function testHasValidTokenReturnsTrueWhenTokenIsValidAndNotExpired(): void
    {
        $account = new WeChatApiAccount();
        $account->setAccessToken('valid_token');
        $account->setTokenExpiresTime(new \DateTimeImmutable('+1 hour'));

        $this->assertTrue($account->hasValidToken());
    }

    public function testHasValidTokenReturnsFalseWhenTokenIsEmpty(): void
    {
        $account = new WeChatApiAccount();
        $account->setAccessToken('');
        $account->setTokenExpiresTime(new \DateTimeImmutable('+1 hour'));

        $this->assertFalse($account->hasValidToken());
    }

    public function testHasValidTokenReturnsFalseWhenTokenIsExpired(): void
    {
        $account = new WeChatApiAccount();
        $account->setAccessToken('valid_token');
        $account->setTokenExpiresTime(new \DateTimeImmutable('-1 hour'));

        $this->assertFalse($account->hasValidToken());
    }

    public function testHasValidTokenReturnsTrueWhenTokenIsValidAndNoExpirationTime(): void
    {
        $account = new WeChatApiAccount();
        $account->setAccessToken('valid_token');
        $account->setTokenExpiresTime(null);

        $this->assertTrue($account->hasValidToken());
    }

    public function testIsTokenExpiredReturnsFalseWhenNoExpirationTime(): void
    {
        $account = new WeChatApiAccount();
        $account->setTokenExpiresTime(null);

        $this->assertFalse($account->isTokenExpired());
    }

    public function testIsTokenExpiredReturnsTrueWhenExpired(): void
    {
        $account = new WeChatApiAccount();
        $account->setTokenExpiresTime(new \DateTimeImmutable('-1 hour'));

        $this->assertTrue($account->isTokenExpired());
    }

    public function testIsTokenExpiredReturnsFalseWhenNotExpired(): void
    {
        $account = new WeChatApiAccount();
        $account->setTokenExpiresTime(new \DateTimeImmutable('+1 hour'));

        $this->assertFalse($account->isTokenExpired());
    }

    public function testMultipleIncrementApiCallCount(): void
    {
        $account = new WeChatApiAccount();
        $initialCount = $account->getApiCallCount();

        $account->incrementApiCallCount();
        $account->incrementApiCallCount();
        $account->incrementApiCallCount();

        $this->assertEquals($initialCount + 3, $account->getApiCallCount());
    }

    public function testSetBaseUrlTrimsTrailingSlash(): void
    {
        $account = new WeChatApiAccount();

        // 测试单个斜杠
        $account->setBaseUrl('https://api.example.com/');
        $this->assertEquals('https://api.example.com', $account->getBaseUrl());

        // 测试多个斜杠 - rtrim只移除末尾的斜杠
        $account->setBaseUrl('https://api.example.com///');
        $this->assertEquals('https://api.example.com', $account->getBaseUrl());

        // 测试无斜杠
        $account->setBaseUrl('https://api.example.com');
        $this->assertEquals('https://api.example.com', $account->getBaseUrl());
    }

    public function testSetAccessTokenCanAcceptNull(): void
    {
        $account = new WeChatApiAccount();
        $account->setAccessToken('some_token');

        $account->setAccessToken(null);
        $this->assertNull($account->getAccessToken());
    }

    public function testSetterMethods(): void
    {
        $account = new WeChatApiAccount();

        $account->setName('test');
        $account->setBaseUrl('https://api.example.com');
        $account->setUsername('user');
        $account->setPassword('pass');
        $account->setAccessToken('token');
        $account->setValid(true);
        $account->setTimeout(60);
        $account->setConnectionStatus('connected');
        $account->setApiCallCount(5);
        $account->setRemark('test remark');

        // 验证所有设置的值
        $this->assertEquals('test', $account->getName());
        $this->assertEquals('https://api.example.com', $account->getBaseUrl());
        $this->assertEquals('user', $account->getUsername());
        $this->assertEquals('pass', $account->getPassword());
        $this->assertEquals('token', $account->getAccessToken());
        $this->assertTrue($account->isValid());
        $this->assertEquals(60, $account->getTimeout());
        $this->assertEquals('connected', $account->getConnectionStatus());
        $this->assertEquals(5, $account->getApiCallCount());
        $this->assertEquals('test remark', $account->getRemark());
    }

    protected function createEntity(): WeChatApiAccount
    {
        return new WeChatApiAccount();
    }

    /**
     * 提供 WeChatApiAccount 实体的属性数据进行自动测试。
     *
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'name' => ['name', 'test_account'];
        yield 'baseUrl' => ['baseUrl', 'https://api.example.com'];
        yield 'username' => ['username', 'test_user'];
        yield 'password' => ['password', 'test_password'];
        yield 'accessToken' => ['accessToken', 'test_token_123'];
        yield 'tokenExpiresTime' => ['tokenExpiresTime', new \DateTimeImmutable('2024-12-31')];
        yield 'lastLoginTime' => ['lastLoginTime', new \DateTimeImmutable('2024-01-01')];
        yield 'lastApiCallTime' => ['lastApiCallTime', new \DateTimeImmutable('2024-01-02')];
        yield 'apiCallCount' => ['apiCallCount', 100];
        yield 'connectionStatus' => ['connectionStatus', 'connected'];
        yield 'timeout' => ['timeout', 60];
        // 注意：valid 属性有特殊的 getter 方法，暂时跳过自动测试
        yield 'remark' => ['remark', 'Test remark'];
    }
}
