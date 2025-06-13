<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 朋友圈列表结果DTO
 */
class MomentsResult
{
    public function __construct(
        public readonly array $moments,
        public readonly string $nextMaxId,
        public readonly bool $hasMore
    ) {}
}
