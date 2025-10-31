<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 文件信息DTO
 */
readonly class FileInfo
{
    public function __construct(
        public string $filePath,
        public string $fileName,
        public int $size,
        public string $mimeType,
        public string $extension,
        public int $modifyTime,
    ) {
    }

    /**
     * 获取格式化的修改时间
     */
    public function getFormattedModifyTime(): string
    {
        return date('Y-m-d H:i:s', $this->modifyTime);
    }
}
