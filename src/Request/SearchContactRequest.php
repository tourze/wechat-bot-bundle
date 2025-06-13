<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 搜索联系人请求
 * 对应社群助手API文档：搜索联系人（QQ_手机_微信号）
 * URL: POST http://网关地址/open/searchUser
 *
 * 注意：支持搜索微信号/手机号，不支持wxid开头的
 * 如果搜索对象已经是好友，v2不会返回或返回null，v1返回搜索对象的wxid
 */
class SearchContactRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $searchTerm
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getSearchTerm(): string
    {
        return $this->searchTerm;
    }

    public function getRequestPath(): string
    {
        return 'open/searchUser';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'deviceId' => $this->deviceId,
                'wxId' => $this->searchTerm,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
