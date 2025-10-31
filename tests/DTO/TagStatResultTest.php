<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\TagStatResult;

/**
 * @internal
 */
#[CoversClass(TagStatResult::class)]
final class TagStatResultTest extends RequestTestCase
{
    public function testConstruct(): void
    {
        $tagId = 'stat_tag_001';
        $tagName = '统计标签';
        $contactCount = 100;
        $createTime = 1640995200; // 2022-01-01 00:00:00

        $tagStatResult = new TagStatResult($tagId, $tagName, $contactCount, $createTime);

        $this->assertSame($tagId, $tagStatResult->tagId);
        $this->assertSame($tagName, $tagStatResult->tagName);
        $this->assertSame($contactCount, $tagStatResult->contactCount);
        $this->assertSame($createTime, $tagStatResult->createTime);
    }

    public function testGetFormattedCreateTime(): void
    {
        $createTime = 1640995200; // 2022-01-01 00:00:00
        $tagStatResult = new TagStatResult('stat_tag_001', '统计标签', 100, $createTime);

        $expected = date('Y-m-d H:i:s', $createTime);
        $this->assertSame($expected, $tagStatResult->getFormattedCreateTime());
    }

    public function testGetFormattedCreateTimeWithDifferentTimestamp(): void
    {
        $createTime = 1609459200; // 2021-01-01 00:00:00
        $tagStatResult = new TagStatResult('stat_tag_002', '另一个统计标签', 50, $createTime);

        $this->assertSame('2021-01-01 00:00:00', $tagStatResult->getFormattedCreateTime());
    }

    public function testConstructWithZeroContactCount(): void
    {
        $tagStatResult = new TagStatResult('stat_tag_003', '无联系人标签', 0, 1640995200);

        $this->assertSame('stat_tag_003', $tagStatResult->tagId);
        $this->assertSame('无联系人标签', $tagStatResult->tagName);
        $this->assertSame(0, $tagStatResult->contactCount);
        $this->assertSame(1640995200, $tagStatResult->createTime);
    }

    public function testConstructWithLargeContactCount(): void
    {
        $largeCount = 1000000;
        $tagStatResult = new TagStatResult('stat_tag_004', '大型统计标签', $largeCount, 1640995200);

        $this->assertSame($largeCount, $tagStatResult->contactCount);
    }

    public function testGetFormattedCreateTimeWithCurrentTime(): void
    {
        $currentTime = time();
        $tagStatResult = new TagStatResult('stat_tag_005', '当前时间标签', 75, $currentTime);

        $expected = date('Y-m-d H:i:s', $currentTime);
        $this->assertSame($expected, $tagStatResult->getFormattedCreateTime());
    }
}
