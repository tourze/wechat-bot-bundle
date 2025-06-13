<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Message;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 发送小程序消息请求
 *
 * 发送微信小程序卡片消息：
 * - 小程序卡片分享
 * - 自定义小程序页面
 * - 小程序参数传递
 *
 * 接口文档: 社群助手API/消息发送API/发送小程序消息.md
 *
 * @author AI Assistant
 */
class SendMiniProgramMessageRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId,
        private readonly string $appId,
        private readonly string $title,
        private readonly string $content,
        private readonly string $pagePath,
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

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getPagePath(): string
    {
        return $this->pagePath;
    }

    public function getThumbUrl(): ?string
    {
        return $this->thumbUrl;
    }

    public function getRequestPath(): string
    {
        return 'open/sendMiniProgram';
    }

    public function getRequestOptions(): ?array
    {
        $data = [
            'deviceId' => $this->deviceId,
            'wxId' => $this->wxId,
            'appId' => $this->appId,
            'title' => $this->title,
            'content' => $this->content,
            'pagePath' => $this->pagePath,
        ];

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
