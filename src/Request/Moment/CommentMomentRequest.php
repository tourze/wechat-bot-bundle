<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Moment;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 朋友圈评论请求
 *
 * 对指定朋友圈动态进行评论：
 * - 发表评论内容
 * - 增强朋友圈互动
 * - 支持回复他人评论
 *
 * 接口文档: 社群助手API/朋友圈API/朋友圈评论.md
 *
 * @author AI Assistant
 */
class CommentMomentRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $momentId,
        private readonly string $content,
        private readonly ?string $replyToCommentId = null
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

    public function getReplyToCommentId(): ?string
    {
        return $this->replyToCommentId;
    }

    public function getRequestPath(): string
    {
        return 'open/commentMoment';
    }

    public function getRequestOptions(): ?array
    {
        $requestData = [
            'deviceId' => $this->deviceId,
            'momentId' => $this->momentId,
            'content' => $this->content,
        ];

        if ($this->replyToCommentId !== null) {
            $requestData['replyToCommentId'] = $this->replyToCommentId;
        }

        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
                'Content-Type' => 'application/json',
            ],
            'json' => $requestData,
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
