<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Login;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 重置设备请求
 *
 * 重置指定设备的状态和配置：
 * - 清除设备缓存
 * - 重置登录状态
 * - 恢复初始配置
 *
 * 接口文档: 社群助手API/登录API接口/重置设备.md
 *
 * @author AI Assistant
 */
class ResetDeviceRequest extends ApiRequest implements WeChatRequestInterface
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
        return 'open/resetDevice';
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
