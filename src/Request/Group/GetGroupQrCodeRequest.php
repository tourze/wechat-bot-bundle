<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Group;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 获取群二维码请求
 *
 * 获取指定微信群的二维码：
 * - 群二维码图片链接
 * - 用于他人扫码入群
 * - 支持二维码有效期设置
 *
 * 接口文档: 社群助手API/群操作相关API/获取群二维码.md
 *
 * @author AI Assistant
 */
class GetGroupQrCodeRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $chatRoomId,
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

    public function getChatRoomId(): string
    {
        return $this->chatRoomId;
    }

    public function getRequestPath(): string
    {
        return 'open/getChatRoomQrCode';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
            ],
            'query' => [
                'deviceId' => $this->deviceId,
                'chatRoomId' => $this->chatRoomId,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }
}
