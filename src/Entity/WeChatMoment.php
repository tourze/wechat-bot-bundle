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
#[ORM\Index(columns: ['account_id'], name: 'wechat_moment_idx_account_id')]
#[ORM\Index(columns: ['moment_id'], name: 'wechat_moment_idx_moment_id')]
#[ORM\Index(columns: ['author_wxid'], name: 'wechat_moment_idx_author_wxid')]
#[ORM\Index(columns: ['publish_time'], name: 'wechat_moment_idx_publish_time')]
#[ORM\Index(columns: ['moment_type'], name: 'wechat_moment_idx_moment_type')]
class WeChatMoment implements \Stringable
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
    #[IndexColumn]
    private ?string $momentType = null;

    #[ORM\Column(
        type: Types::TEXT,
        nullable: true,
        options: ['comment' => '文本内容']
    )]
    private ?string $textContent = null;

    #[ORM\Column(
        type: Types::JSON,
        nullable: true,
        options: ['comment' => '图片列表，存储图片URL数组']
    )]
    private ?array $images = null;

    #[ORM\Column(
        type: Types::JSON,
        nullable: true,
        options: ['comment' => '视频信息，包含视频URL、缩略图等']
    )]
    private ?array $video = null;

    #[ORM\Column(
        type: Types::JSON,
        nullable: true,
        options: ['comment' => '链接信息，包含标题、描述、缩略图等']
    )]
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
    private int $likeCount = 0;

    #[ORM\Column(
        type: Types::INTEGER,
        options: ['comment' => '评论数量']
    )]
    private int $commentCount = 0;

    #[ORM\Column(
        type: Types::BOOLEAN,
        options: ['comment' => '是否已点赞']
    )]
    private bool $isLiked = false;

    #[ORM\Column(
        type: Types::DATETIME_IMMUTABLE,
        options: ['comment' => '发布时间']
    )]
    #[IndexColumn]
    private ?\DateTimeInterface $publishTime = null;

    #[ORM\Column(
        type: Types::JSON,
        nullable: true,
        options: ['comment' => '点赞用户列表']
    )]
    private ?array $likeUsers = null;

    #[ORM\Column(
        type: Types::JSON,
        nullable: true,
        options: ['comment' => '评论列表']
    )]
    private ?array $comments = null;

    #[ORM\Column(
        type: Types::TEXT,
        nullable: true,
        options: ['comment' => '原始数据JSON']
    )]
    private ?string $rawData = null;

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

    public function getMomentId(): ?string
    {
        return $this->momentId;
    }

    public function setMomentId(string $momentId): static
    {
        $this->momentId = $momentId;
        return $this;
    }

    public function getAuthorWxid(): ?string
    {
        return $this->authorWxid;
    }

    public function setAuthorWxid(string $authorWxid): static
    {
        $this->authorWxid = $authorWxid;
        return $this;
    }

    public function getAuthorNickname(): ?string
    {
        return $this->authorNickname;
    }

    public function setAuthorNickname(?string $authorNickname): static
    {
        $this->authorNickname = $authorNickname;
        return $this;
    }

    public function getAuthorAvatar(): ?string
    {
        return $this->authorAvatar;
    }

    public function setAuthorAvatar(?string $authorAvatar): static
    {
        $this->authorAvatar = $authorAvatar;
        return $this;
    }

    public function getMomentType(): ?string
    {
        return $this->momentType;
    }

    public function setMomentType(string $momentType): static
    {
        $this->momentType = $momentType;
        return $this;
    }

    public function getTextContent(): ?string
    {
        return $this->textContent;
    }

    public function setTextContent(?string $textContent): static
    {
        $this->textContent = $textContent;
        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(?array $images): static
    {
        $this->images = $images;
        return $this;
    }

    public function getVideo(): ?array
    {
        return $this->video;
    }

    public function setVideo(?array $video): static
    {
        $this->video = $video;
        return $this;
    }

    public function getLink(): ?array
    {
        return $this->link;
    }

    public function setLink(?array $link): static
    {
        $this->link = $link;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getLikeCount(): int
    {
        return $this->likeCount;
    }

    public function setLikeCount(int $likeCount): static
    {
        $this->likeCount = $likeCount;
        return $this;
    }

    public function getCommentCount(): int
    {
        return $this->commentCount;
    }

    public function setCommentCount(int $commentCount): static
    {
        $this->commentCount = $commentCount;
        return $this;
    }

    public function isLiked(): bool
    {
        return $this->isLiked;
    }

    public function setIsLiked(bool $isLiked): static
    {
        $this->isLiked = $isLiked;
        return $this;
    }

    public function getPublishTime(): ?\DateTimeInterface
    {
        return $this->publishTime;
    }

    public function setPublishTime(\DateTimeInterface $publishTime): static
    {
        $this->publishTime = $publishTime;
        return $this;
    }

    public function getLikeUsers(): ?array
    {
        return $this->likeUsers;
    }

    public function setLikeUsers(?array $likeUsers): static
    {
        $this->likeUsers = $likeUsers;
        return $this;
    }

    public function getComments(): ?array
    {
        return $this->comments;
    }

    public function setComments(?array $comments): static
    {
        $this->comments = $comments;
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
        $content = $this->textContent ?: '【' . $this->momentType . '】';
        return sprintf(
            '%s: %s',
            $this->authorNickname ?: $this->authorWxid,
            mb_substr($content, 0, 50) . (mb_strlen($content) > 50 ? '...' : '')
        );
    }

    public function isTextMoment(): bool
    {
        return $this->momentType === 'text';
    }

    public function isImageMoment(): bool
    {
        return $this->momentType === 'image';
    }

    public function isVideoMoment(): bool
    {
        return $this->momentType === 'video';
    }

    public function isLinkMoment(): bool
    {
        return $this->momentType === 'link';
    }

    public function incrementLikeCount(): static
    {
        $this->likeCount++;
        return $this;
    }

    public function decrementLikeCount(): static
    {
        $this->likeCount = max(0, $this->likeCount - 1);
        return $this;
    }

    public function incrementCommentCount(): static
    {
        $this->commentCount++;
        return $this;
    }

    public function decrementCommentCount(): static
    {
        $this->commentCount = max(0, $this->commentCount - 1);
        return $this;
    }
}
