<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Login;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 获取设备离线弹框说明请求
 *
 * 获取设备离线时的弹框说明信息：
 * - 离线原因说明
 * - 处理建议
 * - 错误代码解释
 *
 * 接口文档: 社群助手API/登录API接口/获取设备离线弹框说明.md
 *
 * @author AI Assistant
 */
class GetDeviceOfflineInfoRequest extends ApiRequest implements WeChatRequestInterface
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
        return 'open/getDeviceOfflineInfo';
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
