<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 下载图片请求
 *
 * 根据消息ID和相关信息下载微信图片文件
 * 下载可能需要较长时间，请注意调整请求超时时间
 *
 * API文档: 社群助手API/下载API/下载图片.md
 *
 * @author AI Assistant
 */
class DownloadImageRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly int $msgId,
        private readonly string $fromUser,
        private readonly string $toUser,
        private readonly string $content,
        private readonly ?int $type = null
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getRequestPath(): string
    {
        return 'open/getMsgImg';
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }

    public function getRequestOptions(): ?array
    {
        $data = [
            'deviceId' => $this->deviceId,
            'msgId' => $this->msgId,
            'fromUser' => $this->fromUser,
            'toUser' => $this->toUser,
            'content' => $this->content,
        ];

        if ($this->type !== null) {
            $data['type'] = $this->type;
        }

        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
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
            'DownloadImageRequest(deviceId=%s, msgId=%d, fromUser=%s, type=%s)',
            $this->deviceId,
            $this->msgId,
            $this->fromUser,
            $this->type === null ? 'null' : (string)$this->type
        );
    }
}
