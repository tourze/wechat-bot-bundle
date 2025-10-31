<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Receive;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 删除消息过滤器请求
 *
 * 删除已设置的群消息过滤规则：
 * - 移除特定群过滤器
 * - 恢复群消息接收
 * - 管理过滤器列表
 *
 * 接口文档: 社群助手API/消息接收API/按群过滤消息/删除消息过滤器.md
 *
 * @author AI Assistant
 */
class DeleteGroupFilterRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        /** @var array<string, mixed> */
        private readonly array $groupIds,
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

    /**
     * @return array<string, mixed>
     */
    public function getGroupIds(): array
    {
        return $this->groupIds;
    }

    public function getRequestPath(): string
    {
        return 'open/deleteGroupFilter';
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
                'groupIds' => $this->groupIds,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
