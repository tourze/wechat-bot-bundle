<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Tag;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 删除好友标签请求
 *
 * 删除不需要的好友标签：
 * - 移除无用标签
 * - 清理标签体系
 * - 标签管理维护
 *
 * 接口文档: 社群助手API/标签API/删除好友标签.md
 *
 * @author AI Assistant
 */
class DeleteFriendTagRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $tagId
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getTagId(): string
    {
        return $this->tagId;
    }

    public function getRequestPath(): string
    {
        return 'open/deleteFriendTag';
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
                'tagId' => $this->tagId,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
