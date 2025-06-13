<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Group;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 添加群成员为好友请求
 *
 * 添加群内成员为好友：
 * - 从群内添加好友
 * - 发送好友申请
 * - 扩展社交网络
 *
 * 接口文档: 社群助手API/群操作相关API/添加群成员为好友.md
 *
 * @author AI Assistant
 */
class AddGroupMemberAsFriendRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $groupId,
        private readonly string $memberWxId,
        private readonly string $message = '我是群友，想加你为好友'
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function getMemberWxId(): string
    {
        return $this->memberWxId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getRequestPath(): string
    {
        return 'open/addGroupMemberAsFriend';
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
                'groupId' => $this->groupId,
                'memberWxId' => $this->memberWxId,
                'message' => $this->message,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
