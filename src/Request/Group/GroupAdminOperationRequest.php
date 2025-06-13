<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Group;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 群主群管操作请求
 *
 * 执行群主和群管理员相关操作：
 * - 设置/取消群管理员
 * - 群权限管理
 * - 群主专属功能
 *
 * 接口文档: 社群助手API/群操作相关API/群主群管操作.md
 *
 * @author AI Assistant
 */
class GroupAdminOperationRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $groupId,
        private readonly string $memberWxId,
        private readonly string $operation = 'setAdmin'
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

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getRequestPath(): string
    {
        return 'open/groupAdminOperation';
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
                'operation' => $this->operation,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
