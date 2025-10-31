<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Exception\InvalidArgumentException;
use Tourze\WechatBotBundle\Request\SetDeviceProxyRequest;
use Tourze\WechatBotBundle\Service\WeChatDeviceProxyManager;

/**
 * @internal
 */
#[CoversClass(WeChatDeviceProxyManager::class)]
#[RunTestsInSeparateProcesses]
final class WeChatDeviceProxyManagerTest extends AbstractIntegrationTestCase
{
    private WeChatApiClient&MockObject $apiClient;

    private LoggerInterface&MockObject $logger;

    private WeChatDeviceProxyManager $proxyManager;

    protected function onSetUp(): void
    {
        // Mock 外部依赖（因为我们不想在测试中调用真实的微信 API 和日志）
        $this->apiClient = $this->createMock(WeChatApiClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        // 将 Mock 对象注册到服务容器中
        self::getContainer()->set(WeChatApiClient::class, $this->apiClient);
        self::getContainer()->set('monolog.logger.wechat_bot', $this->logger);

        // 获取真实的服务实例
        $this->proxyManager = self::getService(WeChatDeviceProxyManager::class);
    }

    public function testApplyDeviceProxySuccess(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $deviceId = 'device123';
        $proxy = '192.168.1.1:8080';

        $this->apiClient->expects($this->once())
            ->method('request')
            ->with(self::callback(static fn($arg) => $arg instanceof SetDeviceProxyRequest));

        $this->logger->expects($this->once())
            ->method('info')
            ->with('Device proxy set', self::callback(static fn($arg) => is_array($arg) && array_key_exists('deviceId', $arg)));

        $result = $this->proxyManager->applyDeviceProxy($apiAccount, $deviceId, $proxy);

        $this->assertTrue($result);
    }

    public function testApplyDeviceProxyWithUsernamePassword(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $deviceId = 'device123';
        $proxy = '192.168.1.1:8080:user:pass';

        $this->apiClient->expects($this->once())
            ->method('request')
            ->with(self::callback(static fn($arg) => $arg instanceof SetDeviceProxyRequest));

        $result = $this->proxyManager->applyDeviceProxy($apiAccount, $deviceId, $proxy);

        $this->assertTrue($result);
    }

    public function testApplyDeviceProxyWithHttpScheme(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $deviceId = 'device123';
        $proxy = 'http://192.168.1.1:8080';

        $this->apiClient->expects($this->once())
            ->method('request')
            ->with(self::callback(static fn($arg) => $arg instanceof SetDeviceProxyRequest));

        $result = $this->proxyManager->applyDeviceProxy($apiAccount, $deviceId, $proxy);

        $this->assertTrue($result);
    }

    #[DataProvider('provideInvalidProxies')]
    public function testApplyDeviceProxyWithInvalidFormat(string $proxy): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $deviceId = 'device123';

        $this->apiClient->expects($this->never())
            ->method('request');

        $this->logger->expects($this->once())
            ->method('warning')
            ->with('Invalid proxy configuration', self::callback(static fn($arg) => is_array($arg) && array_key_exists('deviceId', $arg)));

        $result = $this->proxyManager->applyDeviceProxy($apiAccount, $deviceId, $proxy);

        $this->assertFalse($result);
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function provideInvalidProxies(): iterable
    {
        yield 'missing port' => ['192.168.1.1'];
        yield 'invalid port too low' => ['192.168.1.1:0'];
        yield 'invalid port too high' => ['192.168.1.1:65536'];
        yield 'empty host' => [':8080'];
        yield 'invalid host format' => ['invalid_host!:8080'];
    }

    public function testApplyDeviceProxyWithApiException(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $deviceId = 'device123';
        $proxy = '192.168.1.1:8080';

        $this->apiClient->expects($this->once())
            ->method('request')
            ->willThrowException(new \Exception('API error'));

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Failed to set device proxy', self::callback(static fn($arg) => is_array($arg) && array_key_exists('deviceId', $arg)));

        $result = $this->proxyManager->applyDeviceProxy($apiAccount, $deviceId, $proxy);

        $this->assertFalse($result);
    }

    public function testApplyDeviceProxyWithDomainName(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $deviceId = 'device123';
        $proxy = 'proxy.example.com:8080';

        $this->apiClient->expects($this->once())
            ->method('request')
            ->with(self::callback(static fn($arg) => $arg instanceof SetDeviceProxyRequest));

        $result = $this->proxyManager->applyDeviceProxy($apiAccount, $deviceId, $proxy);

        $this->assertTrue($result);
    }

    public function testApplyDeviceProxyWithIPv6(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $deviceId = 'device123';
        $proxy = '[2001:db8::1]:8080';

        $this->apiClient->expects($this->never())
            ->method('request');

        // IPv6 with brackets will fail validation in current implementation
        $result = $this->proxyManager->applyDeviceProxy($apiAccount, $deviceId, $proxy);

        $this->assertFalse($result);
    }
}
