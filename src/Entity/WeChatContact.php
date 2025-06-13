<?php

namespace Tourze\WechatBotBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\WechatBotBundle\Repository\WeChatContactRepository;

/**
 * 微信联系人实体
 * 存储微信好友和联系人信息
 */
#[ORM\Entity(repositoryClass: WeChatContactRepository::class)]
#[ORM\Table(
    name: 'wechat_contact',
    options: ['comment' => '微信联系人表']
)]
#[ORM\Index(columns: ['account_id'], name: 'wechat_contact_idx_account_id')]
#[ORM\Index(columns: ['contact_id'], name: 'wechat_contact_idx_contact_id')]
#[ORM\Index(columns: ['contact_type'], name: 'wechat_contact_idx_contact_type')]
class WeChatContact implements \Stringable
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
        options: ['comment' => '联系人微信ID']
    )]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[IndexColumn]
    private string $contactId;

    #[ORM\Column(
        type: Types::STRING,
        length: 200,
        nullable: true,
        options: ['comment' => '联系人昵称']
    )]
    #[Assert\Length(max: 200)]
    private ?string $nickname = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 200,
        nullable: true,
        options: ['comment' => '联系人备注名']
    )]
    #[Assert\Length(max: 200)]
    private ?string $remarkName = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 500,
        nullable: true,
        options: ['comment' => '头像URL']
    )]
    #[Assert\Length(max: 500)]
    #[Assert\Url]
    private ?string $avatar = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 20,
        options: ['comment' => '联系人类型：friend、stranger、blacklist']
    )]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['friend', 'stranger', 'blacklist'])]
    #[IndexColumn]
    private string $contactType = 'friend';

    #[ORM\Column(
        type: Types::STRING,
        length: 10,
        nullable: true,
        options: ['comment' => '性别：male、female、unknown']
    )]
    #[Assert\Choice(choices: ['male', 'female', 'unknown'])]
    private ?string $gender = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 200,
        nullable: true,
        options: ['comment' => '地区信息']
    )]
    #[Assert\Length(max: 200)]
    private ?string $region = null;

    #[ORM\Column(
        type: Types::TEXT,
        nullable: true,
        options: ['comment' => '个性签名']
    )]
    private ?string $signature = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 100,
        nullable: true,
        options: ['comment' => '标签（逗号分隔）']
    )]
    #[Assert\Length(max: 100)]
    private ?string $tags = null;

    #[ORM\Column(
        type: Types::DATETIME_MUTABLE,
        nullable: true,
        options: ['comment' => '添加好友时间']
    )]
    private ?\DateTimeInterface $addFriendTime = null;

    #[ORM\Column(
        type: Types::DATETIME_MUTABLE,
        nullable: true,
        options: ['comment' => '最后聊天时间']
    )]
    private ?\DateTimeInterface $lastChatTime = null;

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

    public function getContactId(): string
    {
        return $this->contactId;
    }

    public function setContactId(string $contactId): static
    {
        $this->contactId = $contactId;
        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): static
    {
        $this->nickname = $nickname;
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

    public function getContactType(): string
    {
        return $this->contactType;
    }

    public function setContactType(string $contactType): static
    {
        $this->contactType = $contactType;
        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): static
    {
        $this->gender = $gender;
        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        $this->region = $region;
        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): static
    {
        $this->signature = $signature;
        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): static
    {
        $this->tags = $tags;
        return $this;
    }

    public function getAddFriendTime(): ?\DateTimeInterface
    {
        return $this->addFriendTime;
    }

    public function setAddFriendTime(?\DateTimeInterface $addFriendTime): static
    {
        $this->addFriendTime = $addFriendTime;
        return $this;
    }

    public function getLastChatTime(): ?\DateTimeInterface
    {
        return $this->lastChatTime;
    }

    public function setLastChatTime(?\DateTimeInterface $lastChatTime): static
    {
        $this->lastChatTime = $lastChatTime;
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
            '%s (%s)',
            $this->getDisplayName(),
            $this->contactType
        );
    }

    // 业务方法

    public function getDisplayName(): string
    {
        return $this->remarkName ?? $this->nickname ?? $this->contactId;
    }

    public function isFriend(): bool
    {
        return $this->contactType === 'friend';
    }

    public function isStranger(): bool
    {
        return $this->contactType === 'stranger';
    }

    public function isBlacklisted(): bool
    {
        return $this->contactType === 'blacklist';
    }

    public function addTag(string $tag): static
    {
        $tags = $this->getTagsArray();
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->setTagsArray($tags);
        }
        return $this;
    }

    public function getTagsArray(): array
    {
        if (empty($this->tags)) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $this->tags)));
    }

    public function setTagsArray(array $tags): static
    {
        $this->tags = !empty($tags) ? implode(',', $tags) : null;
        return $this;
    }

    public function removeTag(string $tag): static
    {
        $tags = $this->getTagsArray();
        $tags = array_filter($tags, fn($t) => $t !== $tag);
        $this->setTagsArray($tags);
        return $this;
    }

    public function updateLastChatTime(): static
    {
        $this->lastChatTime = new \DateTime();
        return $this;
    }
}
