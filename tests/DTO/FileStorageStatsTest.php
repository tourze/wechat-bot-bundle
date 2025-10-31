<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\FileStorageStats;

/**
 * @internal
 */
#[CoversClass(FileStorageStats::class)]
final class FileStorageStatsTest extends RequestTestCase
{
    public function testConstruct(): void
    {
        $totalFiles = 100;
        $totalSize = 1048576; // 1MB
        $typeStats = ['jpg' => 50, 'png' => 30, 'txt' => 20];

        $stats = new FileStorageStats($totalFiles, $totalSize, $typeStats);

        $this->assertSame($totalFiles, $stats->totalFiles);
        $this->assertSame($totalSize, $stats->totalSize);
        $this->assertSame($typeStats, $stats->typeStats);
    }

    public function testGetFormattedTotalSizeInBytes(): void
    {
        $stats = new FileStorageStats(1, 512, []);
        $this->assertSame('512.00 B', $stats->getFormattedTotalSize());
    }

    public function testGetFormattedTotalSizeInKilobytes(): void
    {
        $stats = new FileStorageStats(1, 1536, []); // 1.5KB
        $this->assertSame('1.50 KB', $stats->getFormattedTotalSize());
    }

    public function testGetFormattedTotalSizeInMegabytes(): void
    {
        $stats = new FileStorageStats(1, 1572864, []); // 1.5MB
        $this->assertSame('1.50 MB', $stats->getFormattedTotalSize());
    }

    public function testGetFormattedTotalSizeInGigabytes(): void
    {
        $stats = new FileStorageStats(1, 1610612736, []); // 1.5GB
        $this->assertSame('1.50 GB', $stats->getFormattedTotalSize());
    }

    public function testGetFormattedTotalSizeExactlyOneKilobyte(): void
    {
        $stats = new FileStorageStats(1, 1024, []);
        $this->assertSame('1.00 KB', $stats->getFormattedTotalSize());
    }

    public function testGetFormattedTotalSizeZero(): void
    {
        $stats = new FileStorageStats(0, 0, []);
        $this->assertSame('0.00 B', $stats->getFormattedTotalSize());
    }

    public function testGetFormattedTotalSizeVeryLarge(): void
    {
        $stats = new FileStorageStats(1, 5368709120, []); // 5GB
        $this->assertSame('5.00 GB', $stats->getFormattedTotalSize());
    }
}
