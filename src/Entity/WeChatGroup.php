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
#[ORM\Index(columns: ['account_id'], name: 'wechat_group_idx_account_id')]
#[ORM\Index(columns: ['group_id'], name: 'wechat_group_idx_group_id')]
class WeChatGroup implements \Stringable
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
    #[Assert\PositiveOrZero]
    private int $memberCount = 0;

    #[ORM\Column(
        type: Types::TEXT,
        nullable: true,
        options: ['comment' => '群公告']
    )]
    private ?string $announcement = null;

    #[ORM\Column(
        type: Types::TEXT,
        nullable: true,
        options: ['comment' => '群描述']
    )]
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
        type: Types::DATETIME_MUTABLE,
        nullable: true,
        options: ['comment' => '加入群时间']
    )]
    private ?\DateTimeInterface $joinTime = null;

    #[ORM\Column(
        type: Types::DATETIME_MUTABLE,
        nullable: true,
        options: ['comment' => '最后活跃时间']
    )]
    private ?\DateTimeInterface $lastActiveTime = null;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['comment' => '是否在群中']
    )]
    private bool $inGroup = true;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['comment' => '是否有效']
    )]
    private bool $valid = true;

    #[ORM\Column(
        type: Types::TEXT,
        nullable: true,
        options: ['comment' => '备注信息']
    )]
    private ?string $remark = null;

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

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function setGroupId(string $groupId): static
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

    public function getRemarkName(): ?string
    {
        return $this->remarkName;
    }

    public function setRemarkName(?string $remarkName): static
    {
        $this->remarkName = $remarkName;
        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;
        return $this;
    }

    public function getOwnerId(): ?string
    {
        return $this->ownerId;
    }

    public function setOwnerId(?string $ownerId): static
    {
        $this->ownerId = $ownerId;
        return $this;
    }

    public function getOwnerName(): ?string
    {
        return $this->ownerName;
    }

    public function setOwnerName(?string $ownerName): static
    {
        $this->ownerName = $ownerName;
        return $this;
    }

    public function getMemberCount(): int
    {
        return $this->memberCount;
    }

    public function setMemberCount(int $memberCount): static
    {
        $this->memberCount = $memberCount;
        return $this;
    }

    public function getAnnouncement(): ?string
    {
        return $this->announcement;
    }

    public function setAnnouncement(?string $announcement): static
    {
        $this->announcement = $announcement;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getQrCodeUrl(): ?string
    {
        return $this->qrCodeUrl;
    }

    public function setQrCodeUrl(?string $qrCodeUrl): static
    {
        $this->qrCodeUrl = $qrCodeUrl;
        return $this;
    }

    public function getJoinTime(): ?\DateTimeInterface
    {
        return $this->joinTime;
    }

    public function setJoinTime(?\DateTimeInterface $joinTime): static
    {
        $this->joinTime = $joinTime;
        return $this;
    }

    public function getLastActiveTime(): ?\DateTimeInterface
    {
        return $this->lastActiveTime;
    }

    public function setLastActiveTime(?\DateTimeInterface $lastActiveTime): static
    {
        $this->lastActiveTime = $lastActiveTime;
        return $this;
    }

    public function isInGroup(): bool
    {
        return $this->inGroup;
    }

    public function setInGroup(bool $inGroup): static
    {
        $this->inGroup = $inGroup;
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

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): static
    {
        $this->remark = $remark;
        return $this;
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

    public function updateLastActiveTime(): static
    {
        $this->lastActiveTime = new \DateTime();
        return $this;
    }

    public function leaveGroup(): static
    {
        $this->inGroup = false;
        return $this;
    }

    public function rejoinGroup(): static
    {
        $this->inGroup = true;
        return $this;
    }

    public function increaseMemberCount(int $count = 1): static
    {
        $this->memberCount += $count;
        return $this;
    }

    public function decreaseMemberCount(int $count = 1): static
    {
        $this->memberCount = max(0, $this->memberCount - $count);
        return $this;
    }
}
