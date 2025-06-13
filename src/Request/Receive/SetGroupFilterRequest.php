<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Receive;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 设置接收消息过滤器请求
 *
 * 设置按群过滤消息的规则：
 * - 指定群聊过滤
 * - 白名单或黑名单模式
 * - 灵活控制消息接收
 *
 * 接口文档: 社群助手API/消息接收API/按群过滤消息/设置接收消息过滤器.md
 *
 * @author AI Assistant
 */
class SetGroupFilterRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly array $groupIds,
        private readonly string $filterType = 'blacklist'
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getGroupIds(): array
    {
        return $this->groupIds;
    }

    public function getFilterType(): string
    {
        return $this->filterType;
    }

    public function getRequestPath(): string
    {
        return 'open/setGroupFilter';
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
                'filterType' => $this->filterType,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
