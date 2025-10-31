<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Group;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 修改在群里昵称请求
 *
 * 修改自己在指定群里的昵称：
 * - 设置群内昵称
 * - 个性化群内身份
 * - 群身份管理
 *
 * 接口文档: 社群助手API/群操作相关API/修改在群里昵称.md
 *
 * @author AI Assistant
 */
class UpdateGroupNicknameRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $groupId,
        private readonly string $nickname,
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

    public function getNickname(): string
    {
        return $this->nickname;
    }

    public function getRequestPath(): string
    {
        return 'open/updateGroupNickname';
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
                'nickname' => $this->nickname,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
