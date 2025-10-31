<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Login;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 获取指定设备请求
 *
 * 根据设备ID获取特定设备的信息：
 * - 指定设备详情
 * - 设备运行状态
 * - 配置参数信息
 *
 * 接口文档: 社群助手API/登录API接口/获取指定设备.md
 *
 * @author AI Assistant
 */
class GetSpecificDeviceRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
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

    public function getRequestPath(): string
    {
        return 'open/getSpecificDevice';
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
