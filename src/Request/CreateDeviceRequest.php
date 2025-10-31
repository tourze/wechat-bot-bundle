<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 创建微信设备请求
 * 对应社群助手API文档：第二步：创建微信设备
 * URL: POST http://网关地址/open/workstation
 * Header: Authorization: 授权密钥
 */
#[Autoconfigure(public: true)]
class CreateDeviceRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
    ) {
    }

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getRequestPath(): string
    {
        return 'open/workstation';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => http_build_query([
                'deviceId' => $this->deviceId,
            ]),
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
