<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 微信消息数据DTO
 *
 * 封装解析后的微信消息数据
 *
 * @author AI Assistant
 */
class WeChatMessageData implements \Stringable
{
    public function __construct(
        public readonly string $deviceId,
        public readonly ?string $messageId,
        public readonly string $messageType,
        public readonly ?string $senderId,
        public readonly ?string $senderName,
        public readonly ?string $receiverId,
        public readonly ?string $receiverName,
        public readonly ?string $groupId,
        public readonly ?string $groupName,
        public readonly ?string $content,
        public readonly ?string $mediaUrl,
        public readonly ?string $mediaFileName,
        public readonly \DateTimeInterface $messageTime,
    ) {
    }

    public function isGroupMessage(): bool
    {
        return null !== $this->groupId;
    }

    public function isPrivateMessage(): bool
    {
        return null === $this->groupId;
    }

    public function isTextMessage(): bool
    {
        return 'text' === $this->messageType;
    }

    public function isMediaMessage(): bool
    {
        return in_array($this->messageType, ['image', 'voice', 'video', 'file'], true);
    }

    public function getDisplayContent(): string
    {
        if (null !== $this->content && '' !== $this->content) {
            return mb_substr($this->content, 0, 100);
        }

        return match ($this->messageType) {
            'image' => '[图片]',
            'voice' => '[语音]',
            'video' => '[视频]',
            'file' => '[文件]' . (null !== $this->mediaFileName && '' !== $this->mediaFileName ? ': ' . $this->mediaFileName : ''),
            'link' => '[链接]',
            'card' => '[名片]',
            'emoji' => '[表情]',
            default => '[' . $this->messageType . ']',
        };
    }

    public function __toString(): string
    {
        return sprintf(
            'WeChatMessageData(type=%s, from=%s, to=%s, group=%s)',
            $this->messageType,
            $this->senderId ?? 'null',
            $this->receiverId ?? 'null',
            $this->groupId ?? 'null'
        );
    }
}
