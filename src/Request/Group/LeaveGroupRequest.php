<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Group;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 退出群聊天请求
 *
 * 主动退出指定的微信群聊：
 * - 退出普通群聊
 * - 群主需要先转让群主再退出
 * - 退出后无法接收群消息
 *
 * 接口文档: 社群助手API/群操作相关API/退出群聊天.md
 *
 * @author AI Assistant
 */
class LeaveGroupRequest extends ApiRequest implements WeChatRequestInterface
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
        return 'open/quitChatRoom';
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
                'chatRoomId' => $this->chatRoomId,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
