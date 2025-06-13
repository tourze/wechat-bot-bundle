<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Moment;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 获取好友朋友圈详情请求
 *
 * 获取指定朋友圈动态的详细信息：
 * - 朋友圈内容详情
 * - 点赞和评论列表
 * - 图片和视频资源
 *
 * 接口文档: 社群助手API/朋友圈API/获取好友朋友圈详情.md
 *
 * @author AI Assistant
 */
class GetMomentDetailRequest extends ApiRequest implements WeChatRequestInterface
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
        return 'open/getMomentDetail';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
            ],
            'query' => [
                'deviceId' => $this->deviceId,
                'momentId' => $this->momentId,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }
}
