<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Group;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 获取群成员详情请求
 *
 * 获取指定群成员的详细信息：
 * - 成员基本信息
 * - 在群内角色
 * - 权限和状态
 *
 * 接口文档: 社群助手API/群操作相关API/获取群成员详情.md
 *
 * @author AI Assistant
 */
class GetGroupMemberDetailRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $groupId,
        private readonly string $memberWxId
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

    public function getRequestPath(): string
    {
        return 'open/getGroupMemberDetail';
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
                'memberWxId' => $this->memberWxId,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }
}
