<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Client;

use HttpClientBundle\Request\RequestInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Exception\ApiException;
use Tourze\WechatBotBundle\Exception\InvalidResponseException;
use Tourze\WechatBotBundle\Request\CheckOnlineStatusRequest;

/**
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(WeChatApiClient::class)]
final class WeChatApiClientTest extends AbstractIntegrationTestCase
{
    private WeChatApiClient $client;

    private WeChatApiAccount $apiAccount;

    private CheckOnlineStatusRequest $wechatRequest;

    protected function onSetUp(): void
    {
        // 通过服务容器获取服务实例
        $this->client = self::getService(WeChatApiClient::class);

        // 使用匿名类替代createMock，避免PHPStan类型问题
        // 该实体类包含复杂的业务逻辑状态管理，需要测试状态变化
        $this->apiAccount = new class extends WeChatApiAccount {
            private bool $connectCalled = false;

            private bool $errorCalled = false;

            private int $apiCallCount = 0;

            private string $baseUrl = 'https://api.example.com';

            private bool $shouldReturnEmpty = false;

            private bool $shouldThrowException = false;

            public function getBaseUrl(): string
            {
                if ($this->shouldThrowException) {
                    throw new \Exception('Test exception');
                }

                return $this->shouldReturnEmpty ? '' : $this->baseUrl;
            }

            public function markAsConnected(): void
            {
                $this->connectCalled = true;
            }

            public function markAsError(): void
            {
                $this->errorCalled = true;
            }

            public function incrementApiCallCount(): void
            {
                ++$this->apiCallCount;
            }

            public function wasConnectCalled(): bool
            {
                return $this->connectCalled;
            }

            public function wasErrorCalled(): bool
            {
                return $this->errorCalled;
            }

            public function getApiCallCount(): int
            {
                return $this->apiCallCount;
            }

            public function setShouldReturnEmptyUrl(bool $empty): void
            {
                $this->shouldReturnEmpty = $empty;
            }

            public function setShouldThrowException(bool $throw): void
            {
                $this->shouldThrowException = $throw;
            }
        };
        $this->wechatRequest = new CheckOnlineStatusRequest($this->apiAccount, 'test-device');
    }

    public function testCheckWeChatConnectionWithValidAccount(): void
    {
        $result = $this->client->checkWeChatConnection($this->wechatRequest);

        $this->assertTrue($result);
        /** @phpstan-ignore method.notFound */
        $this->assertTrue($this->apiAccount->wasConnectCalled());
    }

    public function testCheckWeChatConnectionWithEmptyBaseUrl(): void
    {
        /** @phpstan-ignore method.notFound */
        $this->apiAccount->setShouldReturnEmptyUrl(true);

        $result = $this->client->checkWeChatConnection($this->wechatRequest);

        $this->assertFalse($result);
    }

    public function testCheckWeChatConnectionWithException(): void
    {
        /** @phpstan-ignore method.notFound */
        $this->apiAccount->setShouldThrowException(true);

        $result = $this->client->checkWeChatConnection($this->wechatRequest);

        $this->assertFalse($result);
        /** @phpstan-ignore method.notFound */
        $this->assertTrue($this->apiAccount->wasErrorCalled());
    }

    public function testFormatResponseWithValidJson(): void
    {
        $content = '{"code": "1000", "message": "success", "data": {"id": 123}}';
        $response = new class($content) implements ResponseInterface {
            public function __construct(private string $content)
            {
            }

            public function getContent(bool $throw = true): string
            {
                return $this->content;
            }

            // 其他必要的方法实现（留空）
            public function getStatusCode(): int
            {
                return 200;
            }

            public function getHeaders(bool $throw = true): array
            {
                return [];
            }

            public function getInfo(?string $type = null): mixed
            {
                return null;
            }

            public function cancel(): void
            {
            }

            /** @return array<string, mixed> */
            public function toArray(bool $throw = true): array
            {
                return [];
            }

            public function toStream(bool $throw = true): mixed
            {
                return null;
            }
        };

        // API call count will be incremented by the method

        // 使用反射来测试受保护的方法
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('formatResponse');
        $method->setAccessible(true);

        $result = $method->invoke($this->client, $this->wechatRequest, $response);

        $expected = [
            'code' => '1000',
            'message' => 'success',
            'data' => ['id' => 123],
        ];

        $this->assertSame($expected, $result);
    }

    public function testFormatResponseWithInvalidJson(): void
    {
        $content = 'invalid json';
        $response = new class($content) implements ResponseInterface {
            public function __construct(private string $content)
            {
            }

            public function getContent(bool $throw = true): string
            {
                return $this->content;
            }

            // 其他必要的方法实现（留空）
            public function getStatusCode(): int
            {
                return 200;
            }

            public function getHeaders(bool $throw = true): array
            {
                return [];
            }

            public function getInfo(?string $type = null): mixed
            {
                return null;
            }

            public function cancel(): void
            {
            }

            /** @return array<string, mixed> */
            public function toArray(bool $throw = true): array
            {
                return [];
            }

            public function toStream(bool $throw = true): mixed
            {
                return null;
            }
        };

        $this->expectException(InvalidResponseException::class);
        $this->expectExceptionMessage('响应不是有效的JSON格式');

        // 使用反射来测试受保护的方法
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('formatResponse');
        $method->setAccessible(true);

        $method->invoke($this->client, $this->wechatRequest, $response);
    }

    public function testFormatResponseWithApiError(): void
    {
        $content = '{"code": "1001", "message": "API错误"}';
        $response = new class($content) implements ResponseInterface {
            public function __construct(private string $content)
            {
            }

            public function getContent(bool $throw = true): string
            {
                return $this->content;
            }

            // 其他必要的方法实现（留空）
            public function getStatusCode(): int
            {
                return 200;
            }

            public function getHeaders(bool $throw = true): array
            {
                return [];
            }

            public function getInfo(?string $type = null): mixed
            {
                return null;
            }

            public function cancel(): void
            {
            }

            /** @return array<string, mixed> */
            public function toArray(bool $throw = true): array
            {
                return [];
            }

            public function toStream(bool $throw = true): mixed
            {
                return null;
            }
        };

        // API call count will be incremented by the method

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('API错误 [1001]: API错误');

        // 使用反射来测试受保护的方法
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('formatResponse');
        $method->setAccessible(true);

        $method->invoke($this->client, $this->wechatRequest, $response);
    }

    public function testGetRequestUrlWithWeChatRequest(): void
    {
        // apiAccount already returns 'https://api.example.com' by default

        // 使用反射来测试受保护的方法
        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('getRequestUrl');
        $method->setAccessible(true);

        $result = $method->invoke($this->client, $this->wechatRequest);

        $this->assertSame('https://api.example.com/open/isOnline', $result);
    }

    public function testGetRequestUrlWithEmptyBaseUrl(): void
    {
        /** @phpstan-ignore method.notFound */
        $this->apiAccount->setShouldReturnEmptyUrl(true);

        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('getRequestUrl');
        $method->setAccessible(true);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('微信API基础URL未设置');

        $method->invoke($this->client, $this->wechatRequest);
    }

    public function testGetRequestUrlWithNonWeChatRequest(): void
    {
        /** @var MockObject&RequestInterface $request */
        $request = $this->createMock(RequestInterface::class);
        $request->method('getRequestPath')->willReturn('/test/path');

        $reflection = new \ReflectionClass($this->client);
        $method = $reflection->getMethod('getRequestUrl');
        $method->setAccessible(true);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('无法确定API基础URL');

        $method->invoke($this->client, $request);
    }
}
