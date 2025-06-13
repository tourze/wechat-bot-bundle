<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 初始化通讯录列表请求
 * 对应社群助手API文档：初始化通讯录列表
 * URL: POST http://网关地址/open/initAddressList
 *
 * 注意：此接口将从微信服务器拉取通讯录列表信息到本地进行缓存
 * 好友以及群聊数较多时，此接口需要较长时间拉取数据，请注意设置请求超时时间
 * 如果没有增加好友，后续无需再调用此接口
 */
class InitContactListRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getRequestPath(): string
    {
        return 'open/initAddressList';
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
            ],
            // 拉取通讯录可能需要较长时间，设置较长的超时时间
            'timeout' => 120,
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
