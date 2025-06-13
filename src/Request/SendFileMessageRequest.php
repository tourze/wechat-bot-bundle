<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 发送文件消息请求
 * 对应社群助手API文档：发送文件消息
 * URL: POST http://网关地址/open/sendFile
 *
 * 注意：如果需要批量发送文件消息，建议借助额外的机器人先发送文件
 * 通过消息回调获取文件消息的xml后，使用转发文件消息接口进行发送
 * 此方法可大大加快文件消息的发送速度，并避免因为大量发送文件而引起的风控
 */
class SendFileMessageRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId,
        private readonly string $filePath,
        private readonly ?string $fileName = null
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

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function getRequestPath(): string
    {
        return 'open/sendFile';
    }

    public function getRequestOptions(): ?array
    {
        $data = [
            'deviceId' => $this->deviceId,
            'wxId' => $this->wxId,
            'path' => $this->filePath,
        ];

        if ($this->fileName !== null) {
            $data['fileName'] = $this->fileName;
        }

        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
