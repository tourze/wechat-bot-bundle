<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\File;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 下载CDN资源请求
 *
 * 下载微信CDN上的资源文件：
 * - 下载CDN文件
 * - 获取媒体资源
 * - 支持各种文件类型
 *
 * 接口文档: 社群助手API/下载API/下载 CDN 资源.md
 *
 * @author AI Assistant
 */
class DownloadCdnResourceRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $cdnUrl,
        private readonly string $aesKey = '',
        private readonly string $fileKey = ''
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getCdnUrl(): string
    {
        return $this->cdnUrl;
    }

    public function getAesKey(): string
    {
        return $this->aesKey;
    }

    public function getFileKey(): string
    {
        return $this->fileKey;
    }

    public function getRequestPath(): string
    {
        return 'open/downloadCdnResource';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
            ],
            'query' => [
                'deviceId' => $this->deviceId,
                'cdnUrl' => $this->cdnUrl,
                'aesKey' => $this->aesKey,
                'fileKey' => $this->fileKey,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }
}
