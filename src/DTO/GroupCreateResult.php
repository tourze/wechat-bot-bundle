<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 群组创建结果DTO
 */
class GroupCreateResult
{
    public function __construct(
        public readonly string $groupWxid,
        public readonly string $groupName,
        public readonly array $memberWxids
    ) {}
}
