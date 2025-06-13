<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 获取联系人信息请求
 * 对应社群助手API文档：获取联系人信息
 * URL: POST http://网关地址/open/getContact
 *
 * 注意：支持多个好友/群，以","分隔，每次最多支持20个微信/群号
 * 频繁调用容易导致掉线，建议随机间隔1-3秒
 */
class GetContactInfoRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxIds
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getWxIds(): string
    {
        return $this->wxIds;
    }

    public function getRequestPath(): string
    {
        return 'open/getContact';
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
                'wxId' => $this->wxIds,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
