<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 撤回消息请求
 * 对应社群助手API文档：撤回消息
 * URL: POST http://网关地址/open/recallMsg
 *
 * 注意：需要提供消息回调中的msgId、newMsgId和timestamp
 */
class RecallMessageRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId,
        private readonly string $msgId,
        private readonly string $newMsgId,
        private readonly string $createTime
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

    public function getMsgId(): string
    {
        return $this->msgId;
    }

    public function getNewMsgId(): string
    {
        return $this->newMsgId;
    }

    public function getCreateTime(): string
    {
        return $this->createTime;
    }

    public function getRequestPath(): string
    {
        return 'open/recallMsg';
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
                'msgId' => $this->msgId,
                'newMsgId' => $this->newMsgId,
                'createTime' => $this->createTime,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
