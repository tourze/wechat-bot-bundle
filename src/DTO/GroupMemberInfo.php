<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 群成员信息DTO
 */
class GroupMemberInfo
{
    public function __construct(
        public readonly string $wxid,
        public readonly string $nickname,
        public readonly string $displayName,
        public readonly string $avatar,
        public readonly string $inviterWxid,
        public readonly int $joinTime,
        public readonly bool $isAdmin,
    ) {
    }
}
