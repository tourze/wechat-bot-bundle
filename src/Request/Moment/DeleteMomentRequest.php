<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Moment;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 删除朋友圈请求
 *
 * 删除自己发布的朋友圈内容：
 * - 删除朋友圈动态
 * - 管理朋友圈内容
 * - 清理过期内容
 *
 * 接口文档: 社群助手API/朋友圈API/删除朋友圈.md
 *
 * @author AI Assistant
 */
class DeleteMomentRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $momentId
    ) {}

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
        return 'open/deleteMoment';
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
