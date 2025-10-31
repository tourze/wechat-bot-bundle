<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Group;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 获取群详细信息请求
 *
 * 获取指定群聊的详细信息：
 * - 群名称、头像、公告
 * - 群成员数量、创建时间
 * - 群设置和权限信息
 *
 * 接口文档: 社群助手API/群操作相关API/获取群详细信息.md
 *
 * @author AI Assistant
 */
class GetGroupDetailRequest extends ApiRequest implements WeChatRequestInterface
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
        return 'open/getGroupDetail';
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
