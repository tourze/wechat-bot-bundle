<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Receive;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 获取当前消息类型过滤器列表请求
 *
 * 查询当前设置的消息类型过滤规则：
 * - 已过滤的消息类型
 * - 过滤器配置详情
 * - 管理过滤策略
 *
 * 接口文档: 社群助手API/消息接收API/按消息类型过滤消息/获取当前消息类型过滤器列表.md
 *
 * @author AI Assistant
 */
class GetMessageFiltersRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getRequestPath(): string
    {
        return 'open/getMessageFilters';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
            ],
            'query' => [
                'deviceId' => $this->deviceId,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }
}
