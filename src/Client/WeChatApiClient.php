<?php

namespace Tourze\WechatBotBundle\Client;

use HttpClientBundle\Client\ApiClient;
use HttpClientBundle\Request\RequestInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 微信机器人API客户端
 * 基于http-client-bundle，专门处理微信API的特殊逻辑
 */
class WeChatApiClient extends ApiClient
{
    /**
     * 检查微信API连接状态
     */
    public function checkWeChatConnection(WeChatRequestInterface $request): bool
    {
        try {
            $apiAccount = $request->getApiAccount();
            $baseUrl = $apiAccount->getBaseUrl();

            if (empty($baseUrl)) {
                return false;
            }

            // 更新连接状态
            $apiAccount->markAsConnected();
            return true;
        } catch (\Exception $e) {
            if ($request instanceof WeChatRequestInterface) {
                $request->getApiAccount()->markAsError();
            }
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
            if (empty($baseUrl)) {
                throw new \RuntimeException('微信API基础URL未设置');
            }
            return "{$baseUrl}/{$path}";
        }

        throw new \RuntimeException('无法确定API基础URL');
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

        if (isset($options['headers'])) {
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
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('响应不是有效的JSON格式: ' . json_last_error_msg());
        }

        // 如果是微信请求，更新API调用统计
        if ($request instanceof WeChatRequestInterface) {
            $apiAccount = $request->getApiAccount();
            $apiAccount->incrementApiCallCount();
        }

        // 检查API响应状态 - 微信API成功码是 "1000" (字符串)
        if (isset($data['code']) && $data['code'] !== '1000' && $data['code'] !== 1000) {
            $message = $data['message'] ?? $data['msg'] ?? '未知错误';

            // 如果是认证错误且是微信请求，标记API账号状态
            if ($request instanceof WeChatRequestInterface) {
                $errorCodes = ['401', '403', 'AUTH_FAILED', 401, 403];
                if (in_array($data['code'], $errorCodes)) {
                    $request->getApiAccount()->markAsError();
                }
            }

            throw new \RuntimeException("API错误 [{$data['code']}]: {$message}");
        }

        // 返回完整的响应数据，让业务层决定如何处理
        return $data;
    }
}
