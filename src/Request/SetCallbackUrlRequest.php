<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 设置消息接收地址请求
 * 对应社群助手API文档：设置消息接收地址
 * URL: POST http://网关地址/open/user/setHttpCallbackUrl
 *
 * 注意：更改消息接收地址会有一段时间的延迟，请注意保留原有地址的服务以实现平滑切换
 * HTTP请求默认最高6秒内建立链接并且发送数据，超过6秒通讯时长不发送回调消息
 */
class SetCallbackUrlRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $callbackUrl,
    ) {
    }

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getCallbackUrl(): string
    {
        return $this->callbackUrl;
    }

    public function getRequestPath(): string
    {
        return 'open/user/setHttpCallbackUrl';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'callbackUrl' => $this->callbackUrl,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
