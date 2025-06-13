<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Account;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 重置接口调用凭证请求
 *
 * 重置微信API平台的访问令牌：
 * - 生成新的API Token
 * - 使旧Token失效
 * - 安全更新凭证
 *
 * 接口文档: 社群助手API/账号相关/重置接口调用凭证.md
 *
 * @author AI Assistant
 */
class ResetApiTokenRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getRequestPath(): string
    {
        return 'open/user/resetApiToken';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
                'Content-Type' => 'application/json',
            ],
            'json' => [],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
