<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 发送语音消息请求
 * 对应社群助手API文档：发送语音消息
 * URL: POST http://网关地址/open/sendVoice
 *
 * 注意：语音必须是silk格式，其他音频格式（如mp3）需要转换为silk格式
 * 可参考 https://github.com/kn007/silk-v3-decoder/ 进行格式转换
 */
class SendVoiceMessageRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId,
        private readonly string $voiceUrl,
        private readonly string $length
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

    public function getVoiceUrl(): string
    {
        return $this->voiceUrl;
    }

    public function getLength(): string
    {
        return $this->length;
    }

    public function getRequestPath(): string
    {
        return 'open/sendVoice';
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
                'content' => $this->voiceUrl,
                'length' => $this->length,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
