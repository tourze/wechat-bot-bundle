<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Group;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 修改群备注请求
 *
 * 修改指定群聊的备注名称：
 * - 设置群聊备注
 * - 方便管理识别
 * - 个人化群信息
 *
 * 接口文档: 社群助手API/群操作相关API/修改群备注.md
 *
 * @author AI Assistant
 */
class UpdateGroupRemarkRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $groupId,
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

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function getRemark(): string
    {
        return $this->remark;
    }

    public function getRequestPath(): string
    {
        return 'open/updateGroupRemark';
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
                'groupId' => $this->groupId,
                'remark' => $this->remark,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
