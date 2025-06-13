<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Tag;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 修改好友标签请求
 *
 * 修改已创建的好友标签：
 * - 更新标签名称
 * - 标签管理维护
 * - 优化标签体系
 *
 * 接口文档: 社群助手API/标签API/修改好友标签.md
 *
 * @author AI Assistant
 */
class UpdateFriendTagRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $tagId,
        private readonly string $tagName
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

    public function getTagName(): string
    {
        return $this->tagName;
    }

    public function getRequestPath(): string
    {
        return 'open/updateFriendTag';
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
                'tagName' => $this->tagName,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
