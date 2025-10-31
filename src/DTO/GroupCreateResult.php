<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 群组创建结果DTO
 */
readonly class GroupCreateResult
{
    /**
     * @param string[] $memberWxids
     */
    public function __construct(
        public string $groupWxid,
        public string $groupName,
        public array $memberWxids,
    ) {
    }
}
