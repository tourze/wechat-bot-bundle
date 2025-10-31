<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 登录API平台请求
 * 对应社群助手API文档：第一步：登录API平台
 * URL: POST http://网关地址/auth/login
 * Content-Type: application/x-www-form-urlencoded 或 form-data
 */
class LoginPlatformRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
    ) {
    }

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getRequestPath(): string
    {
        return 'auth/login';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => http_build_query([
                'username' => $this->apiAccount->getUsername(),
                'password' => $this->apiAccount->getPassword(),
            ]),
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
