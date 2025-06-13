<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 发送图片消息请求
 * 对应社群助手API文档：发送图片消息
 * URL: POST http://网关地址/open/sendImage
 *
 * 注意：如果需要批量发送图片消息，建议借助额外的机器人先发送图片
 * 通过消息回调获取图片消息的xml后，使用转发图片消息接口进行发送
 * 此方法可大大加快图片消息的发送速度，并避免因为大量发送图片而引起的风控
 */
class SendImageMessageRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId,
        private readonly string $imageUrl
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

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function getRequestPath(): string
    {
        return 'open/sendImage';
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
                'content' => $this->imageUrl,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
