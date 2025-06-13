<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Moment;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 获取好友朋友圈请求
 *
 * 获取指定好友的朋友圈动态：
 * - 查看好友朋友圈
 * - 获取历史动态
 * - 分页浏览支持
 *
 * 接口文档: 社群助手API/朋友圈API/获取好友朋友圈.md
 *
 * @author AI Assistant
 */
class GetFriendMomentsRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $friendWxId,
        private readonly int $page = 1,
        private readonly int $limit = 20
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getFriendWxId(): string
    {
        return $this->friendWxId;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getRequestPath(): string
    {
        return 'open/getFriendMoments';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
            ],
            'query' => [
                'deviceId' => $this->deviceId,
                'friendWxId' => $this->friendWxId,
                'page' => $this->page,
                'limit' => $this->limit,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }
} 