<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 设置设备网络代理请求
 * 对应社群助手API文档：设置设备网络代理
 * URL: POST http://网关地址/open/setDeviceProxy
 *
 * 注意：为已登录的设备设置网络代理（仅支持socks5代理）
 * 设置同城代理可解决80%的风控、掉线问题
 * 登录必须传递同省/同城代理，否则微信会秒掉
 */
class SetDeviceProxyRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly ?string $proxyIp = null,
        private readonly ?string $proxyUser = null,
        private readonly ?string $proxyPassword = null
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getProxyIp(): ?string
    {
        return $this->proxyIp;
    }

    public function getProxyUser(): ?string
    {
        return $this->proxyUser;
    }

    public function getProxyPassword(): ?string
    {
        return $this->proxyPassword;
    }

    public function getRequestPath(): string
    {
        return 'open/setDeviceProxy';
    }

    public function getRequestOptions(): ?array
    {
        $data = [
            'deviceId' => $this->deviceId,
        ];

        // 只有提供了代理IP才添加代理相关参数
        if ($this->proxyIp !== null) {
            $data['proxyIp'] = $this->proxyIp;
        }

        if ($this->proxyUser !== null) {
            $data['proxyUser'] = $this->proxyUser;
        }

        if ($this->proxyPassword !== null) {
            $data['proxyPassword'] = $this->proxyPassword;
        }

        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
