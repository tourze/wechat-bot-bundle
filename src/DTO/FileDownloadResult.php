<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 文件下载结果DTO
 */
class FileDownloadResult
{
    public function __construct(
        public readonly string $localPath,
        public readonly string $originalName,
        public readonly int $size,
        public readonly string $mimeType,
        public readonly string $originalUrl,
        public readonly int $duration = 0
    ) {}

    /**
     * 获取文件扩展名
     */
    public function getExtension(): string
    {
        return strtolower(pathinfo($this->originalName, PATHINFO_EXTENSION));
    }

    /**
     * 是否是图片
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mimeType, 'image/');
    }

    /**
     * 是否是视频
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mimeType, 'video/');
    }

    /**
     * 是否是音频
     */
    public function isAudio(): bool
    {
        return str_starts_with($this->mimeType, 'audio/');
    }

    /**
     * 获取格式化的文件大小
     */
    public function getFormattedSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return number_format($size, 2) . ' ' . $units[$unitIndex];
    }
}
