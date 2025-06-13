<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 文件上传结果DTO
 */
class FileUploadResult
{
    public function __construct(
        public readonly string $url,
        public readonly string $fileId,
        public readonly string $fileName,
        public readonly int $size
    ) {}
}
