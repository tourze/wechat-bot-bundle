<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\TagResult;

/**
 * @internal
 */
#[CoversClass(TagResult::class)]
final class TagResultTest extends RequestTestCase
{
    public function testConstruct(): void
    {
        $tagId = 'tag_001';
        $tagName = '测试标签';
        $memberCount = 42;
        $createTime = 1640995200; // 2022-01-01 00:00:00

        $tagResult = new TagResult($tagId, $tagName, $memberCount, $createTime);

        $this->assertSame($tagId, $tagResult->tagId);
        $this->assertSame($tagName, $tagResult->tagName);
        $this->assertSame($memberCount, $tagResult->memberCount);
        $this->assertSame($createTime, $tagResult->createTime);
    }

    public function testGetFormattedCreateTime(): void
    {
        $createTime = 1640995200; // 2022-01-01 00:00:00
        $tagResult = new TagResult('tag_001', '测试标签', 42, $createTime);

        $expected = date('Y-m-d H:i:s', $createTime);
        $this->assertSame($expected, $tagResult->getFormattedCreateTime());
    }

    public function testGetFormattedCreateTimeWithDifferentTimestamp(): void
    {
        $createTime = 1609459200; // 2021-01-01 00:00:00
        $tagResult = new TagResult('tag_002', '另一个标签', 10, $createTime);

        $this->assertSame('2021-01-01 00:00:00', $tagResult->getFormattedCreateTime());
    }

    public function testConstructWithZeroMemberCount(): void
    {
        $tagResult = new TagResult('tag_003', '空标签', 0, 1640995200);

        $this->assertSame('tag_003', $tagResult->tagId);
        $this->assertSame('空标签', $tagResult->tagName);
        $this->assertSame(0, $tagResult->memberCount);
        $this->assertSame(1640995200, $tagResult->createTime);
    }

    public function testConstructWithLargeMemberCount(): void
    {
        $largeCount = 999999;
        $tagResult = new TagResult('tag_004', '大型标签', $largeCount, 1640995200);

        $this->assertSame($largeCount, $tagResult->memberCount);
    }
}
