<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

use Tourze\WechatBotBundle\Entity\WeChatAccount;

/**
 * 微信登录结果DTO
 *
 * 封装微信登录操作的结果信息
 *
 * @author AI Assistant
 */
class WeChatLoginResult implements \Stringable
{
    public function __construct(
        public readonly ?WeChatAccount $account,
        public readonly ?string $qrCodeUrl,
        public readonly bool $success,
        public readonly string $message,
        public readonly ?string $error = null,
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return !$this->success;
    }

    public function hasQrCode(): bool
    {
        return null !== $this->qrCodeUrl;
    }

    public function hasAccount(): bool
    {
        return null !== $this->account;
    }

    public function __toString(): string
    {
        return sprintf(
            'WeChatLoginResult(success=%s, message=%s, hasAccount=%s)',
            $this->success ? 'true' : 'false',
            $this->message,
            $this->hasAccount() ? 'true' : 'false'
        );
    }
}
