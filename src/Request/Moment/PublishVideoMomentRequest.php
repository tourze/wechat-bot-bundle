<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Moment;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 朋友圈发视频请求
 *
 * 发布视频内容到朋友圈：
 * - 视频动态发布
 * - 添加文字描述
 * - 视频封面设置
 *
 * 接口文档: 社群助手API/朋友圈API/朋友圈发视频.md
 *
 * @author AI Assistant
 */
class PublishVideoMomentRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $videoPath,
        private readonly string $content = '',
        private readonly string $thumbPath = '',
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

    public function getVideoPath(): string
    {
        return $this->videoPath;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getThumbPath(): string
    {
        return $this->thumbPath;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getRequestPath(): string
    {
        return 'open/publishVideoMoment';
    }

    public function getRequestOptions(): ?array
    {
        $multipart = [
            [
                'name' => 'deviceId',
                'contents' => $this->deviceId,
            ],
            [
                'name' => 'content',
                'contents' => $this->content,
            ],
            [
                'name' => 'visibility',
                'contents' => $this->visibility,
            ],
            [
                'name' => 'video',
                'contents' => fopen($this->videoPath, 'r'),
                'filename' => basename($this->videoPath),
            ],
        ];

        if ('' !== $this->thumbPath) {
            $multipart[] = [
                'name' => 'thumb',
                'contents' => fopen($this->thumbPath, 'r'),
                'filename' => basename($this->thumbPath),
            ];
        }

        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
            ],
            'multipart' => $multipart,
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
