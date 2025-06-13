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
readonly class WeChatMessageData implements \Stringable
{
    public function __construct(
        public string $deviceId,
        public ?string $messageId,
        public string $messageType,
        public ?string $senderId,
        public ?string $senderName,
        public ?string $receiverId,
        public ?string $receiverName,
        public ?string $groupId,
        public ?string $groupName,
        public ?string $content,
        public ?string $mediaUrl,
        public ?string $mediaFileName,
        public \DateTimeInterface $messageTime
    ) {}

    public function isGroupMessage(): bool
    {
        return $this->groupId !== null;
    }

    public function isPrivateMessage(): bool
    {
        return $this->groupId === null;
    }

    public function isTextMessage(): bool
    {
        return $this->messageType === 'text';
    }

    public function isMediaMessage(): bool
    {
        return in_array($this->messageType, ['image', 'voice', 'video', 'file']);
    }

    public function getDisplayContent(): string
    {
        if ($this->content) {
            return mb_substr($this->content, 0, 100);
        }

        return match ($this->messageType) {
            'image' => '[图片]',
            'voice' => '[语音]',
            'video' => '[视频]',
            'file' => '[文件]' . ($this->mediaFileName ? ': ' . $this->mediaFileName : ''),
            'link' => '[链接]',
            'card' => '[名片]',
            'emoji' => '[表情]',
            default => '[' . $this->messageType . ']'
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
