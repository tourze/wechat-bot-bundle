<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 下载文件请求
 * 对应社群助手API文档：下载文件
 * URL: POST http://网关地址/open/downloadFile
 *
 * 注意：
 * - 文件过大时可能会出现超时，建议设置较大的超时时间
 * - 文件下载成功后会返回文件的base64编码内容
 * - 超时时间建议设置为60秒以上
 */
class DownloadFileRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $msgId,
        private readonly string $newMsgId,
        private readonly string $fromUser
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getMsgId(): string
    {
        return $this->msgId;
    }

    public function getNewMsgId(): string
    {
        return $this->newMsgId;
    }

    public function getFromUser(): string
    {
        return $this->fromUser;
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
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'deviceId' => $this->deviceId,
                'msgId' => $this->msgId,
                'newMsgId' => $this->newMsgId,
                'fromUser' => $this->fromUser,
            ],
            'timeout' => 60, // 设置60秒超时
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
