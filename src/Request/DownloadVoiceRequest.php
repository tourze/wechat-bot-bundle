<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 下载语音请求
 *
 * 根据消息ID和bufId下载微信语音文件
 * 下载可能需要较长时间，请注意调整请求超时时间
 *
 * API文档: 社群助手API/下载API/下载语音.md
 *
 * @author AI Assistant
 */
class DownloadVoiceRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly int $msgId,
        private readonly int $length,
        private readonly int $bufId,
        private readonly string $fromUser
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getRequestPath(): string
    {
        return 'open/getMsgVoice';
    }

    public function getRequestMethod(): string
    {
        return 'POST';
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
                'msgId' => $this->msgId,
                'length' => $this->length,
                'bufId' => $this->bufId,
                'fromUser' => $this->fromUser,
            ],
            'timeout' => 60, // 下载可能需要较长时间
        ];
    }

    /**
     * 获取下载链接
     */
    public function getDownloadUrl(array $response): ?string
    {
        return $response['data']['url'] ?? null;
    }

    public function __toString(): string
    {
        return sprintf(
            'DownloadVoiceRequest(deviceId=%s, msgId=%d, bufId=%d, length=%d)',
            $this->deviceId,
            $this->msgId,
            $this->bufId,
            $this->length
        );
    }
}
