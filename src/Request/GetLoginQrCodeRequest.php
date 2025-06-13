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
        private readonly ?string $proxyPassword = null
    ) {}

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
        if ($this->province !== null) {
            $params['province'] = $this->province;
        }
        if ($this->city !== null) {
            $params['city'] = $this->city;
        }
        if ($this->deviceType !== null) {
            $params['deviceType'] = $this->deviceType;
        }
        if ($this->proxyIp !== null) {
            $params['proxyIp'] = $this->proxyIp;
        }
        if ($this->proxyUser !== null) {
            $params['proxyUser'] = $this->proxyUser;
        }
        if ($this->proxyPassword !== null) {
            $params['proxyPassword'] = $this->proxyPassword;
        }

        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
            ],
            'form_params' => $params,
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
