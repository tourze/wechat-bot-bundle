<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

use Tourze\WechatBotBundle\Entity\WeChatMessage;

/**
 * 微信消息发送结果DTO
 *
 * 封装微信消息发送操作的结果信息
 *
 * @author AI Assistant
 */
readonly class WeChatMessageSendResult implements \Stringable
{
    public function __construct(
        public bool $success,
        public ?WeChatMessage $message,
        public ?array $apiResponse,
        public ?string $errorMessage
    ) {}

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isFailure(): bool
    {
        return !$this->success;
    }

    public function hasMessage(): bool
    {
        return $this->message !== null;
    }

    public function hasError(): bool
    {
        return $this->errorMessage !== null;
    }

    public function getMessageId(): ?int
    {
        return $this->message?->getId();
    }

    public function __toString(): string
    {
        return sprintf(
            'WeChatMessageSendResult(success=%s, messageId=%s, error=%s)',
            $this->success ? 'true' : 'false',
            $this->getMessageId() ?? 'null',
            $this->errorMessage ?? 'none'
        );
    }
}
