<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 文件信息DTO
 */
class FileInfo
{
    public function __construct(
        public readonly string $filePath,
        public readonly string $fileName,
        public readonly int $size,
        public readonly string $mimeType,
        public readonly string $extension,
        public readonly int $modifyTime
    ) {}

    /**
     * 获取格式化的修改时间
     */
    public function getFormattedModifyTime(): string
    {
        return date('Y-m-d H:i:s', $this->modifyTime);
    }
}
