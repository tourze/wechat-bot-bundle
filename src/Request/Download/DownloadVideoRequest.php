<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Download;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 下载视频请求
 *
 * 下载微信聊天中收到的视频文件，支持：
 * - 视频原文件下载
 * - 视频缩略图下载
 * - 多种视频格式（MP4、AVI等）
 *
 * 接口文档: 社群助手API/下载API/下载视频.md
 *
 * @author AI Assistant
 */
class DownloadVideoRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $bufId,
        private readonly string $msgId,
        private readonly string $fromUser,
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

    public function getBufId(): string
    {
        return $this->bufId;
    }

    public function getMsgId(): string
    {
        return $this->msgId;
    }

    public function getFromUser(): string
    {
        return $this->fromUser;
    }

    public function getRequestPath(): string
    {
        return 'open/downloadVideo';
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
                'bufId' => $this->bufId,
                'msgId' => $this->msgId,
                'fromUser' => $this->fromUser,
            ]),
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
