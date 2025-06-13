<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 修改群名请求
 * 对应社群助手API文档：修改群名
 * URL: POST http://网关地址/open/modifyGroupName
 *
 * 注意：当群聊人数超过100人时，需要群主或管理员才能修改群名
 */
class UpdateGroupNameRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $chatRoomId,
        private readonly string $content
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

    public function getContent(): string
    {
        return $this->content;
    }

    public function getRequestPath(): string
    {
        return 'open/modifyGroupName';
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
                'content' => $this->content,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
