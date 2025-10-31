<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 获取当前在线设备列表请求
 * 对应社群助手API文档：获取当前在线设备设备号列表
 * URL: GET http://网关地址/open/queryLoginDevice
 *
 * 注意：此接口为GET请求，不需要设备ID参数
 * 返回当前账号下所有在线的设备ID列表
 */
class GetOnlineDevicesRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
    ) {
    }

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getRequestPath(): string
    {
        return 'open/queryLoginDevice';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'GET';
    }
}
