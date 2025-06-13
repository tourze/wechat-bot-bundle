<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 创建微信群请求
 * 对应社群助手API文档：创建微信群
 * URL: POST http://网关地址/open/createChatroom
 *
 * 注意：必须传输2个微信号以上才可创建群聊
 */
class CreateGroupRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $topic,
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

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getUserList(): string
    {
        return $this->userList;
    }

    public function getRequestPath(): string
    {
        return 'open/createChatroom';
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
                'topic' => $this->topic,
                'userList' => $this->userList,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
