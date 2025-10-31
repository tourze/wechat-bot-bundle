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
class WeChatMessage implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: WeChatAccount::class, cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
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
    #[Assert\Length(max: 20)]
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
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $content = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 500,
        nullable: true,
        options: ['comment' => '媒体文件URL（图片、视频、语音、文件等）']
    )]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 500)]
    #[Assert\Url]
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
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $rawData = null;

    #[ORM\Column(
        type: Types::DATETIME_IMMUTABLE,
        options: ['comment' => '消息时间']
    )]
    #[Assert\Type(type: \DateTimeInterface::class)]
    #[IndexColumn]
    private \DateTimeInterface $messageTime;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['comment' => '是否已读']
    )]
    #[Assert\Type(type: 'bool')]
    private bool $isRead = false;

    #[ORM\Column(
        type: Types::DATETIME_IMMUTABLE,
        nullable: true,
        options: ['comment' => '已读时间']
    )]
    #[Assert\Type(type: \DateTimeInterface::class)]
    private ?\DateTimeInterface $readTime = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['comment' => '是否已回复']
    )]
    #[Assert\Type(type: 'bool')]
    private bool $isReplied = false;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['comment' => '是否有效']
    )]
    #[Assert\Type(type: 'bool')]
    private bool $valid = true;

    public function __construct()
    {
        $this->messageTime = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccount(): WeChatAccount
    {
        return $this->account;
    }

    public function setAccount(WeChatAccount $account): void
    {
        $this->account = $account;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function setMessageId(?string $messageId): void
    {
        $this->messageId = $messageId;
    }

    public function getMessageType(): string
    {
        return $this->messageType;
    }

    public function setMessageType(string $messageType): void
    {
        $this->messageType = $messageType;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function setDirection(string $direction): void
    {
        $this->direction = $direction;
    }

    public function getSenderId(): ?string
    {
        return $this->senderId;
    }

    public function setSenderId(?string $senderId): void
    {
        $this->senderId = $senderId;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function setSenderName(?string $senderName): void
    {
        $this->senderName = $senderName;
    }

    public function getReceiverId(): ?string
    {
        return $this->receiverId;
    }

    public function setReceiverId(?string $receiverId): void
    {
        $this->receiverId = $receiverId;
    }

    public function getReceiverName(): ?string
    {
        return $this->receiverName;
    }

    public function setReceiverName(?string $receiverName): void
    {
        $this->receiverName = $receiverName;
    }

    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    public function setGroupId(?string $groupId): void
    {
        $this->groupId = $groupId;
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    public function setGroupName(?string $groupName): void
    {
        $this->groupName = $groupName;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getMediaUrl(): ?string
    {
        return $this->mediaUrl;
    }

    public function setMediaUrl(?string $mediaUrl): void
    {
        $this->mediaUrl = $mediaUrl;
    }

    public function getMediaFileName(): ?string
    {
        return $this->mediaFileName;
    }

    public function setMediaFileName(?string $mediaFileName): void
    {
        $this->mediaFileName = $mediaFileName;
    }

    public function getMediaFileSize(): ?int
    {
        return $this->mediaFileSize;
    }

    public function setMediaFileSize(?int $mediaFileSize): void
    {
        $this->mediaFileSize = $mediaFileSize;
    }

    public function getRawData(): ?string
    {
        return $this->rawData;
    }

    public function setRawData(?string $rawData): void
    {
        $this->rawData = $rawData;
    }

    public function getMessageTime(): \DateTimeInterface
    {
        return $this->messageTime;
    }

    public function setMessageTime(\DateTimeInterface $messageTime): void
    {
        $this->messageTime = $messageTime;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): void
    {
        $this->isRead = $isRead;
    }

    public function getReadTime(): ?\DateTimeInterface
    {
        return $this->readTime;
    }

    public function setReadTime(?\DateTimeInterface $readTime): void
    {
        $this->readTime = $readTime;
    }

    public function isReplied(): bool
    {
        return $this->isReplied;
    }

    public function setIsReplied(bool $isReplied): void
    {
        $this->isReplied = $isReplied;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
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
        if ($this->isTextMessage() && null !== $this->content && '' !== $this->content) {
            return mb_strlen($this->content) > 50
                ? mb_substr($this->content, 0, 50) . '...'
                : $this->content;
        }

        if ($this->isMediaMessage() && null !== $this->mediaFileName && '' !== $this->mediaFileName) {
            return "[{$this->messageType}] {$this->mediaFileName}";
        }

        return "[{$this->messageType}]";
    }

    public function isTextMessage(): bool
    {
        return 'text' === $this->messageType;
    }

    public function isMediaMessage(): bool
    {
        return in_array($this->messageType, ['image', 'voice', 'video', 'file'], true);
    }

    public function isInbound(): bool
    {
        return 'inbound' === $this->direction;
    }

    public function isOutbound(): bool
    {
        return 'outbound' === $this->direction;
    }

    public function isGroupMessage(): bool
    {
        return null !== $this->groupId && '' !== $this->groupId;
    }

    public function markAsRead(): void
    {
        $this->isRead = true;
        $this->readTime = new \DateTimeImmutable();
    }

    public function markAsReplied(): void
    {
        $this->isReplied = true;
    }
}
