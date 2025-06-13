<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Moment;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 获取朋友圈动态请求
 *
 * 获取朋友圈最新动态列表：
 * - 朋友圈内容和图片
 * - 点赞和评论信息
 * - 发布时间和位置
 *
 * 接口文档: 社群助手API/朋友圈API/获取朋友圈动态.md
 *
 * @author AI Assistant
 */
class GetMomentsRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
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
        return 'open/getMoments';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
            ],
            'query' => [
                'deviceId' => $this->deviceId,
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
