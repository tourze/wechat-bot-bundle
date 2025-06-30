<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

/**
 * 二维码状态服务
 *
 * 提供微信登录二维码相关的状态消息处理
 */
class QrCodeStatusService
{
    /**
     * 获取状态消息
     */
    public function getStatusMessage(string $status): string
    {
        return match ($status) {
            'pending_login' => '等待扫码登录',
            'online' => '已登录，设备在线',
            'offline' => '设备离线',
            'expired' => '登录已过期，需要重新登录',
            default => '状态未知'
        };
    }
}
