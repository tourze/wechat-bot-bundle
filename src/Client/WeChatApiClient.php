<?php

namespace Tourze\WechatBotBundle\Client;

use HttpClientBundle\Client\ApiClient;
use HttpClientBundle\Request\RequestInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Tourze\DoctrineAsyncInsertBundle\Service\AsyncInsertService;
use Tourze\WechatBotBundle\Exception\ApiException;
use Tourze\WechatBotBundle\Exception\InvalidResponseException;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 微信机器人API客户端
 * 基于http-client-bundle，专门处理微信API的特殊逻辑
 */
#[WithMonologChannel(channel: 'wechat_bot')]
#[Autoconfigure(public: true)]
class WeChatApiClient extends ApiClient implements ServiceSubscriberInterface
{
    /**
     * @return array<string, mixed>
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $httpClient,
        private readonly LockFactory $lockFactory,
        private readonly CacheInterface $cache,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AsyncInsertService $asyncInsertService,
    ) {
    }

    /**
     * @return array<string|SubscribedService>
     */
    public static function getSubscribedServices(): array
    {
        return [];
    }

    protected function getLockFactory(): LockFactory
    {
        return $this->lockFactory;
    }

    protected function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    protected function getCache(): CacheInterface
    {
        return $this->cache;
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    protected function getAsyncInsertService(): AsyncInsertService
    {
        return $this->asyncInsertService;
    }

    /**
     * 检查微信API连接状态
     */
    public function checkWeChatConnection(WeChatRequestInterface $request): bool
    {
        try {
            $apiAccount = $request->getApiAccount();
            $baseUrl = $apiAccount->getBaseUrl();

            if (null === $baseUrl || '' === $baseUrl) {
                return false;
            }

            // 更新连接状态
            $apiAccount->markAsConnected();

            return true;
        } catch (\Exception $e) {
            $request->getApiAccount()->markAsError();

            return false;
        }
    }

    protected function getRequestUrl(RequestInterface $request): string
    {
        $path = ltrim($request->getRequestPath(), '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // 如果是微信请求，从Request中获取baseUrl
        if ($request instanceof WeChatRequestInterface) {
            $baseUrl = $request->getApiAccount()->getBaseUrl();
            if (null === $baseUrl || '' === $baseUrl) {
                throw new ApiException('微信API基础URL未设置');
            }

            return "{$baseUrl}/{$path}";
        }

        throw new ApiException('无法确定API基础URL');
    }

    protected function getRequestMethod(RequestInterface $request): string
    {
        return $request->getRequestMethod() ?? 'POST';
    }

    protected function getRequestOptions(RequestInterface $request): ?array
    {
        $options = $request->getRequestOptions() ?? [];

        // 合并默认请求头，但不覆盖Request中已设置的headers
        $defaultHeaders = [
            'User-Agent' => 'WeChatBot/1.0',
        ];

        if (isset($options['headers']) && is_array($options['headers'])) {
            $options['headers'] = array_merge($defaultHeaders, $options['headers']);
        } else {
            $options['headers'] = $defaultHeaders;
        }

        // 如果是微信请求，获取timeout配置
        if ($request instanceof WeChatRequestInterface) {
            $timeout = $request->getApiAccount()->getTimeout();
            if (!isset($options['timeout'])) {
                $options['timeout'] = $timeout;
            }
        }

        return $options;
    }

    protected function formatResponse(RequestInterface $request, ResponseInterface $response): mixed
    {
        $content = $response->getContent();
        $data = $this->parseJsonResponse($content);

        // 如果是微信请求，更新API调用统计
        if ($request instanceof WeChatRequestInterface) {
            $apiAccount = $request->getApiAccount();
            $apiAccount->incrementApiCallCount();
        }

        // 检查API响应状态
        $this->checkApiResponseStatus($data, $request);

        // 返回完整的响应数据，让业务层决定如何处理
        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    private function parseJsonResponse(string $content): array
    {
        $data = json_decode($content, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidResponseException('响应不是有效的JSON格式: ' . json_last_error_msg());
        }

        if (!is_array($data)) {
            throw new InvalidResponseException('响应JSON必须是数组格式');
        }

        /** @var array<string, mixed> $data */
        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function checkApiResponseStatus(array $data, RequestInterface $request): void
    {
        // 检查API响应状态 - 微信API成功码是 "1000" (字符串)
        if (!isset($data['code']) || ('1000' === $data['code'] || 1000 === $data['code'])) {
            return;
        }

        $message = is_string($data['message'] ?? null) ? $data['message'] : (is_string($data['msg'] ?? null) ? $data['msg'] : '未知错误');
        $code = is_string($data['code']) || is_int($data['code']) ? (string) $data['code'] : 'UNKNOWN';

        // 如果是认证错误且是微信请求，标记API账号状态
        if ($request instanceof WeChatRequestInterface) {
            $this->handleAuthenticationError($data['code'], $request);
        }

        throw new ApiException("API错误 [{$code}]: {$message}");
    }

    private function handleAuthenticationError(mixed $code, WeChatRequestInterface $request): void
    {
        $errorCodes = ['401', '403', 'AUTH_FAILED', 401, 403];
        if (in_array($code, $errorCodes, true)) {
            $apiAccount = $request->getApiAccount();
            $apiAccount->markAsError();
        }
    }
}
