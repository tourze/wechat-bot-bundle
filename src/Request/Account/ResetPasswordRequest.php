<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Request\Account;

use HttpClientBundle\Request\ApiRequest;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

/**
 * 重置账号密码请求
 *
 * 重置微信API平台账户密码：
 * - 修改登录密码
 * - 安全验证
 * - 密码强度要求
 *
 * 接口文档: 社群助手API/账号相关/重置账号密码.md
 *
 * @author AI Assistant
 */
class ResetPasswordRequest extends ApiRequest implements WeChatRequestInterface
{
    public function __construct(
        private readonly WeChatApiAccount $apiAccount,
        private readonly string $oldPassword,
        private readonly string $newPassword,
        private readonly string $confirmPassword
    ) {}

    public function getApiAccount(): WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function getOldPassword(): string
    {
        return $this->oldPassword;
    }

    public function getNewPassword(): string
    {
        return $this->newPassword;
    }

    public function getConfirmPassword(): string
    {
        return $this->confirmPassword;
    }

    public function getRequestPath(): string
    {
        return 'open/user/resetPassword';
    }

    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => $this->apiAccount->getAccessToken(),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'oldPassword' => $this->oldPassword,
                'newPassword' => $this->newPassword,
                'confirmPassword' => $this->confirmPassword,
            ],
        ];
    }

    public function getRequestMethod(): string
    {
        return 'POST';
    }
}
