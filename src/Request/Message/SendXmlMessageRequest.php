<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Message;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 发送XML消息请求
 *
 * 发送特殊的XML格式消息：
 * - 聊天记录分享
 * - 位置信息分享
 * - 其他结构化消息
 *
 * 接口文档: 社群助手API/消息发送API/发送 xml 消息（聊天记录，定位）.md
 *
 * @author AI Assistant
 */
class SendXmlMessageRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId,
        private readonly string $xml,
    ) {
    }

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

    public function getXml(): string
    {
        return $this->xml;
    }

    public function getRequestPath(): string
    {
        return 'open/sendXml';
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
                'xml' => $this->xml,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
