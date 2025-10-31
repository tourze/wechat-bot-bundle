<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Moment;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 朋友圈发文本请求
 *
 * 发布纯文本内容到朋友圈：
 * - 发布文字动态
 * - 支持表情符号
 * - 可见性设置
 *
 * 接口文档: 社群助手API/朋友圈API/朋友圈发文本.md
 *
 * @author AI Assistant
 */
class PublishTextMomentRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $content,
        private readonly string $visibility = 'public',
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
        return 'open/publishTextMoment';
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
