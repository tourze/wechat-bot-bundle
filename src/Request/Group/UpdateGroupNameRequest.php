<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Group;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 修改群名请求
 *
 * 修改微信群的群名称，需要管理员权限：
 * - 支持修改群名称
 * - 需要群主或管理员权限
 * - 群名长度限制
 *
 * 接口文档: 社群助手API/群操作相关API/修改群名.md
 *
 * @author AI Assistant
 */
class UpdateGroupNameRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $chatRoomId,
        private readonly string $chatRoomName,
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

    public function getChatRoomName(): string
    {
        return $this->chatRoomName;
    }

    public function getRequestPath(): string
    {
        return 'open/modifyChatroomName';
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
                'chatRoomName' => $this->chatRoomName,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
