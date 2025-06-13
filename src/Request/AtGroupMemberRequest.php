<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 群聊@他人请求
 * 对应社群助手API文档：群聊@他人
 * URL: POST http://网关地址/open/sendText
 *
 * 注意：
 * - content必须带与at参数数量相匹配的@字符，否则不会有@效果
 * - @所有人时，传notify@all，content参数必须包含"@所有人"
 * - 使用notify@all时，请确认当前账号为群主或群管，否则发送失败
 */
class AtGroupMemberRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId,
        private readonly string $content,
        private readonly string $at
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getWxId(): string
    {
        return $this->wxId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getAt(): string
    {
        return $this->at;
    }

    public function getRequestPath(): string
    {
        return 'open/sendText';
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
                'wxId' => $this->wxId,
                'content' => $this->content,
                'at' => $this->at,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
