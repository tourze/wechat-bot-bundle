<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Tag;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 创建好友标签请求
 *
 * 为好友创建自定义标签：
 * - 创建新的标签分类
 * - 设置标签名称和颜色
 * - 用于好友分组管理
 *
 * 接口文档: 社群助手API/标签API/创建好友标签.md
 *
 * @author AI Assistant
 */
class CreateFriendTagRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
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

    public function getTagName(): string
    {
        return $this->tagName;
    }

    public function getRequestPath(): string
    {
        return 'open/createContactTag';
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
                'tagName' => $this->tagName,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
