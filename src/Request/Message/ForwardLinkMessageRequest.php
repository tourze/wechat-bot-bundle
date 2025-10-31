<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Message;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 转发已收到的链接消息请求
 *
 * 转发之前收到的链接消息到其他联系人：
 * - 转发链接卡片
 * - 保持原有链接信息
 * - 提高发送效率
 *
 * 接口文档: 社群助手API/消息发送API/发送已经收到的链接消息.md
 *
 * @author AI Assistant
 */
class ForwardLinkMessageRequest extends ApiRequest implements WeChatRequestInterface
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
        return 'open/forwardUrlMsg';
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
