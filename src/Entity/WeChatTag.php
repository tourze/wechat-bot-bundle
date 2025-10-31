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
#[UniqueEntity(fields: ['account', 'tagId'], message: '该账号下标签ID已存在')]
#[UniqueEntity(fields: ['account', 'tagName'], message: '该账号下标签名称已存在')]
class WeChatTag implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: WeChatAccount::class, fetch: 'LAZY', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'account_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: '微信账号不能为空')]
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
    #[Assert\Type(type: 'int')]
    #[Assert\PositiveOrZero]
    private int $friendCount = 0;

    /** @var array<int, string>|null */
    #[ORM\Column(
        type: Types::JSON,
        nullable: true,
        options: ['comment' => '标签下的好友列表，存储微信ID数组']
    )]
    #[Assert\Type(type: 'array')]
    private ?array $friendList = null;

    #[ORM\Column(
        type: Types::INTEGER,
        options: ['comment' => '排序权重，数值越大越靠前']
    )]
    #[Assert\Type(type: 'int')]
    private int $sortOrder = 0;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['comment' => '是否为系统标签']
    )]
    #[Assert\Type(type: 'bool')]
    private bool $isSystem = false;

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

    public function getAccount(): ?WeChatAccount
    {
        return $this->account;
    }

    public function setAccount(?WeChatAccount $account): void
    {
        $this->account = $account;
    }

    public function getTagId(): ?string
    {
        return $this->tagId;
    }

    public function setTagId(string $tagId): void
    {
        $this->tagId = $tagId;
    }

    public function getTagName(): ?string
    {
        return $this->tagName;
    }

    public function setTagName(string $tagName): void
    {
        $this->tagName = $tagName;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }

    public function getFriendCount(): int
    {
        return $this->friendCount;
    }

    public function setFriendCount(int $friendCount): void
    {
        $this->friendCount = $friendCount;
    }

    /**
     * @return array<int, string>|null
     */
    public function getFriendList(): ?array
    {
        return $this->friendList;
    }

    /**
     * @param array<int, string>|null $friendList
     */
    public function setFriendList(?array $friendList): void
    {
        $this->friendList = $friendList;
        $this->friendCount = null !== $friendList ? count($friendList) : 0;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
    }

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    public function setIsSystem(bool $isSystem): void
    {
        $this->isSystem = $isSystem;
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
            $this->tagName ?? $this->tagId,
            $this->friendCount
        );
    }

    public function addFriend(string $wxid): void
    {
        $friendList = $this->friendList ?? [];
        if (!in_array($wxid, $friendList, true)) {
            $friendList[] = $wxid;
            $this->setFriendList($friendList);
        }
    }

    public function removeFriend(string $wxid): void
    {
        $friendList = $this->friendList ?? [];
        $key = array_search($wxid, $friendList, true);
        if (false !== $key) {
            unset($friendList[$key]);
            $this->setFriendList(array_values($friendList));
        }
    }

    public function hasFriend(string $wxid): bool
    {
        $friendList = $this->friendList ?? [];

        return in_array($wxid, $friendList, true);
    }

    public function clearFriends(): void
    {
        $this->setFriendList([]);
    }

    public function incrementFriendCount(): void
    {
        ++$this->friendCount;
    }

    public function decrementFriendCount(): void
    {
        $this->friendCount = max(0, $this->friendCount - 1);
    }

    public function isEmpty(): bool
    {
        return 0 === $this->friendCount;
    }
}
