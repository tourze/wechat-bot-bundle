<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 掉线二次登录请求
 * 对应社群助手API文档：掉线二次登录
 * URL: POST http://网关地址/open/loginAgain
 *
 * 注意：调用此接口后，手机微信上会弹出登录确认框，用户无需扫码，可以直接确认登录
 * 超过20天未登录的设备，系统会自动回收，回收后的设备需要重置设备
 * 如果3分钟后用户仍未成功登录，二次登录会自动失效，需重新获取二维码进行登录
 */
class ReLoginRequest extends ApiRequest implements WeChatRequestInterface
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
        return 'open/loginAgain';
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
            // 二次登录可能需要用户手动确认，设置较长的超时时间
            'timeout' => 180,
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
