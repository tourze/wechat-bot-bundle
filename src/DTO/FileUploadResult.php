<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DTO;

/**
 * 文件上传结果DTO
 */
readonly class FileUploadResult
{
    public function __construct(
        public string $url,
        public string $fileId,
        public string $fileName,
        public int $size,
    ) {
    }
}
