<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Moment;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 上传图片请求
 *
 * 上传图片资源用于朋友圈发布：
 * - 图片上传到服务器
 * - 获取图片资源ID
 * - 支持多种格式
 *
 * 接口文档: 社群助手API/朋友圈API/上传图片.md
 *
 * @author AI Assistant
 */
class UploadMomentImageRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $imageBase64,
        private readonly string $imageType = 'jpg'
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getImageBase64(): string
    {
        return $this->imageBase64;
    }

    public function getImageType(): string
    {
        return $this->imageType;
    }

    public function getRequestPath(): string
    {
        return 'open/uploadMomentImage';
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
                'imageBase64' => $this->imageBase64,
                'imageType' => $this->imageType,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
