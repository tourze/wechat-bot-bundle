<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Friend;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 获取企业微信联系人信息请求
 *
 * 获取企业微信联系人的详细信息：
 * - 企业微信用户信息
 * - 部门和职位信息
 * - 企业组织架构
 *
 * 接口文档: 社群助手API/好友操作API/获取企业微信联系人信息.md
 *
 * @author AI Assistant
 */
class GetEnterpriseContactRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $wxId,
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

    public function getWxId(): string
    {
        return $this->wxId;
    }

    public function getRequestPath(): string
    {
        return 'open/getEnterpriseContact';
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
