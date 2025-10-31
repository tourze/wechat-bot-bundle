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
class WeChatMessageSendResult implements \Stringable
{
    /**
     * @param array<string, mixed> $apiResponse
     */
    public function __construct(
        public readonly bool $success,
        public readonly ?WeChatMessage $message,
        public readonly ?array $apiResponse,
        public readonly ?string $errorMessage,
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

    public function hasMessage(): bool
    {
        return null !== $this->message;
    }

    public function hasError(): bool
    {
        return null !== $this->errorMessage;
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
