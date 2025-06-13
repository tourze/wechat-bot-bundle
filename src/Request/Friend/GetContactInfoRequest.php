<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Friend;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 获取联系人信息请求
 *
 * 获取指定联系人的详细信息：
 * - 头像、昵称、备注
 * - 个性签名、地区信息
 * - 好友状态、标签信息
 *
 * 接口文档: 社群助手API/好友操作API/获取联系人信息.md
 *
 * @author AI Assistant
 */
class GetContactInfoRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId
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

    public function getRequestPath(): string
    {
        return 'open/getContactInfo';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
            ],
            'query' => [
                'deviceId' => $this->deviceId,
                'wxId' => $this->wxId,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }
}
