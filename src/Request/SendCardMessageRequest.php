<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 发送名片消息请求
 * 对应社群助手API文档：发送名片消息
 * URL: POST http://网关地址/open/sendCard
 *
 * 注意：名片微信号必须是好友关系
 */
class SendCardMessageRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId,
        private readonly string $cardWxId
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

    public function getCardWxId(): string
    {
        return $this->cardWxId;
    }

    public function getRequestPath(): string
    {
        return 'open/sendCard';
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
                'cardWxId' => $this->cardWxId,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
