<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 发送文本消息请求
 * 对应社群助手API文档：发送文本消息
 * URL: POST http://网关地址/open/sendText
 *
 * 注意：发送消息一次只能指定一个对象
 * 1分钟建议30条左右，每个不同对象切换间隔2秒，不同对象发送间隔随机5秒左右
 */
class SendTextMessageRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId,
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

    public function getWxId(): string
    {
        return $this->wxId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getRequestPath(): string
    {
        return 'open/sendText';
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
                'content' => $this->content,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
