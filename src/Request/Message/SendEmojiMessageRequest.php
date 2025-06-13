<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Message;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 发送Emoji消息请求
 *
 * 发送微信Emoji表情消息：
 * - 内置Emoji表情
 * - 自定义表情包
 * - 动态表情
 *
 * 接口文档: 社群助手API/消息发送API/发送Emoji消息.md
 *
 * @author AI Assistant
 */
class SendEmojiMessageRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId,
        private readonly string $emojiMd5
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

    public function getEmojiMd5(): string
    {
        return $this->emojiMd5;
    }

    public function getRequestPath(): string
    {
        return 'open/sendEmoji';
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
                'emojiMd5' => $this->emojiMd5,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
