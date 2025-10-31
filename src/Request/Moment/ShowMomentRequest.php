<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Moment;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 设置朋友圈公开请求
 *
 * 公开显示指定朋友圈内容：
 * - 设置朋友圈公开
 * - 恢复可见性
 * - 取消隐藏状态
 *
 * 接口文档: 社群助手API/朋友圈API/设置朋友圈公开.md
 *
 * @author AI Assistant
 */
class ShowMomentRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $momentId,
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

    public function getMomentId(): string
    {
        return $this->momentId;
    }

    public function getRequestPath(): string
    {
        return 'open/showMoment';
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
                'momentId' => $this->momentId,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
