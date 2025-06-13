<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Friend;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 搜索联系人请求
 *
 * 通过多种方式搜索微信联系人：
 * - 微信号搜索
 * - 手机号搜索
 * - QQ号搜索
 *
 * 接口文档: 社群助手API/好友操作API/搜索联系人（QQ_手机_微信号）.md
 *
 * @author AI Assistant
 */
class SearchContactRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $keyword,
        private readonly string $searchType = 'wechat'
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getKeyword(): string
    {
        return $this->keyword;
    }

    public function getSearchType(): string
    {
        return $this->searchType;
    }

    public function getRequestPath(): string
    {
        return 'open/searchContact';
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
                'keyword' => $this->keyword,
                'searchType' => $this->searchType,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
