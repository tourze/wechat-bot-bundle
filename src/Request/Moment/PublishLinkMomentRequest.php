<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Moment;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 朋友圈发链接请求
 *
 * 发布链接内容到朋友圈：
 * - 分享网页链接
 * - 自动获取链接预览
 * - 添加个人评论
 *
 * 接口文档: 社群助手API/朋友圈API/朋友圈发链接.md
 *
 * @author AI Assistant
 */
class PublishLinkMomentRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $url,
        private readonly string $title,
        private readonly string $description = '',
        private readonly string $content = '',
        private readonly string $visibility = 'public'
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getRequestPath(): string
    {
        return 'open/publishLinkMoment';
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
                'url' => $this->url,
                'title' => $this->title,
                'description' => $this->description,
                'content' => $this->content,
                'visibility' => $this->visibility,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
