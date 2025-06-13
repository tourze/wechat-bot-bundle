<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 通过入群邀请请求
 * 对应社群助手API文档：通过入群邀请
 * URL: POST http://网关地址/open/acceptChatRoomInvite
 */
class AcceptGroupInviteRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $encryptUsername,
        private readonly string $ticket
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getEncryptUsername(): string
    {
        return $this->encryptUsername;
    }

    public function getTicket(): string
    {
        return $this->ticket;
    }

    public function getRequestPath(): string
    {
        return 'open/acceptChatRoomInvite';
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
                'encryptUsername' => $this->encryptUsername,
                'ticket' => $this->ticket,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
