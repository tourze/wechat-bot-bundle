<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Group;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 通过入群邀请请求
 *
 * 处理群聊邀请通知：
 * - 同意或拒绝入群邀请
 * - 自动处理群邀请
 * - 加入指定群聊
 *
 * 接口文档: 社群助手API/群操作相关API/通过入群邀请.md
 *
 * @author AI Assistant
 */
class AcceptGroupInviteRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $groupId,
        private readonly string $inviteId,
        private readonly bool $accept = true
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

    public function getInviteId(): string
    {
        return $this->inviteId;
    }

    public function isAccept(): bool
    {
        return $this->accept;
    }

    public function getRequestPath(): string
    {
        return 'open/acceptGroupInvite';
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
                'inviteId' => $this->inviteId,
                'accept' => $this->accept,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
