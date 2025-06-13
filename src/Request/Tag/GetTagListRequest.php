<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Tag;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 获取标签列表请求
 *
 * 获取当前账号的所有好友标签：
 * - 查看所有创建的标签
 * - 标签ID和名称信息
 * - 用于标签管理和选择
 *
 * 接口文档: 社群助手API/标签API/获取标签列表.md
 *
 * @author AI Assistant
 */
class GetTagListRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId
    ) {}

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
        return 'open/getContactTagList';
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
