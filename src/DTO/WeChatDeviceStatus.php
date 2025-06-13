<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 微信设备状态DTO
 *
 * 封装微信设备的在线状态和相关信息
 *
 * @author AI Assistant
 */
readonly class WeChatDeviceStatus implements \Stringable
{
    public function __construct(
        public string $deviceId,
        public bool $isOnline,
        public string $status,
        public ?\DateTimeInterface $lastActiveTime = null,
        public ?string $error = null
    ) {}

    public function isOffline(): bool
    {
        return !$this->isOnline;
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }

    public function getStatusText(): string
    {
        return match ($this->status) {
            'online' => '在线',
            'offline' => '离线',
            'pending_login' => '等待登录',
            'expired' => '已过期',
            default => '未知状态'
        };
    }

    public function __toString(): string
    {
        return sprintf(
            'WeChatDeviceStatus(deviceId=%s, status=%s, online=%s)',
            $this->deviceId,
            $this->status,
            $this->isOnline ? 'true' : 'false'
        );
    }
}
