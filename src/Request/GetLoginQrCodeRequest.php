<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 获取登录二维码请求
 * 对应社群助手API文档：第三步：获取登录二维码
 * URL: POST http://网关地址/open/getLoginQrCode
 * Header: Authorization: 授权密钥
 */
class GetLoginQrCodeRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly ?string $province = null,
        private readonly ?string $city = null,
        private readonly ?string $deviceType = null,
        private readonly ?string $proxyIp = null,
        private readonly ?string $proxyUser = null,
        private readonly ?string $proxyPassword = null,
    ) {
    }

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getRequestPath(): string
    {
        return 'open/getLoginQrCode';
    }

    public function getRequestOptions(): ?array
    {
        $params = [
            'deviceId' => $this->deviceId,
        ];

        // 添加可选参数
        if (null !== $this->province) {
            $params['province'] = $this->province;
        }
        if (null !== $this->city) {
            $params['city'] = $this->city;
        }
        if (null !== $this->deviceType) {
            $params['deviceType'] = $this->deviceType;
        }
        if (null !== $this->proxyIp) {
            $params['proxyIp'] = $this->proxyIp;
        }
        if (null !== $this->proxyUser) {
            $params['proxyUser'] = $this->proxyUser;
        }
        if (null !== $this->proxyPassword) {
            $params['proxyPassword'] = $this->proxyPassword;
        }

        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => http_build_query($params),
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
