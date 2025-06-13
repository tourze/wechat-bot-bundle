<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 发送视频消息请求
 * 对应社群助手API文档：发送视频消息
 * URL: POST http://网关地址/open/sendVideo
 *
 * 注意：如果需要批量发送视频消息，建议借助额外的机器人先发送视频
 * 通过消息回调获取视频消息的xml后，使用转发视频消息接口进行发送
 * 此方法可大大加快视频消息的发送速度，并避免因为大量发送视频而引起的风控
 */
class SendVideoMessageRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId,
        private readonly string $videoPath,
        private readonly string $thumbPath
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

    public function getVideoPath(): string
    {
        return $this->videoPath;
    }

    public function getThumbPath(): string
    {
        return $this->thumbPath;
    }

    public function getRequestPath(): string
    {
        return 'open/sendVideo';
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
                'path' => $this->videoPath,
                'thumbPath' => $this->thumbPath,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
