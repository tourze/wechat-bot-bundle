<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Group;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 添加群成员请求
 *
 * 向指定微信群添加新成员：
 * - 支持添加好友到群
 * - 支持批量添加
 * - 需要群管理员权限
 *
 * 接口文档: 社群助手API/群操作相关API/添加群成员.md
 *
 * @author AI Assistant
 */
class AddGroupMemberRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $chatRoomId,
        private readonly string $userList
    ) {}

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

    public function getUserList(): string
    {
        return $this->userList;
    }

    public function getRequestPath(): string
    {
        return 'open/addChatRoomMember';
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
                'userList' => $this->userList,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
} 