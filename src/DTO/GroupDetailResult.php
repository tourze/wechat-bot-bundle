<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 群组详情结果DTO
 */
class GroupDetailResult
{
    public function __construct(
        public readonly string $wxid,
        public readonly string $groupName,
        public readonly int $memberCount,
        public readonly int $maxMemberCount,
        public readonly string $ownerWxid,
        public readonly string $notice,
        public readonly string $avatar,
        public readonly int $createTime,
    ) {
    }
}
