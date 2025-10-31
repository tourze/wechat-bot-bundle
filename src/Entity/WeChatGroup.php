<?php

namespace Tourze\WechatBotBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\WechatBotBundle\Repository\WeChatGroupRepository;

/**
 * 微信群组实体
 * 存储微信群聊信息
 */
#[ORM\Entity(repositoryClass: WeChatGroupRepository::class)]
#[ORM\Table(
    name: 'wechat_group',
    options: ['comment' => '微信群组表']
)]
class WeChatGroup implements \Stringable
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
        length: 200,
        options: ['comment' => '群组微信ID']
    )]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[IndexColumn]
    private string $groupId;

    #[ORM\Column(
        type: Types::STRING,
        length: 200,
        nullable: true,
        options: ['comment' => '群名称']
    )]
    #[Assert\Length(max: 200)]
    private ?string $groupName = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 200,
        nullable: true,
        options: ['comment' => '群备注名']
    )]
    #[Assert\Length(max: 200)]
    private ?string $remarkName = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 500,
        nullable: true,
        options: ['comment' => '群头像URL']
    )]
    #[Assert\Length(max: 500)]
    #[Assert\Url]
    private ?string $avatar = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 200,
        nullable: true,
        options: ['comment' => '群主微信ID']
    )]
    #[Assert\Length(max: 200)]
    private ?string $ownerId = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 200,
        nullable: true,
        options: ['comment' => '群主昵称']
    )]
    #[Assert\Length(max: 200)]
    private ?string $ownerName = null;

    #[ORM\Column(
        type: Types::INTEGER,
        options: ['comment' => '群成员数量']
    )]
    #[Assert\Type(type: 'int')]
    #[Assert\PositiveOrZero]
    private int $memberCount = 0;

    #[ORM\Column(
        type: Types::TEXT,
        nullable: true,
        options: ['comment' => '群公告']
    )]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $announcement = null;

    #[ORM\Column(
        type: Types::TEXT,
        nullable: true,
        options: ['comment' => '群描述']
    )]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $description = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 500,
        nullable: true,
        options: ['comment' => '群二维码URL']
    )]
    #[Assert\Length(max: 500)]
    #[Assert\Url]
    private ?string $qrCodeUrl = null;

    #[ORM\Column(
        type: Types::DATETIME_IMMUTABLE,
        nullable: true,
        options: ['comment' => '加入群时间']
    )]
    #[Assert\Type(type: \DateTimeInterface::class)]
    private ?\DateTimeInterface $joinTime = null;

    #[ORM\Column(
        type: Types::DATETIME_IMMUTABLE,
        nullable: true,
        options: ['comment' => '最后活跃时间']
    )]
    #[Assert\Type(type: \DateTimeInterface::class)]
    private ?\DateTimeInterface $lastActiveTime = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['comment' => '是否在群中']
    )]
    #[Assert\Type(type: 'bool')]
    private bool $inGroup = true;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['comment' => '是否有效']
    )]
    #[Assert\Type(type: 'bool')]
    private bool $valid = true;

    #[ORM\Column(
        type: Types::TEXT,
        nullable: true,
        options: ['comment' => '备注信息']
    )]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $remark = null;

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

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function setGroupId(string $groupId): void
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

    public function getRemarkName(): ?string
    {
        return $this->remarkName;
    }

    public function setRemarkName(?string $remarkName): void
    {
        $this->remarkName = $remarkName;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getOwnerId(): ?string
    {
        return $this->ownerId;
    }

    public function setOwnerId(?string $ownerId): void
    {
        $this->ownerId = $ownerId;
    }

    public function getOwnerName(): ?string
    {
        return $this->ownerName;
    }

    public function setOwnerName(?string $ownerName): void
    {
        $this->ownerName = $ownerName;
    }

    public function getMemberCount(): int
    {
        return $this->memberCount;
    }

    public function setMemberCount(int $memberCount): void
    {
        $this->memberCount = $memberCount;
    }

    public function getAnnouncement(): ?string
    {
        return $this->announcement;
    }

    public function setAnnouncement(?string $announcement): void
    {
        $this->announcement = $announcement;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getQrCodeUrl(): ?string
    {
        return $this->qrCodeUrl;
    }

    public function setQrCodeUrl(?string $qrCodeUrl): void
    {
        $this->qrCodeUrl = $qrCodeUrl;
    }

    public function getJoinTime(): ?\DateTimeInterface
    {
        return $this->joinTime;
    }

    public function setJoinTime(?\DateTimeInterface $joinTime): void
    {
        $this->joinTime = $joinTime;
    }

    public function getLastActiveTime(): ?\DateTimeInterface
    {
        return $this->lastActiveTime;
    }

    public function setLastActiveTime(?\DateTimeInterface $lastActiveTime): void
    {
        $this->lastActiveTime = $lastActiveTime;
    }

    public function isInGroup(): bool
    {
        return $this->inGroup;
    }

    public function setInGroup(bool $inGroup): void
    {
        $this->inGroup = $inGroup;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s (%d人)',
            $this->getDisplayName(),
            $this->memberCount
        );
    }

    // 业务方法

    public function getDisplayName(): string
    {
        return $this->remarkName ?? $this->groupName ?? $this->groupId;
    }

    public function updateLastActiveTime(): void
    {
        $this->lastActiveTime = new \DateTimeImmutable();
    }

    public function leaveGroup(): void
    {
        $this->inGroup = false;
    }

    public function rejoinGroup(): void
    {
        $this->inGroup = true;
    }

    public function increaseMemberCount(int $count = 1): void
    {
        $this->memberCount += $count;
    }

    public function decreaseMemberCount(int $count = 1): void
    {
        $this->memberCount = max(0, $this->memberCount - $count);
    }
}
