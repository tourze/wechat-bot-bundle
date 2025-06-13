<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Moment;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 朋友圈发图片请求
 *
 * 发布图片内容到朋友圈：
 * - 单图或多图发布
 * - 添加文字描述
 * - 使用已上传的图片资源
 *
 * 接口文档: 社群助手API/朋友圈API/朋友圈发图片.md
 *
 * @author AI Assistant
 */
class PublishImageMomentRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly array $imageIds,
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

    public function getImageIds(): array
    {
        return $this->imageIds;
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
        return 'open/publishImageMoment';
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
                'imageIds' => $this->imageIds,
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
