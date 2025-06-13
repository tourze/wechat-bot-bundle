<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Moment;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 朋友圈点赞请求
 *
 * 对指定朋友圈动态进行点赞操作：
 * - 支持点赞和取消点赞
 * - 增强朋友圈互动
 * - 维护社交关系
 *
 * 接口文档: 社群助手API/朋友圈API/朋友圈点赞.md
 *
 * @author AI Assistant
 */
class LikeMomentRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $momentId,
        private readonly bool $isLike = true
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

    public function isLike(): bool
    {
        return $this->isLike;
    }

    public function getRequestPath(): string
    {
        return 'open/likeMoment';
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
                'isLike' => $this->isLike,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
