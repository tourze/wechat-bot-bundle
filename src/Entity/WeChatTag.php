<?php

namespace Tourze\WechatBotBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\WechatBotBundle\Repository\WeChatTagRepository;

/**
 * 微信好友标签实体
 * 存储微信好友标签的信息
 */
#[ORM\Entity(repositoryClass: WeChatTagRepository::class)]
#[ORM\Table(
    name: 'wechat_tag',
    options: ['comment' => '微信好友标签表']
)]
#[ORM\Index(columns: ['account_id'], name: 'wechat_tag_idx_account_id')]
#[ORM\Index(columns: ['tag_id'], name: 'wechat_tag_idx_tag_id')]
#[ORM\Index(columns: ['tag_name'], name: 'wechat_tag_idx_tag_name')]
#[UniqueEntity(fields: ['account', 'tagId'], message: '该账号下标签ID已存在')]
#[UniqueEntity(fields: ['account', 'tagName'], message: '该账号下标签名称已存在')]
class WeChatTag implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: WeChatAccount::class, fetch: 'LAZY')]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: '微信账号不能为空')]
    #[IndexColumn]
    private ?WeChatAccount $account = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 50,
        options: ['comment' => '标签ID，微信API返回的标签标识']
    )]
    #[Assert\NotBlank]
    #[Assert\Length(max: 50)]
    #[IndexColumn]
    private ?string $tagId = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 100,
        options: ['comment' => '标签名称']
    )]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[IndexColumn]
    private ?string $tagName = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 20,
        nullable: true,
        options: ['comment' => '标签颜色']
    )]
    #[Assert\Length(max: 20)]
    private ?string $color = null;

    #[ORM\Column(
        type: Types::INTEGER,
        options: ['comment' => '标签下的好友数量']
    )]
    private int $friendCount = 0;

    #[ORM\Column(
        type: Types::JSON,
        nullable: true,
        options: ['comment' => '标签下的好友列表，存储微信ID数组']
    )]
    private ?array $friendList = null;

    #[ORM\Column(
        type: Types::INTEGER,
        options: ['comment' => '排序权重，数值越大越靠前']
    )]
    private int $sortOrder = 0;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['comment' => '是否为系统标签']
    )]
    private bool $isSystem = false;

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

    public function getAccount(): ?WeChatAccount
    {
        return $this->account;
    }

    public function setAccount(?WeChatAccount $account): static
    {
        $this->account = $account;
        return $this;
    }

    public function getTagId(): ?string
    {
        return $this->tagId;
    }

    public function setTagId(string $tagId): static
    {
        $this->tagId = $tagId;
        return $this;
    }

    public function getTagName(): ?string
    {
        return $this->tagName;
    }

    public function setTagName(string $tagName): static
    {
        $this->tagName = $tagName;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function getFriendCount(): int
    {
        return $this->friendCount;
    }

    public function setFriendCount(int $friendCount): static
    {
        $this->friendCount = $friendCount;
        return $this;
    }

    public function getFriendList(): ?array
    {
        return $this->friendList;
    }

    public function setFriendList(?array $friendList): static
    {
        $this->friendList = $friendList;
        $this->friendCount = $friendList !== null ? count($friendList) : 0;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    public function setIsSystem(bool $isSystem): static
    {
        $this->isSystem = $isSystem;
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
            $this->tagName ?? $this->tagId,
            $this->friendCount
        );
    }

    public function addFriend(string $wxid): static
    {
        $friendList = $this->friendList ?? [];
        if (!in_array($wxid, $friendList, true)) {
            $friendList[] = $wxid;
            $this->setFriendList($friendList);
        }
        return $this;
    }

    public function removeFriend(string $wxid): static
    {
        $friendList = $this->friendList ?? [];
        $key = array_search($wxid, $friendList, true);
        if ($key !== false) {
            unset($friendList[$key]);
            $this->setFriendList(array_values($friendList));
        }
        return $this;
    }

    public function hasFriend(string $wxid): bool
    {
        $friendList = $this->friendList ?? [];
        return in_array($wxid, $friendList, true);
    }

    public function clearFriends(): static
    {
        $this->setFriendList([]);
        return $this;
    }

    public function incrementFriendCount(): static
    {
        $this->friendCount++;
        return $this;
    }

    public function decrementFriendCount(): static
    {
        $this->friendCount = max(0, $this->friendCount - 1);
        return $this;
    }

    public function isEmpty(): bool
    {
        return $this->friendCount === 0;
    }
}
