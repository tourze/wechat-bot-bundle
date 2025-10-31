<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Download;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 下载文件请求
 *
 * 下载微信聊天中收到的文件，支持各种文件类型：
 * - 文档文件（PDF、Word、Excel等）
 * - 压缩文件（ZIP、RAR等）
 * - 其他类型文件
 *
 * 接口文档: 社群助手API/下载API/下载文件.md
 *
 * @author AI Assistant
 */
class DownloadFileRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $fileId,
        private readonly string $fromUser,
        private readonly string $msgId,
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

    public function getFileId(): string
    {
        return $this->fileId;
    }

    public function getFromUser(): string
    {
        return $this->fromUser;
    }

    public function getMsgId(): string
    {
        return $this->msgId;
    }

    public function getRequestPath(): string
    {
        return 'open/downloadFile';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => http_build_query([
                'deviceId' => $this->deviceId,
                'fileId' => $this->fileId,
                'fromUser' => $this->fromUser,
                'msgId' => $this->msgId,
            ]),
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
