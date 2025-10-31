<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 文件存储统计DTO
 */
readonly class FileStorageStats
{
    /**
     * @param array<string, mixed> $typeStats
     */
    public function __construct(
        public int $totalFiles,
        public int $totalSize,
        public array $typeStats,
    ) {
    }

    /**
     * 获取格式化的总大小
     */
    public function getFormattedTotalSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->totalSize;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            ++$unitIndex;
        }

        return number_format($size, 2) . ' ' . $units[$unitIndex];
    }
}
