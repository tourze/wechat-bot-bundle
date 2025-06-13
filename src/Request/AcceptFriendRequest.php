<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 同意好友添加请求
 * 对应社群助手API文档：同意好友添加
 * URL: POST http://网关地址/open/acceptUser
 *
 * 添加来源type值：
 * 1：QQ号搜索
 * 3：微信号搜索
 * 4：QQ好友
 * 8：通过群聊
 * 15：通过手机号
 *
 * 注意：type字段请保持与好友请求数据内的type或者scene一致
 */
class AcceptFriendRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $v1,
        private readonly string $v2,
        private readonly int $type
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getV1(): string
    {
        return $this->v1;
    }

    public function getV2(): string
    {
        return $this->v2;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getRequestPath(): string
    {
        return 'open/acceptUser';
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
                'v1' => $this->v1,
                'v2' => $this->v2,
                'type' => $this->type,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
