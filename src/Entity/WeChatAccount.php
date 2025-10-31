<?php

namespace Tourze\WechatBotBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\WechatBotBundle\Repository\WeChatAccountRepository;

/**
 * 微信账号实体
 * 存储微信设备和账号的基本信息
 */
#[ORM\Entity(repositoryClass: WeChatAccountRepository::class)]
#[ORM\Table(
    name: 'wechat_account',
    options: ['comment' => '微信账号表']
)]
#[UniqueEntity(fields: ['deviceId'], message: '设备ID已存在')]
#[UniqueEntity(fields: ['wechatId'], message: '微信号已存在')]
class WeChatAccount implements \Stringable
{
    use TimestampableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: WeChatApiAccount::class, fetch: 'LAZY', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'api_account_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'API账号不能为空')]
    private ?WeChatApiAccount $apiAccount = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 100,
        unique: true,
        options: ['comment' => '设备ID，微信API使用的设备标识']
    )]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[IndexColumn]
    private ?string $deviceId = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 100,
        nullable: true,
        options: ['comment' => '微信号']
    )]
    #[Assert\Length(max: 100)]
    #[IndexColumn]
    private ?string $wechatId = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 200,
        nullable: true,
        options: ['comment' => '微信昵称']
    )]
    #[Assert\Length(max: 200)]
    #[TrackColumn]
    private ?string $nickname = null;

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
        options: ['comment' => '账号状态：pending_login、online、offline、expired']
    )]
    #[Assert\NotBlank]
    #[IndexColumn]
    #[Assert\Choice(choices: ['pending_login', 'online', 'offline', 'expired'])]
    #[Assert\Length(max: 20)]
    private string $status = 'pending_login';

    #[ORM\Column(
        type: Types::TEXT,
        nullable: true,
        options: ['comment' => '登录二维码数据']
    )]
    #[Assert\Length(max: 65535)]
    private ?string $qrCode = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 500,
        nullable: true,
        options: ['comment' => '登录二维码图片URL']
    )]
    #[Assert\Length(max: 500)]
    #[Assert\Url]
    private ?string $qrCodeUrl = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 1000,
        nullable: true,
        options: ['comment' => 'API访问令牌']
    )]
    #[Assert\Length(max: 1000)]
    #[TrackColumn]
    private ?string $accessToken = null;

    #[ORM\Column(
        type: Types::DATETIME_IMMUTABLE,
        nullable: true,
        options: ['comment' => '最后登录时间']
    )]
    #[Assert\Type(type: \DateTimeInterface::class)]
    private ?\DateTimeInterface $lastLoginTime = null;

    #[ORM\Column(
        type: Types::DATETIME_IMMUTABLE,
        nullable: true,
        options: ['comment' => '最后活跃时间']
    )]
    #[Assert\Type(type: \DateTimeInterface::class)]
    private ?\DateTimeInterface $lastActiveTime = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 100,
        nullable: true,
        options: ['comment' => '网络代理设置']
    )]
    #[Assert\Length(max: 100)]
    private ?string $proxy = null;

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
    #[Assert\Length(max: 65535)]
    private ?string $remark = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDeviceId(): ?string
    {
        return $this->deviceId;
    }

    public function setDeviceId(string $deviceId): void
    {
        $this->deviceId = $deviceId;
    }

    public function getWechatId(): ?string
    {
        return $this->wechatId;
    }

    public function setWechatId(?string $wechatId): void
    {
        $this->wechatId = $wechatId;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): void
    {
        $this->nickname = $nickname;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getQrCode(): ?string
    {
        return $this->qrCode;
    }

    public function setQrCode(?string $qrCode): void
    {
        $this->qrCode = $qrCode;
    }

    public function getQrCodeUrl(): ?string
    {
        return $this->qrCodeUrl;
    }

    public function setQrCodeUrl(?string $qrCodeUrl): void
    {
        $this->qrCodeUrl = $qrCodeUrl;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    public function getLastLoginTime(): ?\DateTimeInterface
    {
        return $this->lastLoginTime;
    }

    public function setLastLoginTime(?\DateTimeInterface $lastLoginTime): void
    {
        $this->lastLoginTime = $lastLoginTime;
    }

    public function getLastActiveTime(): ?\DateTimeInterface
    {
        return $this->lastActiveTime;
    }

    public function setLastActiveTime(?\DateTimeInterface $lastActiveTime): void
    {
        $this->lastActiveTime = $lastActiveTime;
    }

    public function getProxy(): ?string
    {
        return $this->proxy;
    }

    public function setProxy(?string $proxy): void
    {
        $this->proxy = $proxy;
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
            '[%s] %s (%s)',
            $this->deviceId ?? 'N/A',
            $this->nickname ?? $this->wechatId ?? 'Unknown',
            $this->status
        );
    }

    // 业务方法

    public function isOnline(): bool
    {
        return 'online' === $this->status;
    }

    public function isOffline(): bool
    {
        return 'offline' === $this->status;
    }

    public function isPendingLogin(): bool
    {
        return 'pending_login' === $this->status;
    }

    public function isExpired(): bool
    {
        return 'expired' === $this->status;
    }

    public function markAsOnline(): void
    {
        $this->status = 'online';
        $this->lastActiveTime = new \DateTimeImmutable();
    }

    public function markAsOffline(): void
    {
        $this->status = 'offline';
    }

    public function markAsExpired(): void
    {
        $this->status = 'expired';
    }

    public function updateLastActiveTime(): void
    {
        $this->lastActiveTime = new \DateTimeImmutable();
    }

    public function getApiAccount(): ?WeChatApiAccount
    {
        return $this->apiAccount;
    }

    public function setApiAccount(?WeChatApiAccount $apiAccount): void
    {
        $this->apiAccount = $apiAccount;
    }
}
