<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 获取群成员列表请求
 * 对应社群助手API文档：获取群成员列表
 * URL: GET http://网关地址/open/getChatRoomMembers
 */
class GetGroupMembersRequest extends ApiRequest implements WeChatRequestInterface
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
        return 'open/getChatRoomMembers';
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
