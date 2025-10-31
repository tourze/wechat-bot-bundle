<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Group;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 群聊@他人请求
 *
 * 在群聊中@指定成员，支持：
 * - @单个成员
 * - @多个成员
 * - @所有人
 *
 * 接口文档: 社群助手API/群操作相关API/群聊@他人.md
 *
 * @author AI Assistant
 */
class AtGroupMemberRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $chatRoomId,
        private readonly string $content,
        private readonly string $atList,
    ) {
    }

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

    public function getContent(): string
    {
        return $this->content;
    }

    public function getAtList(): string
    {
        return $this->atList;
    }

    public function getRequestPath(): string
    {
        return 'open/sendAtText';
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
                'wxId' => $this->chatRoomId,
                'content' => $this->content,
                'atList' => $this->atList,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
