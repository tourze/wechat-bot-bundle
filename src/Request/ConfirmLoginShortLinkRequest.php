<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 确认登录请求（短链模式）
 * 对应社群助手API文档：第四步：确认登录（短链模式）
 * URL: POST http://网关地址/open/checkLoginShort
 *
 * 注意：此接口不管用户是否登录均会返回，需要调用方多次轮询确认登录
 * 推荐执行间隔1-3秒1次
 * 当返回对象内wcId为空，则表示用户未登录
 */
class ConfirmLoginShortLinkRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId
    ) {}

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
        return 'open/checkLoginShort';
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
            // 短链模式使用较短的超时时间
            'timeout' => 30,
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
