<?php

namespace Tourze\WechatBotBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineIpBundle\Attribute\UpdateIpColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\WechatBotBundle\Repository\WeChatApiAccountRepository;

/**
 * 微信API平台账号实体
 * 存储微信社群助手API平台的账号配置信息
 */
#[ORM\Entity(repositoryClass: WeChatApiAccountRepository::class)]
#[ORM\Table(
    name: 'wechat_api_account',
    options: ['comment' => '微信API平台账号表']
)]
#[ORM\Index(columns: ['name'], name: 'wechat_api_account_idx_name')]
#[ORM\Index(columns: ['base_url'], name: 'wechat_api_account_idx_base_url')]
#[ORM\Index(columns: ['valid'], name: 'wechat_api_account_idx_valid')]
#[UniqueEntity(fields: ['name'], message: '账号名称已存在')]
class WeChatApiAccount implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 100,
        unique: true,
        options: ['comment' => '账号名称，用于标识不同的API平台账号']
    )]
    #[Assert\NotBlank(message: '账号名称不能为空')]
    #[Assert\Length(max: 100, maxMessage: '账号名称不能超过100个字符')]
    #[IndexColumn]
    private ?string $name = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 255,
        options: ['comment' => 'API网关地址']
    )]
    #[Assert\NotBlank(message: 'API网关地址不能为空')]
    #[Assert\Url(message: 'API网关地址格式不正确')]
    #[Assert\Length(max: 255, maxMessage: 'API网关地址不能超过255个字符')]
    #[IndexColumn]
    private ?string $baseUrl = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 100,
        options: ['comment' => 'API平台用户名']
    )]
    #[Assert\NotBlank(message: 'API平台用户名不能为空')]
    #[Assert\Length(max: 100, maxMessage: 'API平台用户名不能超过100个字符')]
    #[TrackColumn]
    private ?string $username = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 255,
        options: ['comment' => 'API平台密码']
    )]
    #[Assert\NotBlank(message: 'API平台密码不能为空')]
    #[Assert\Length(max: 255, maxMessage: 'API平台密码不能超过255个字符')]
    #[TrackColumn]
    private ?string $password = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 1000,
        nullable: true,
        options: ['comment' => 'API访问令牌，登录成功后获得']
    )]
    #[Assert\Length(max: 1000, maxMessage: 'API访问令牌不能超过1000个字符')]
    #[TrackColumn]
    private ?string $accessToken = null;

    #[ORM\Column(
        type: Types::DATETIME_IMMUTABLE,
        nullable: true,
        options: ['comment' => '令牌过期时间']
    )]
    private ?\DateTimeInterface $tokenExpiresTime = null;

    #[ORM\Column(
        type: Types::DATETIME_IMMUTABLE,
        nullable: true,
        options: ['comment' => '最后登录时间']
    )]
    private ?\DateTimeInterface $lastLoginTime = null;

    #[ORM\Column(
        type: Types::DATETIME_IMMUTABLE,
        nullable: true,
        options: ['comment' => '最后调用API时间']
    )]
    private ?\DateTimeInterface $lastApiCallTime = null;

    #[ORM\Column(
        type: Types::INTEGER,
        options: ['comment' => 'API调用次数统计']
    )]
    #[Assert\PositiveOrZero(message: 'API调用次数不能为负数')]
    private int $apiCallCount = 0;

    #[ORM\Column(
        type: Types::STRING,
        length: 20,
        options: ['comment' => '连接状态：connected、disconnected、error']
    )]
    #[Assert\NotBlank(message: '连接状态不能为空')]
    #[Assert\Choice(
        choices: ['connected', 'disconnected', 'error'],
        message: '连接状态必须是: connected, disconnected, error 中的一个'
    )]
    #[IndexColumn]
    private string $connectionStatus = 'disconnected';

    #[ORM\Column(
        type: Types::INTEGER,
        options: ['comment' => '请求超时时间（秒）']
    )]
    #[Assert\Positive(message: '请求超时时间必须大于0')]
    #[Assert\LessThanOrEqual(value: 300, message: '请求超时时间不能超过300秒')]
    private int $timeout = 30;

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


    #[CreateIpColumn]
    #[ORM\Column(
        type: Types::STRING,
        length: 45,
        nullable: true,
        options: ['comment' => '创建IP地址']
    )]
    private ?string $createdFromIp = null;

    #[UpdateIpColumn]
    #[ORM\Column(
        type: Types::STRING,
        length: 45,
        nullable: true,
        options: ['comment' => '更新IP地址']
    )]
    private ?string $updatedFromIp = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): static
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(?string $accessToken): static
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function getTokenExpiresTime(): ?\DateTimeInterface
    {
        return $this->tokenExpiresTime;
    }

    public function setTokenExpiresTime(?\DateTimeInterface $tokenExpiresTime): static
    {
        $this->tokenExpiresTime = $tokenExpiresTime;
        return $this;
    }

    public function getLastLoginTime(): ?\DateTimeInterface
    {
        return $this->lastLoginTime;
    }

    public function setLastLoginTime(?\DateTimeInterface $lastLoginTime): static
    {
        $this->lastLoginTime = $lastLoginTime;
        return $this;
    }

    public function getLastApiCallTime(): ?\DateTimeInterface
    {
        return $this->lastApiCallTime;
    }

    public function setLastApiCallTime(?\DateTimeInterface $lastApiCallTime): static
    {
        $this->lastApiCallTime = $lastApiCallTime;
        return $this;
    }

    public function getApiCallCount(): int
    {
        return $this->apiCallCount;
    }

    public function setApiCallCount(int $apiCallCount): static
    {
        $this->apiCallCount = $apiCallCount;
        return $this;
    }

    public function incrementApiCallCount(): static
    {
        $this->apiCallCount++;
        $this->lastApiCallTime = new \DateTime();
        return $this;
    }

    public function getConnectionStatus(): string
    {
        return $this->connectionStatus;
    }

    public function setConnectionStatus(string $connectionStatus): static
    {
        $this->connectionStatus = $connectionStatus;
        return $this;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function setTimeout(int $timeout): static
    {
        $this->timeout = $timeout;
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


    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setCreatedFromIp(?string $createdFromIp): static
    {
        $this->createdFromIp = $createdFromIp;
        return $this;
    }

    public function getUpdatedFromIp(): ?string
    {
        return $this->updatedFromIp;
    }

    public function setUpdatedFromIp(?string $updatedFromIp): static
    {
        $this->updatedFromIp = $updatedFromIp;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? "API账号 #{$this->id}";
    }

    /**
     * 检查连接状态
     */
    public function isConnected(): bool
    {
        return $this->connectionStatus === 'connected';
    }

    public function isDisconnected(): bool
    {
        return $this->connectionStatus === 'disconnected';
    }

    public function isError(): bool
    {
        return $this->connectionStatus === 'error';
    }

    /**
     * 标记为已连接状态
     */
    public function markAsConnected(): static
    {
        $this->connectionStatus = 'connected';
        $this->lastLoginTime = new \DateTime();
        return $this;
    }

    /**
     * 标记为断开连接状态
     */
    public function markAsDisconnected(): static
    {
        $this->connectionStatus = 'disconnected';
        return $this;
    }

    /**
     * 标记为错误状态
     */
    public function markAsError(): static
    {
        $this->connectionStatus = 'error';
        return $this;
    }

    /**
     * 检查是否有有效的访问令牌
     */
    public function hasValidToken(): bool
    {
        return !empty($this->accessToken) && !$this->isTokenExpired();
    }

    /**
     * 检查令牌是否已过期
     */
    public function isTokenExpired(): bool
    {
        if ($this->tokenExpiresTime === null) {
            return false;
        }

        return $this->tokenExpiresTime < new \DateTime();
    }
}
