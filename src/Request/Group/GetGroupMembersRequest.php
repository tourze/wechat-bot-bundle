<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Group;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 获取群成员列表请求
 *
 * 获取指定群聊的所有成员信息：
 * - 群成员基本信息
 * - 群内昵称和权限
 * - 成员在线状态
 *
 * 接口文档: 社群助手API/群操作相关API/获取群成员列表.md
 *
 * @author AI Assistant
 */
class GetGroupMembersRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $groupId,
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

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function getRequestPath(): string
    {
        return 'open/getGroupMembers';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
            ],
            'query' => [
                'deviceId' => $this->deviceId,
                'groupId' => $this->groupId,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }
}
