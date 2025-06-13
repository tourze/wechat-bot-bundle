<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 移除群成员请求
 * 对应社群助手API文档：移除群成员
 * URL: POST http://网关地址/open/deleteChatRoomMember
 */
class RemoveGroupMemberRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $chatRoomId,
        private readonly string $userList
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

    public function getUserList(): string
    {
        return $this->userList;
    }

    public function getRequestPath(): string
    {
        return 'open/deleteChatRoomMember';
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
                'chatRoomId' => $this->chatRoomId,
                'userList' => $this->userList,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
