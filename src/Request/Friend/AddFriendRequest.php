<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Friend;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 添加好友请求
 *
 * 向指定用户发送好友申请：
 * - 支持多种添加方式
 * - 可设置验证消息
 * - 自动处理添加结果
 *
 * 接口文档: 社群助手API/好友操作API/添加好友.md
 *
 * @author AI Assistant
 */
class AddFriendRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId,
        private readonly string $message = '',
        private readonly string $addType = 'search'
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

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getAddType(): string
    {
        return $this->addType;
    }

    public function getRequestPath(): string
    {
        return 'open/addFriend';
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
                'message' => $this->message,
                'addType' => $this->addType,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
