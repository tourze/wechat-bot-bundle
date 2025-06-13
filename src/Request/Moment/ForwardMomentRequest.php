<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Moment;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 转发朋友圈请求
 *
 * 转发他人朋友圈内容：
 * - 转发朋友圈动态
 * - 添加转发评论
 * - 分享优质内容
 *
 * 接口文档: 社群助手API/朋友圈API/转发朋友圈.md
 *
 * @author AI Assistant
 */
class ForwardMomentRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $momentId,
        private readonly string $content = ''
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

    public function getContent(): string
    {
        return $this->content;
    }

    public function getRequestPath(): string
    {
        return 'open/forwardMoment';
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
                'content' => $this->content,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
