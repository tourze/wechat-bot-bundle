<?php

namespace Tourze\WechatBotBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\WechatBotBundle\Repository\WeChatMessageRepository;

/**
 * 微信消息实体
 * 存储所有接收和发送的微信消息
 */
#[ORM\Entity(repositoryClass: WeChatMessageRepository::class)]
#[ORM\Table(
    name: 'wechat_message',
    options: ['comment' => '微信消息表']
)]
#[ORM\Index(columns: ['account_id'], name: 'wechat_message_idx_account_id')]
#[ORM\Index(columns: ['message_type'], name: 'wechat_message_idx_message_type')]
#[ORM\Index(columns: ['direction'], name: 'wechat_message_idx_direction')]
#[ORM\Index(columns: ['sender_id'], name: 'wechat_message_idx_sender_id')]
#[ORM\Index(columns: ['receiver_id'], name: 'wechat_message_idx_receiver_id')]
#[ORM\Index(columns: ['group_id'], name: 'wechat_message_idx_group_id')]
#[ORM\Index(columns: ['message_time'], name: 'wechat_message_idx_message_time')]
class WeChatMessage implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: WeChatAccount::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[IndexColumn]
    private WeChatAccount $account;

    #[ORM\Column(
        type: Types::STRING,
        length: 100,
        nullable: true,
        options: ['comment' => '微信消息ID']
    )]
    #[Assert\Length(max: 100)]
    private ?string $messageId = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 50,
        options: ['comment' => '消息类型：text、image、voice、video、file、link、emoji、card、mini_program、xml等']
    )]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[IndexColumn]
    private string $messageType;

    #[ORM\Column(
        type: Types::STRING,
        length: 20,
        options: ['comment' => '消息方向：inbound、outbound']
    )]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['inbound', 'outbound'])]
    #[IndexColumn]
    private string $direction;

    #[ORM\Column(
        type: Types::STRING,
        length: 200,
        nullable: true,
        options: ['comment' => '发送者微信ID']
    )]
    #[Assert\Length(max: 200)]
    #[IndexColumn]
    private ?string $senderId = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 200,
        nullable: true,
        options: ['comment' => '发送者昵称']
    )]
    #[Assert\Length(max: 200)]
    private ?string $senderName = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 200,
        nullable: true,
        options: ['comment' => '接收者微信ID']
    )]
    #[Assert\Length(max: 200)]
    #[IndexColumn]
    private ?string $receiverId = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 200,
        nullable: true,
        options: ['comment' => '接收者昵称']
    )]
    #[Assert\Length(max: 200)]
    private ?string $receiverName = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 200,
        nullable: true,
        options: ['comment' => '群组ID（群消息时使用）']
    )]
    #[Assert\Length(max: 200)]
    #[IndexColumn]
    private ?string $groupId = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 200,
        nullable: true,
        options: ['comment' => '群组名称（群消息时使用）']
    )]
    #[Assert\Length(max: 200)]
    private ?string $groupName = null;

    #[ORM\Column(
        type: Types::TEXT,
        nullable: true,
        options: ['comment' => '消息内容（文本消息）']
    )]
    private ?string $content = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 500,
        nullable: true,
        options: ['comment' => '媒体文件URL（图片、视频、语音、文件等）']
    )]
    #[Assert\Length(max: 500)]
    private ?string $mediaUrl = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 200,
        nullable: true,
        options: ['comment' => '媒体文件名']
    )]
    #[Assert\Length(max: 200)]
    private ?string $mediaFileName = null;

    #[ORM\Column(
        type: Types::INTEGER,
        nullable: true,
        options: ['comment' => '媒体文件大小（字节）']
    )]
    #[Assert\PositiveOrZero]
    private ?int $mediaFileSize = null;

    #[ORM\Column(
        type: Types::TEXT,
        nullable: true,
        options: ['comment' => '消息原始数据（JSON格式）']
    )]
    private ?string $rawData = null;

    #[ORM\Column(
        type: Types::DATETIME_MUTABLE,
        options: ['comment' => '消息时间']
    )]
    #[IndexColumn]
    private \DateTimeInterface $messageTime;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['comment' => '是否已读']
    )]
    private bool $isRead = false;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['comment' => '是否已回复']
    )]
    private bool $isReplied = false;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['comment' => '是否有效']
    )]
    private bool $valid = true;

    public function __construct()
    {
        $this->messageTime = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccount(): WeChatAccount
    {
        return $this->account;
    }

    public function setAccount(WeChatAccount $account): static
    {
        $this->account = $account;
        return $this;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function setMessageId(?string $messageId): static
    {
        $this->messageId = $messageId;
        return $this;
    }

    public function getMessageType(): string
    {
        return $this->messageType;
    }

    public function setMessageType(string $messageType): static
    {
        $this->messageType = $messageType;
        return $this;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function setDirection(string $direction): static
    {
        $this->direction = $direction;
        return $this;
    }

    public function getSenderId(): ?string
    {
        return $this->senderId;
    }

    public function setSenderId(?string $senderId): static
    {
        $this->senderId = $senderId;
        return $this;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function setSenderName(?string $senderName): static
    {
        $this->senderName = $senderName;
        return $this;
    }

    public function getReceiverId(): ?string
    {
        return $this->receiverId;
    }

    public function setReceiverId(?string $receiverId): static
    {
        $this->receiverId = $receiverId;
        return $this;
    }

    public function getReceiverName(): ?string
    {
        return $this->receiverName;
    }

    public function setReceiverName(?string $receiverName): static
    {
        $this->receiverName = $receiverName;
        return $this;
    }

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    public function setGroupId(?string $groupId): static
    {
        $this->groupId = $groupId;
        return $this;
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    public function setGroupName(?string $groupName): static
    {
        $this->groupName = $groupName;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(?string $mediaUrl): static
    {
        $this->mediaUrl = $mediaUrl;
        return $this;
    }

    public function getMediaFileName(): ?string
    {
        return $this->mediaFileName;
    }

    public function setMediaFileName(?string $mediaFileName): static
    {
        $this->mediaFileName = $mediaFileName;
        return $this;
    }

    public function getMediaFileSize(): ?int
    {
        return $this->mediaFileSize;
    }

    public function setMediaFileSize(?int $mediaFileSize): static
    {
        $this->mediaFileSize = $mediaFileSize;
        return $this;
    }

    public function getRawData(): ?string
    {
        return $this->rawData;
    }

    public function setRawData(?string $rawData): static
    {
        $this->rawData = $rawData;
        return $this;
    }

    public function getMessageTime(): \DateTimeInterface
    {
        return $this->messageTime;
    }

    public function setMessageTime(\DateTimeInterface $messageTime): static
    {
        $this->messageTime = $messageTime;
        return $this;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;
        return $this;
    }

    public function isReplied(): bool
    {
        return $this->isReplied;
    }

    public function setIsReplied(bool $isReplied): static
    {
        $this->isReplied = $isReplied;
        return $this;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): static
    {
        $this->valid = $valid;
        return $this;
    }

    public function __toString(): string
    {
        return sprintf(
            '[%s] %s -> %s: %s (%s)',
            $this->messageType,
            $this->senderName ?? $this->senderId ?? 'Unknown',
            $this->receiverName ?? $this->receiverId ?? 'Unknown',
            $this->getDisplayContent(),
            $this->direction
        );
    }

    // 业务方法

    public function getDisplayContent(): string
    {
        if ($this->isTextMessage() && $this->content) {
            return mb_strlen($this->content) > 50
                ? mb_substr($this->content, 0, 50) . '...'
                : $this->content;
        }

        if ($this->isMediaMessage() && $this->mediaFileName) {
            return "[{$this->messageType}] {$this->mediaFileName}";
        }

        return "[{$this->messageType}]";
    }

    public function isTextMessage(): bool
    {
        return $this->messageType === 'text';
    }

    public function isMediaMessage(): bool
    {
        return in_array($this->messageType, ['image', 'voice', 'video', 'file']);
    }

    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }

    public function isOutbound(): bool
    {
        return $this->direction === 'outbound';
    }

    public function isGroupMessage(): bool
    {
        return !empty($this->groupId);
    }

    public function markAsRead(): static
    {
        $this->isRead = true;
        return $this;
    }

    public function markAsReplied(): static
    {
        $this->isReplied = true;
        return $this;
    }
}
