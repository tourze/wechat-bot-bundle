<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\FileUploadResult;

/**
 * FileUploadResult DTO 单元测试
 *
 * @internal
 */
#[CoversClass(FileUploadResult::class)]
final class FileUploadResultTest extends RequestTestCase
{
    public function testConstructWithValidParametersSetsPropertiesCorrectly(): void
    {
        // Arrange
        $url = 'https://example.com/uploaded/file.jpg';
        $fileId = 'file_123456';
        $fileName = 'uploaded_photo.jpg';
        $size = 1024000;

        // Act
        $result = new FileUploadResult(
            url: $url,
            fileId: $fileId,
            fileName: $fileName,
            size: $size
        );

        // Assert
        $this->assertSame($url, $result->url);
        $this->assertSame($fileId, $result->fileId);
        $this->assertSame($fileName, $result->fileName);
        $this->assertSame($size, $result->size);
    }

    public function testConstructWithImageFile(): void
    {
        // Act
        $result = new FileUploadResult(
            url: 'https://cdn.example.com/images/photo.png',
            fileId: 'img_789012',
            fileName: 'holiday_photo.png',
            size: 2048000
        );

        // Assert
        $this->assertSame('https://cdn.example.com/images/photo.png', $result->url);
        $this->assertSame('img_789012', $result->fileId);
        $this->assertSame('holiday_photo.png', $result->fileName);
        $this->assertSame(2048000, $result->size);
    }

    public function testConstructWithDocumentFile(): void
    {
        // Act
        $result = new FileUploadResult(
            url: 'https://storage.example.com/docs/document.pdf',
            fileId: 'doc_345678',
            fileName: 'important_document.pdf',
            size: 512000
        );

        // Assert
        $this->assertSame('https://storage.example.com/docs/document.pdf', $result->url);
        $this->assertSame('doc_345678', $result->fileId);
        $this->assertSame('important_document.pdf', $result->fileName);
        $this->assertSame(512000, $result->size);
    }

    public function testConstructWithVideoFile(): void
    {
        // Act
        $result = new FileUploadResult(
            url: 'https://video.example.com/videos/clip.mp4',
            fileId: 'vid_901234',
            fileName: 'vacation_video.mp4',
            size: 10485760
        );

        // Assert
        $this->assertSame('https://video.example.com/videos/clip.mp4', $result->url);
        $this->assertSame('vid_901234', $result->fileId);
        $this->assertSame('vacation_video.mp4', $result->fileName);
        $this->assertSame(10485760, $result->size);
    }

    public function testConstructWithAudioFile(): void
    {
        // Act
        $result = new FileUploadResult(
            url: 'https://audio.example.com/music/song.mp3',
            fileId: 'aud_567890',
            fileName: 'favorite_song.mp3',
            size: 3145728
        );

        // Assert
        $this->assertSame('https://audio.example.com/music/song.mp3', $result->url);
        $this->assertSame('aud_567890', $result->fileId);
        $this->assertSame('favorite_song.mp3', $result->fileName);
        $this->assertSame(3145728, $result->size);
    }

    public function testConstructWithSmallFile(): void
    {
        // Act
        $result = new FileUploadResult(
            url: 'https://files.example.com/small.txt',
            fileId: 'txt_111222',
            fileName: 'note.txt',
            size: 1024
        );

        // Assert
        $this->assertSame('https://files.example.com/small.txt', $result->url);
        $this->assertSame('txt_111222', $result->fileId);
        $this->assertSame('note.txt', $result->fileName);
        $this->assertSame(1024, $result->size);
    }

    public function testConstructWithLargeFile(): void
    {
        // Act
        $result = new FileUploadResult(
            url: 'https://storage.example.com/large/archive.zip',
            fileId: 'zip_333444',
            fileName: 'project_backup.zip',
            size: 104857600
        );

        // Assert
        $this->assertSame('https://storage.example.com/large/archive.zip', $result->url);
        $this->assertSame('zip_333444', $result->fileId);
        $this->assertSame('project_backup.zip', $result->fileName);
        $this->assertSame(104857600, $result->size);
    }

    public function testConstructWithEmptyFileName(): void
    {
        // Act
        $result = new FileUploadResult(
            url: 'https://temp.example.com/unnamed',
            fileId: 'tmp_555666',
            fileName: '',
            size: 0
        );

        // Assert
        $this->assertSame('https://temp.example.com/unnamed', $result->url);
        $this->assertSame('tmp_555666', $result->fileId);
        $this->assertSame('', $result->fileName);
        $this->assertSame(0, $result->size);
    }

    public function testConstructWithZeroSize(): void
    {
        // Act
        $result = new FileUploadResult(
            url: 'https://example.com/empty.txt',
            fileId: 'empty_777888',
            fileName: 'empty.txt',
            size: 0
        );

        // Assert
        $this->assertSame('https://example.com/empty.txt', $result->url);
        $this->assertSame('empty_777888', $result->fileId);
        $this->assertSame('empty.txt', $result->fileName);
        $this->assertSame(0, $result->size);
    }

    public function testConstructWithSpecialCharactersInFileName(): void
    {
        // Act
        $result = new FileUploadResult(
            url: 'https://files.example.com/special/file.jpg',
            fileId: 'special_999000',
            fileName: '文件名-with-特殊字符_123.jpg',
            size: 1500000
        );

        // Assert
        $this->assertSame('https://files.example.com/special/file.jpg', $result->url);
        $this->assertSame('special_999000', $result->fileId);
        $this->assertSame('文件名-with-特殊字符_123.jpg', $result->fileName);
        $this->assertSame(1500000, $result->size);
    }
}
