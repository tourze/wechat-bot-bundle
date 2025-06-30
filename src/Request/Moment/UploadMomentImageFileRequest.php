<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Moment;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 上传图片文件请求
 *
 * 上传图片文件用于朋友圈发布：
 * - 文件流上传
 * - 支持大图片
 * - 获取文件资源ID
 *
 * 接口文档: 社群助手API/朋友圈API/上传图片文件.md
 *
 * @author AI Assistant
 */
class UploadMomentImageFileRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $filePath,
        private readonly string $fileName = ''
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getRequestPath(): string
    {
        return 'open/uploadMomentImageFile';
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
                    'name' => 'file',
                    'contents' => fopen($this->filePath, 'r'),
                    'filename' => $this->fileName !== '' ? $this->fileName : basename($this->filePath),
                ],
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
