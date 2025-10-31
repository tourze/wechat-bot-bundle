<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\FileInfo;

/**
 * @internal
 */
#[CoversClass(FileInfo::class)]
final class FileInfoTest extends RequestTestCase
{
    public function testConstruct(): void
    {
        $filePath = '/path/to/file.jpg';
        $fileName = 'file.jpg';
        $size = 1024;
        $mimeType = 'image/jpeg';
        $extension = 'jpg';
        $modifyTime = 1640995200; // 2022-01-01 00:00:00

        $fileInfo = new FileInfo($filePath, $fileName, $size, $mimeType, $extension, $modifyTime);

        $this->assertSame($filePath, $fileInfo->filePath);
        $this->assertSame($fileName, $fileInfo->fileName);
        $this->assertSame($size, $fileInfo->size);
        $this->assertSame($mimeType, $fileInfo->mimeType);
        $this->assertSame($extension, $fileInfo->extension);
        $this->assertSame($modifyTime, $fileInfo->modifyTime);
    }

    public function testGetFormattedModifyTime(): void
    {
        $modifyTime = 1640995200; // 2022-01-01 00:00:00
        $fileInfo = new FileInfo('/path/to/file.jpg', 'file.jpg', 1024, 'image/jpeg', 'jpg', $modifyTime);

        $expected = date('Y-m-d H:i:s', $modifyTime);
        $this->assertSame($expected, $fileInfo->getFormattedModifyTime());
    }

    public function testGetFormattedModifyTimeWithDifferentTimestamp(): void
    {
        $modifyTime = 1609459200; // 2021-01-01 00:00:00
        $fileInfo = new FileInfo('/path/to/test.txt', 'test.txt', 512, 'text/plain', 'txt', $modifyTime);

        $this->assertSame('2021-01-01 00:00:00', $fileInfo->getFormattedModifyTime());
    }
}
