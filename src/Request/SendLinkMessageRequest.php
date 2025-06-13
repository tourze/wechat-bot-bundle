<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 发送链接消息请求
 * 对应社群助手API文档：发送链接消息
 * URL: POST http://网关地址/open/sendUrl
 */
class SendLinkMessageRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId,
        private readonly string $title,
        private readonly string $url,
        private readonly ?string $description = null,
        private readonly ?string $thumbUrl = null
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getThumbUrl(): ?string
    {
        return $this->thumbUrl;
    }

    public function getRequestPath(): string
    {
        return 'open/sendUrl';
    }

    public function getRequestOptions(): ?array
    {
        $data = [
            'deviceId' => $this->deviceId,
            'wxId' => $this->wxId,
            'title' => $this->title,
            'url' => $this->url,
        ];

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        if ($this->thumbUrl !== null) {
            $data['thumbUrl'] = $this->thumbUrl;
        }

        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
