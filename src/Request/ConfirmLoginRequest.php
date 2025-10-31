<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 确认登录请求
 * 对应社群助手API文档：第四步：确认登录
 * URL: POST http://网关地址/open/checkLogin
 *
 * 注意：这是一个长连接接口，需要设置调用超时时间大于215秒
 * 若215秒后返回未登录，则登录二维码失效，需要重新获取二维码
 */
#[Autoconfigure(public: true)]
class ConfirmLoginRequest extends ApiRequest implements WeChatRequestInterface
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

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getRequestPath(): string
    {
        return 'open/checkLogin';
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
            ],
            // 长连接需要设置超时时间大于215秒
            'timeout' => 240,
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
