<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 邀请群成员请求
 * 对应社群助手API文档：邀请群成员
 * URL: POST http://网关地址/open/inviteChatRoomMember
 *
 * 注意：
 * - 当群成员大于40人时，无法直接添加群成员，请调用当前接口发送邀请链接
 * - 群成员少于40人时，也可以调用此接口发送邀请链接
 * - 新群不建议直接拉人，可使用多个机器人在群里随意发几天消息后再拉人
 * - 新号建议1小时内拉人数不要超过6个，老号可适当放宽，但不宜超过15个
 */
class InviteGroupMemberRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $chatRoomId,
        private readonly string $userList
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getChatRoomId(): string
    {
        return $this->chatRoomId;
    }

    public function getUserList(): string
    {
        return $this->userList;
    }

    public function getRequestPath(): string
    {
        return 'open/inviteChatRoomMember';
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
                'chatRoomId' => $this->chatRoomId,
                'userList' => $this->userList,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
