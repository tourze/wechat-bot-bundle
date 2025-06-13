<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Friend;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 修改好友备注请求
 *
 * 修改指定好友的备注名称：
 * - 设置个性化备注
 * - 便于好友管理
 * - 本地显示名称
 *
 * 接口文档: 社群助手API/好友操作API/修改好友备注.md
 *
 * @author AI Assistant
 */
class UpdateFriendRemarkRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId,
        private readonly string $remark
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getWxId(): string
    {
        return $this->wxId;
    }

    public function getRemark(): string
    {
        return $this->remark;
    }

    public function getRequestPath(): string
    {
        return 'open/updateFriendRemark';
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
                'wxId' => $this->wxId,
                'remark' => $this->remark,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
