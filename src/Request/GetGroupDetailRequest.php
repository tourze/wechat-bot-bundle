<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 获取群详细信息请求
 * 对应社群助手API文档：获取群详细信息
 * URL: GET http://网关地址/open/getChatRoomInfo
 */
class GetGroupDetailRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $chatRoomId
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getChatRoomId(): string
    {
        return $this->chatRoomId;
    }

    public function getRequestPath(): string
    {
        return 'open/getChatRoomInfo';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
            ],
            'query' => [
                'deviceId' => $this->deviceId,
                'chatRoomId' => $this->chatRoomId,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }
}
