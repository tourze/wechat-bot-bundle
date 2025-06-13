<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Receive;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 获取消息过滤器列表请求
 *
 * 查看当前设置的群消息过滤规则：
 * - 查看所有过滤器
 * - 过滤器状态查询
 * - 管理过滤器列表
 *
 * 接口文档: 社群助手API/消息接收API/按群过滤消息/获取消息过滤器列表.md
 *
 * @author AI Assistant
 */
class GetGroupFiltersRequest extends ApiRequest implements WeChatRequestInterface
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
        return 'open/getGroupFilters';
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
