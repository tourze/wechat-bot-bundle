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
class WeChatContact implements \Stringable
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
    #[Assert\Length(max: 20)]
    #[IndexColumn]
    private string $contactType = 'friend';

    #[ORM\Column(
        type: Types::STRING,
        length: 10,
        nullable: true,
        options: ['comment' => '性别：male、female、unknown']
    )]
    #[Assert\Choice(choices: ['male', 'female', 'unknown'])]
    #[Assert\Length(max: 10)]
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
    #[Assert\Length(max: 65535)]
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
        type: Types::DATETIME_IMMUTABLE,
        nullable: true,
        options: ['comment' => '添加好友时间']
    )]
    #[Assert\Type(type: \DateTimeInterface::class)]
    private ?\DateTimeInterface $addFriendTime = null;

    #[ORM\Column(
        type: Types::DATETIME_IMMUTABLE,
        nullable: true,
        options: ['comment' => '最后聊天时间']
    )]
    #[Assert\Type(type: \DateTimeInterface::class)]
    private ?\DateTimeInterface $lastChatTime = null;

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

    public function getContactId(): string
    {
        return $this->contactId;
    }

    public function setContactId(string $contactId): void
    {
        $this->contactId = $contactId;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): void
    {
        $this->nickname = $nickname;
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

    public function getContactType(): string
    {
        return $this->contactType;
    }

    public function setContactType(string $contactType): void
    {
        $this->contactType = $contactType;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): void
    {
        $this->region = $region;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): void
    {
        $this->signature = $signature;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): void
    {
        $this->tags = $tags;
    }

    public function getAddFriendTime(): ?\DateTimeInterface
    {
        return $this->addFriendTime;
    }

    public function setAddFriendTime(?\DateTimeInterface $addFriendTime): void
    {
        $this->addFriendTime = $addFriendTime;
    }

    public function getLastChatTime(): ?\DateTimeInterface
    {
        return $this->lastChatTime;
    }

    public function setLastChatTime(?\DateTimeInterface $lastChatTime): void
    {
        $this->lastChatTime = $lastChatTime;
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
        return 'friend' === $this->contactType;
    }

    public function isStranger(): bool
    {
        return 'stranger' === $this->contactType;
    }

    public function isBlacklisted(): bool
    {
        return 'blacklist' === $this->contactType;
    }

    public function addTag(string $tag): void
    {
        $tags = $this->getTagsArray();
        if (!in_array($tag, $tags, true)) {
            $tags[] = $tag;
            $this->setTagsArray($tags);
        }
    }

    /**
     * @return string[]
     */
    public function getTagsArray(): array
    {
        if (null === $this->tags || '' === $this->tags) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $this->tags)), fn (string $tag): bool => '' !== $tag);
    }

    /**
     * @param string[] $tags
     */
    public function setTagsArray(array $tags): void
    {
        $this->tags = [] !== $tags ? implode(',', $tags) : null;
    }

    public function removeTag(string $tag): void
    {
        $tags = $this->getTagsArray();
        $tags = array_filter($tags, fn ($t) => $t !== $tag);
        $this->setTagsArray($tags);
    }

    public function updateLastChatTime(): void
    {
        $this->lastChatTime = new \DateTimeImmutable();
    }
}
