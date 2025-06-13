<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Moment;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 下载朋友圈视频请求
 *
 * 下载朋友圈中的视频资源：
 * - 朋友圈视频下载
 * - 本地保存视频
 * - 视频资源获取
 *
 * 接口文档: 社群助手API/朋友圈API/下载朋友圈视频.md
 *
 * @author AI Assistant
 */
class DownloadMomentVideoRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $videoUrl,
        private readonly string $videoId = ''
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getVideoUrl(): string
    {
        return $this->videoUrl;
    }

    public function getVideoId(): string
    {
        return $this->videoId;
    }

    public function getRequestPath(): string
    {
        return 'open/downloadMomentVideo';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
            ],
            'query' => [
                'deviceId' => $this->deviceId,
                'videoUrl' => $this->videoUrl,
                'videoId' => $this->videoId,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }
}
