<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 检查在线状态请求
 * 对应社群助手API文档：检查在线状态
 * URL: POST http://网关地址/open/isOnline
 *
 * 注意：用于检测账号是否在线，推荐执行间隔1分钟1次
 * code为1000时表示账号在线
 * code为1001时表示其它状态（未知异常、已离线）
 * 避免频繁请求，频率一分钟左右一次
 */
class CheckOnlineStatusRequest extends ApiRequest implements WeChatRequestInterface
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
        return 'open/isOnline';
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
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
