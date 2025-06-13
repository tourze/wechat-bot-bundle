<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Account;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 获取账户余额请求
 * 
 * 查询微信API平台账户的余额信息：
 * - 查询当前余额
 * - 查询消费记录
 * - 账户状态信息
 * 
 * 接口文档: 社群助手API/账号相关/获取账户余额.md
 * 
 * @author AI Assistant
 */
class GetAccountBalanceRequest extends ApiRequest implements WeChatRequestInterface
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
        return 'open/user/getBalance';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }
} 