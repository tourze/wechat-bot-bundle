<?php

namespace Tourze\WechatBotBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\WechatBotBundle\Repository\WeChatMomentRepository;

/**
 * 微信朋友圈动态实体
 * 存储朋友圈动态的内容和信息
 */
#[ORM\Entity(repositoryClass: WeChatMomentRepository::class)]
#[ORM\Table(
    name: 'wechat_moment',
    options: ['comment' => '微信朋友圈动态表']
)]
class WeChatMoment implements \Stringable
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
        length: 100,
        options: ['comment' => '朋友圈动态ID']
    )]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[IndexColumn]
    private ?string $momentId = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 100,
        options: ['comment' => '发布者微信ID']
    )]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[IndexColumn]
    private ?string $authorWxid = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 200,
        nullable: true,
        options: ['comment' => '发布者昵称']
    )]
    #[Assert\Length(max: 200)]
    private ?string $authorNickname = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 500,
        nullable: true,
        options: ['comment' => '发布者头像URL']
    )]
    #[Assert\Length(max: 500)]
    #[Assert\Url]
    private ?string $authorAvatar = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 20,
        options: ['comment' => '动态类型：text、image、video、link']
    )]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['text', 'image', 'video', 'link'])]
    #[Assert\Length(max: 20)]
    #[IndexColumn]
    private ?string $momentType = null;

    #[ORM\Column(
        type: Types::TEXT,
        nullable: true,
        options: ['comment' => '文本内容']
    )]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $textContent = null;

    /** @var array<int, string>|null 图片列表 */
    #[ORM\Column(
        type: Types::JSON,
        nullable: true,
        options: ['comment' => '图片列表，存储图片URL数组']
    )]
    #[Assert\Type(type: 'array')]
    private ?array $images = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(
        type: Types::JSON,
        nullable: true,
        options: ['comment' => '视频信息，包含视频URL、缩略图等']
    )]
    #[Assert\Type(type: 'array')]
    private ?array $video = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(
        type: Types::JSON,
        nullable: true,
        options: ['comment' => '链接信息，包含标题、描述、缩略图等']
    )]
    #[Assert\Type(type: 'array')]
    private ?array $link = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 100,
        nullable: true,
        options: ['comment' => '位置信息']
    )]
    #[Assert\Length(max: 100)]
    private ?string $location = null;

    #[ORM\Column(
        type: Types::INTEGER,
        options: ['comment' => '点赞数量']
    )]
    #[Assert\Type(type: 'int')]
    #[Assert\PositiveOrZero]
    private int $likeCount = 0;

    #[ORM\Column(
        type: Types::INTEGER,
        options: ['comment' => '评论数量']
    )]
    #[Assert\Type(type: 'int')]
    #[Assert\PositiveOrZero]
    private int $commentCount = 0;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['comment' => '是否已点赞']
    )]
    #[Assert\Type(type: 'bool')]
    private bool $isLiked = false;

    #[ORM\Column(
        type: Types::DATETIME_IMMUTABLE,
        options: ['comment' => '发布时间']
    )]
    #[Assert\Type(type: \DateTimeInterface::class)]
    #[IndexColumn]
    private ?\DateTimeInterface $publishTime = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(
        type: Types::JSON,
        nullable: true,
        options: ['comment' => '点赞用户列表']
    )]
    #[Assert\Type(type: 'array')]
    private ?array $likeUsers = null;

    /** @var array<string, mixed>|null */
    #[ORM\Column(
        type: Types::JSON,
        nullable: true,
        options: ['comment' => '评论列表']
    )]
    #[Assert\Type(type: 'array')]
    private ?array $comments = null;

    #[ORM\Column(
        type: Types::TEXT,
        nullable: true,
        options: ['comment' => '原始数据JSON']
    )]
    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    private ?string $rawData = null;

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

    public function getMomentId(): ?string
    {
        return $this->momentId;
    }

    public function setMomentId(string $momentId): void
    {
        $this->momentId = $momentId;
    }

    public function getAuthorWxid(): ?string
    {
        return $this->authorWxid;
    }

    public function setAuthorWxid(string $authorWxid): void
    {
        $this->authorWxid = $authorWxid;
    }

    public function getAuthorNickname(): ?string
    {
        return $this->authorNickname;
    }

    public function setAuthorNickname(?string $authorNickname): void
    {
        $this->authorNickname = $authorNickname;
    }

    public function getAuthorAvatar(): ?string
    {
        return $this->authorAvatar;
    }

    public function setAuthorAvatar(?string $authorAvatar): void
    {
        $this->authorAvatar = $authorAvatar;
    }

    public function getMomentType(): ?string
    {
        return $this->momentType;
    }

    public function setMomentType(string $momentType): void
    {
        $this->momentType = $momentType;
    }

    public function getTextContent(): ?string
    {
        return $this->textContent;
    }

    public function setTextContent(?string $textContent): void
    {
        $this->textContent = $textContent;
    }

    /**
     * @return array<int, string>|null
     */
    public function getImages(): ?array
    {
        return $this->images;
    }

    /**
     * @param array<int, string>|null $images
     */
    public function setImages(?array $images): void
    {
        $this->images = $images;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getVideo(): ?array
    {
        return $this->video;
    }

    /**
     * @param array<string, mixed>|null $video
     */
    public function setVideo(?array $video): void
    {
        $this->video = $video;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLink(): ?array
    {
        return $this->link;
    }

    /**
     * @param array<string, mixed>|null $link
     */
    public function setLink(?array $link): void
    {
        $this->link = $link;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): void
    {
        $this->location = $location;
    }

    public function getLikeCount(): int
    {
        return $this->likeCount;
    }

    public function setLikeCount(int $likeCount): void
    {
        $this->likeCount = $likeCount;
    }

    public function getCommentCount(): int
    {
        return $this->commentCount;
    }

    public function setCommentCount(int $commentCount): void
    {
        $this->commentCount = $commentCount;
    }

    public function isLiked(): bool
    {
        return $this->isLiked;
    }

    public function setIsLiked(bool $isLiked): void
    {
        $this->isLiked = $isLiked;
    }

    public function getPublishTime(): ?\DateTimeInterface
    {
        return $this->publishTime;
    }

    public function setPublishTime(\DateTimeInterface $publishTime): void
    {
        $this->publishTime = $publishTime;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLikeUsers(): ?array
    {
        return $this->likeUsers;
    }

    /**
     * @param array<string, mixed>|null $likeUsers
     */
    public function setLikeUsers(?array $likeUsers): void
    {
        $this->likeUsers = $likeUsers;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getComments(): ?array
    {
        return $this->comments;
    }

    /**
     * @param array<string, mixed>|null $comments
     */
    public function setComments(?array $comments): void
    {
        $this->comments = $comments;
    }

    public function getRawData(): ?string
    {
        return $this->rawData;
    }

    public function setRawData(?string $rawData): void
    {
        $this->rawData = $rawData;
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
        $content = $this->textContent ?? '【' . $this->momentType . '】';

        return sprintf(
            '%s: %s',
            $this->authorNickname ?? $this->authorWxid,
            mb_substr($content, 0, 50) . (mb_strlen($content) > 50 ? '...' : '')
        );
    }

    public function isTextMoment(): bool
    {
        return 'text' === $this->momentType;
    }

    public function isImageMoment(): bool
    {
        return 'image' === $this->momentType;
    }

    public function isVideoMoment(): bool
    {
        return 'video' === $this->momentType;
    }

    public function isLinkMoment(): bool
    {
        return 'link' === $this->momentType;
    }

    public function incrementLikeCount(): void
    {
        ++$this->likeCount;
    }

    public function decrementLikeCount(): void
    {
        $this->likeCount = max(0, $this->likeCount - 1);
    }

    public function incrementCommentCount(): void
    {
        ++$this->commentCount;
    }

    public function decrementCommentCount(): void
    {
        $this->commentCount = max(0, $this->commentCount - 1);
    }
}
