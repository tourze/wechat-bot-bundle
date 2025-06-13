<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 删除好友请求
 * 对应社群助手API文档：删除好友
 * URL: POST http://网关地址/open/delContact
 */
class DeleteFriendRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getWxId(): string
    {
        return $this->wxId;
    }

    public function getRequestPath(): string
    {
        return 'open/delContact';
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
                'wxId' => $this->wxId,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
