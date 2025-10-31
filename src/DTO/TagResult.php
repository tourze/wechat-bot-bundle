<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 标签结果DTO
 */
class TagResult
{
    public function __construct(
        public readonly string $tagId,
        public readonly string $tagName,
        public readonly int $memberCount,
        public readonly int $createTime,
    ) {
    }

    /**
     * 获取格式化的创建时间
     */
    public function getFormattedCreateTime(): string
    {
        return date('Y-m-d H:i:s', $this->createTime);
    }
}
