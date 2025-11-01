<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\FileDownloadResult;

/**
 * FileDownloadResult DTO 单元测试
 *
 * @internal
 */
#[CoversClass(FileDownloadResult::class)]
final class FileDownloadResultTest extends RequestTestCase
{
    public function testConstructWithValidParametersSetsPropertiesCorrectly(): void
    {
        // Arrange
        $localPath = '/tmp/downloaded_file.jpg';
        $originalName = 'original_photo.jpg';
        $size = 1024000;
        $mimeType = 'image/jpeg';
        $originalUrl = 'https://example.com/file.jpg';
        $duration = 30;

        // Act
        $result = new FileDownloadResult(
            localPath: $localPath,
            originalName: $originalName,
            size: $size,
            mimeType: $mimeType,
            originalUrl: $originalUrl,
            duration: $duration
        );

        // Assert
        $this->assertSame($localPath, $result->localPath);
        $this->assertSame($originalName, $result->originalName);
        $this->assertSame($size, $result->size);
        $this->assertSame($mimeType, $result->mimeType);
        $this->assertSame($originalUrl, $result->originalUrl);
        $this->assertSame($duration, $result->duration);
    }

    public function testConstructWithDefaultDuration(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/file.txt',
            originalName: 'file.txt',
            size: 1024,
            mimeType: 'text/plain',
            originalUrl: 'https://example.com/file.txt'
        );

        // Assert
        $this->assertSame(0, $result->duration);
    }

    public function testGetExtensionWithJpgFile(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/photo.jpg',
            originalName: 'photo.JPG',
            size: 500000,
            mimeType: 'image/jpeg',
            originalUrl: 'https://example.com/photo.JPG'
        );

        // Assert
        $this->assertSame('jpg', $result->getExtension());
    }

    public function testGetExtensionWithPngFile(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/image.png',
            originalName: 'image.PNG',
            size: 300000,
            mimeType: 'image/png',
            originalUrl: 'https://example.com/image.PNG'
        );

        // Assert
        $this->assertSame('png', $result->getExtension());
    }

    public function testGetExtensionWithNoExtension(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/file',
            originalName: 'file',
            size: 1000,
            mimeType: 'application/octet-stream',
            originalUrl: 'https://example.com/file'
        );

        // Assert
        $this->assertSame('', $result->getExtension());
    }

    public function testIsImageWithJpegMimeType(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/photo.jpg',
            originalName: 'photo.jpg',
            size: 500000,
            mimeType: 'image/jpeg',
            originalUrl: 'https://example.com/photo.jpg'
        );

        // Assert
        $this->assertTrue($result->isImage());
    }

    public function testIsImageWithPngMimeType(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/image.png',
            originalName: 'image.png',
            size: 300000,
            mimeType: 'image/png',
            originalUrl: 'https://example.com/image.png'
        );

        // Assert
        $this->assertTrue($result->isImage());
    }

    public function testIsImageWithNonImageMimeType(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/document.pdf',
            originalName: 'document.pdf',
            size: 200000,
            mimeType: 'application/pdf',
            originalUrl: 'https://example.com/document.pdf'
        );

        // Assert
        $this->assertFalse($result->isImage());
    }

    public function testIsVideoWithMp4MimeType(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/video.mp4',
            originalName: 'video.mp4',
            size: 5000000,
            mimeType: 'video/mp4',
            originalUrl: 'https://example.com/video.mp4',
            duration: 120
        );

        // Assert
        $this->assertTrue($result->isVideo());
    }

    public function testIsVideoWithAviMimeType(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/movie.avi',
            originalName: 'movie.avi',
            size: 10000000,
            mimeType: 'video/x-msvideo',
            originalUrl: 'https://example.com/movie.avi',
            duration: 180
        );

        // Assert
        $this->assertTrue($result->isVideo());
    }

    public function testIsVideoWithNonVideoMimeType(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/audio.mp3',
            originalName: 'audio.mp3',
            size: 3000000,
            mimeType: 'audio/mpeg',
            originalUrl: 'https://example.com/audio.mp3',
            duration: 240
        );

        // Assert
        $this->assertFalse($result->isVideo());
    }

    public function testIsAudioWithMp3MimeType(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/music.mp3',
            originalName: 'music.mp3',
            size: 4000000,
            mimeType: 'audio/mpeg',
            originalUrl: 'https://example.com/music.mp3',
            duration: 180
        );

        // Assert
        $this->assertTrue($result->isAudio());
    }

    public function testIsAudioWithWavMimeType(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/sound.wav',
            originalName: 'sound.wav',
            size: 2000000,
            mimeType: 'audio/wav',
            originalUrl: 'https://example.com/sound.wav',
            duration: 60
        );

        // Assert
        $this->assertTrue($result->isAudio());
    }

    public function testIsAudioWithNonAudioMimeType(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/document.txt',
            originalName: 'document.txt',
            size: 1000,
            mimeType: 'text/plain',
            originalUrl: 'https://example.com/document.txt'
        );

        // Assert
        $this->assertFalse($result->isAudio());
    }

    public function testGetFormattedSizeWithBytesOnly(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/small.txt',
            originalName: 'small.txt',
            size: 512,
            mimeType: 'text/plain',
            originalUrl: 'https://example.com/small.txt'
        );

        // Assert
        $this->assertSame('512.00 B', $result->getFormattedSize());
    }

    public function testGetFormattedSizeWithKilobytes(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/medium.txt',
            originalName: 'medium.txt',
            size: 1536, // 1.5 KB
            mimeType: 'text/plain',
            originalUrl: 'https://example.com/medium.txt'
        );

        // Assert
        $this->assertSame('1.50 KB', $result->getFormattedSize());
    }

    public function testGetFormattedSizeWithMegabytes(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/large.jpg',
            originalName: 'large.jpg',
            size: 2097152, // 2 MB
            mimeType: 'image/jpeg',
            originalUrl: 'https://example.com/large.jpg'
        );

        // Assert
        $this->assertSame('2.00 MB', $result->getFormattedSize());
    }

    public function testGetFormattedSizeWithGigabytes(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/huge.mp4',
            originalName: 'huge.mp4',
            size: 3221225472, // 3 GB
            mimeType: 'video/mp4',
            originalUrl: 'https://example.com/huge.mp4',
            duration: 7200
        );

        // Assert
        $this->assertSame('3.00 GB', $result->getFormattedSize());
    }

    public function testGetFormattedSizeWithFractionalValues(): void
    {
        // Act
        $result = new FileDownloadResult(
            localPath: '/tmp/file.pdf',
            originalName: 'file.pdf',
            size: 1572864, // 1.5 MB
            mimeType: 'application/pdf',
            originalUrl: 'https://example.com/file.pdf'
        );

        // Assert
        $this->assertSame('1.50 MB', $result->getFormattedSize());
    }
}
