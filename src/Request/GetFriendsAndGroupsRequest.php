<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 获取好友和群列表请求
 * 对应社群助手API文档：获取好友和群列表（仅获取通讯录内）
 * URL: POST http://网关地址/open/getAddressList
 *
 * 注意：调用此接口前必须先调用初始化通讯录列表接口
 * 此接口返回本地缓存数据，如需更新缓存请再次调用初始化通讯录列表接口
 * 只返回已主动保存到通讯录内的群聊，其他群聊需通过消息回调获取
 */
class GetFriendsAndGroupsRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly ?string $indexs = null,
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

    public function getIndexs(): ?string
    {
        return $this->indexs;
    }

    public function getRequestPath(): string
    {
        return 'open/getAddressList';
    }

    public function getRequestOptions(): ?array
    {
        $data = [
            'deviceId' => $this->deviceId,
        ];

        // 如果指定了获取类型，添加indexs参数
        if (null !== $this->indexs) {
            $data['indexs'] = $this->indexs;
        }

        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
