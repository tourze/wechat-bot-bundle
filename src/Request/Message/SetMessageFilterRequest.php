<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Message;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 设置需要过滤的消息类型请求
 *
 * 设置哪些类型的消息不需要接收回调：
 * - 过滤指定消息类型
 * - 减少无用消息回调
 * - 提高处理效率
 *
 * 接口文档: 社群助手API/消息接收API/按消息类型过滤消息/设置需要过滤的消息类型.md
 *
 * @author AI Assistant
 */
class SetMessageFilterRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        /** @var array<string, mixed> */
        private readonly array $types,
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
    public function getTypes(): array
    {
        return $this->types;
    }

    public function getRequestPath(): string
    {
        return 'open/user/setFilterMsgType';
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
                'types' => $this->types,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
