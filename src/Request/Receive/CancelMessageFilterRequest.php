<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Receive;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 取消消息类型过滤请求
 *
 * 取消对特定消息类型的过滤设置：
 * - 移除消息类型过滤器
 * - 恢复接收所有类型消息
 * - 灵活管理消息接收策略
 *
 * 接口文档: 社群助手API/消息接收API/按消息类型过滤消息/取消消息类型过滤.md
 *
 * @author AI Assistant
 */
class CancelMessageFilterRequest extends ApiRequest implements WeChatRequestInterface
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
        return 'open/cancelMsgFilter';
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
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
