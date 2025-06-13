<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Group;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 保存群聊天到通讯录请求
 *
 * 将群聊保存到通讯录：
 * - 添加群到通讯录
 * - 方便群管理
 * - 群组收藏功能
 *
 * 接口文档: 社群助手API/群操作相关API/保存群聊天到通讯录.md
 *
 * @author AI Assistant
 */
class SaveGroupToContactRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $groupId
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

    public function getRequestPath(): string
    {
        return 'open/saveGroupToContact';
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
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
