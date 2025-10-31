<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Upload;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 上传图片到CDN请求
 *
 * 将图片上传到微信CDN：
 * - 图片CDN存储
 * - 获取CDN地址
 * - 优化传输速度
 *
 * 接口文档: 社群助手API/上传API/上传图片（CDN）.md
 *
 * @author AI Assistant
 */
class UploadImageToCdnRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $imagePath,
        private readonly string $fileName = '',
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

    public function getImagePath(): string
    {
        return $this->imagePath;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getRequestPath(): string
    {
        return 'open/uploadImageToCdn';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
            ],
            'multipart' => [
                [
                    'name' => 'deviceId',
                    'contents' => $this->deviceId,
                ],
                [
                    'name' => 'image',
                    'contents' => fopen($this->imagePath, 'r'),
                    'filename' => '' !== $this->fileName ? $this->fileName : basename($this->imagePath),
                ],
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
