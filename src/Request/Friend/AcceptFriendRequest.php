<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Friend;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 同意好友添加请求
 *
 * 同意其他用户的好友申请：
 * - 处理好友申请通知
 * - 自动同意或拒绝
 * - 建立好友关系
 *
 * 接口文档: 社群助手API/好友操作API/同意好友添加.md
 *
 * @author AI Assistant
 */
class AcceptFriendRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId,
        private readonly bool $accept = true,
        private readonly string $message = ''
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

    public function isAccept(): bool
    {
        return $this->accept;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getRequestPath(): string
    {
        return 'open/acceptFriend';
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
                'accept' => $this->accept,
                'message' => $this->message,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
