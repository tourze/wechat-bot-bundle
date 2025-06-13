<?php

namespace Tourze\WechatBotBundle\Request;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 输入登录验证码请求
 * 对应社群助手API文档：输入登录验证码
 * URL: POST http://网关地址/open/loginVerifyCode
 *
 * 注意：当手机端出现输入验证码界面时，必须先调用此接口
 * 验证码输入后将自动确认登录并返回账号信息
 * 此接口必须先于确认登录接口调用，否则失效
 */
class InputLoginCodeRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $deviceId,
        private readonly string $code
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getRequestPath(): string
    {
        return 'open/loginVerifyCode';
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
                'code' => $this->code,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
