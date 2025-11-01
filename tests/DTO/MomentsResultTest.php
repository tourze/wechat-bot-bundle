<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\MomentsResult;

/**
 * MomentsResult DTO 单元测试
 *
 * @internal
 */
#[CoversClass(MomentsResult::class)]
final class MomentsResultTest extends RequestTestCase
{
    public function testConstructWithValidParametersSetsPropertiesCorrectly(): void
    {
        // Arrange
        $moments = [
            ['momentId' => 'moment1', 'content' => 'Content 1'],
            ['momentId' => 'moment2', 'content' => 'Content 2'],
            ['momentId' => 'moment3', 'content' => 'Content 3'],
        ];
        $nextMaxId = 'next_max_id_123';
        $hasMore = true;

        // Act
        $result = new MomentsResult(
            moments: $moments,
            nextMaxId: $nextMaxId,
            hasMore: $hasMore
        );

        // Assert
        $this->assertSame($moments, $result->moments);
        $this->assertSame($nextMaxId, $result->nextMaxId);
        $this->assertSame($hasMore, $result->hasMore);
    }

    public function testConstructWithEmptyMoments(): void
    {
        // Act
        $result = new MomentsResult(
            moments: [],
            nextMaxId: '',
            hasMore: false
        );

        // Assert
        $this->assertSame([], $result->moments);
        $this->assertEmpty($result->moments);
        $this->assertSame('', $result->nextMaxId);
        $this->assertFalse($result->hasMore);
    }

    public function testConstructWithSingleMoment(): void
    {
        // Arrange
        $moments = [
            ['momentId' => 'single_moment', 'content' => 'Only one moment'],
        ];

        // Act
        $result = new MomentsResult(
            moments: $moments,
            nextMaxId: 'single_next_id',
            hasMore: false
        );

        // Assert
        $this->assertSame($moments, $result->moments);
        $this->assertCount(1, $result->moments);
        $this->assertSame('single_next_id', $result->nextMaxId);
        $this->assertFalse($result->hasMore);
    }

    public function testConstructWithMultipleMoments(): void
    {
        // Arrange
        $moments = [
            ['momentId' => 'moment1', 'content' => 'First moment', 'type' => 1],
            ['momentId' => 'moment2', 'content' => 'Second moment', 'type' => 2],
            ['momentId' => 'moment3', 'content' => 'Third moment', 'type' => 3],
            ['momentId' => 'moment4', 'content' => 'Fourth moment', 'type' => 1],
            ['momentId' => 'moment5', 'content' => 'Fifth moment', 'type' => 2],
        ];

        // Act
        $result = new MomentsResult(
            moments: $moments,
            nextMaxId: 'multiple_next_id',
            hasMore: true
        );

        // Assert
        $this->assertSame($moments, $result->moments);
        $this->assertCount(5, $result->moments);
        $this->assertSame('multiple_next_id', $result->nextMaxId);
        $this->assertTrue($result->hasMore);
    }

    public function testConstructWithHasMoreTrue(): void
    {
        // Arrange
        $moments = [
            ['momentId' => 'moment1', 'content' => 'Content 1'],
            ['momentId' => 'moment2', 'content' => 'Content 2'],
        ];

        // Act
        $result = new MomentsResult(
            moments: $moments,
            nextMaxId: 'has_more_next_id',
            hasMore: true
        );

        // Assert
        $this->assertTrue($result->hasMore);
        $this->assertSame('has_more_next_id', $result->nextMaxId);
        $this->assertNotEmpty($result->nextMaxId);
    }

    public function testConstructWithHasMoreFalse(): void
    {
        // Arrange
        $moments = [
            ['momentId' => 'last_moment', 'content' => 'Last content'],
        ];

        // Act
        $result = new MomentsResult(
            moments: $moments,
            nextMaxId: '',
            hasMore: false
        );

        // Assert
        $this->assertFalse($result->hasMore);
        $this->assertSame('', $result->nextMaxId);
        $this->assertEmpty($result->nextMaxId);
    }

    public function testConstructWithComplexMomentData(): void
    {
        // Arrange
        $moments = [
            [
                'momentId' => 'complex_moment_1',
                'content' => 'Complex moment with images',
                'type' => 2,
                'images' => ['image1.jpg', 'image2.jpg'],
                'likeCount' => 5,
                'commentCount' => 3,
                'createTime' => 1640995200,
            ],
            [
                'momentId' => 'complex_moment_2',
                'content' => 'Video moment',
                'type' => 3,
                'videoUrl' => 'https://example.com/video.mp4',
                'likeCount' => 10,
                'commentCount' => 7,
                'createTime' => 1641081600,
            ],
            [
                'momentId' => 'complex_moment_3',
                'content' => 'Link moment',
                'type' => 4,
                'linkTitle' => 'Interesting Article',
                'linkUrl' => 'https://example.com/article',
                'likeCount' => 2,
                'commentCount' => 1,
                'createTime' => 1641168000,
            ],
        ];

        // Act
        $result = new MomentsResult(
            moments: $moments,
            nextMaxId: 'complex_next_id',
            hasMore: true
        );

        // Assert
        $this->assertSame($moments, $result->moments);
        $this->assertCount(3, $result->moments);
        $this->assertSame('complex_next_id', $result->nextMaxId);
        $this->assertTrue($result->hasMore);

        // Check specific moment data
        $this->assertSame('complex_moment_1', $result->moments[0]['momentId']);
        $this->assertSame('Video moment', $result->moments[1]['content']);
        $this->assertSame(4, $result->moments[2]['type']);
    }

    public function testConstructWithEmptyNextMaxId(): void
    {
        // Arrange
        $moments = [
            ['momentId' => 'moment1', 'content' => 'Content 1'],
        ];

        // Act
        $result = new MomentsResult(
            moments: $moments,
            nextMaxId: '',
            hasMore: false
        );

        // Assert
        $this->assertSame('', $result->nextMaxId);
        $this->assertEmpty($result->nextMaxId);
    }

    public function testConstructWithLongNextMaxId(): void
    {
        // Arrange
        $longNextMaxId = 'very_long_next_max_id_with_many_characters_to_test_string_handling_123456789';

        // Act
        $result = new MomentsResult(
            moments: [['momentId' => 'moment1', 'content' => 'Content 1']],
            nextMaxId: $longNextMaxId,
            hasMore: true
        );

        // Assert
        $this->assertSame($longNextMaxId, $result->nextMaxId);
        $this->assertStringContainsString('very_long_next_max_id', $result->nextMaxId);
        $this->assertStringContainsString('123456789', $result->nextMaxId);
    }

    public function testConstructWithMixedMomentTypes(): void
    {
        // Arrange
        $moments = [
            ['momentId' => 'text_moment', 'content' => 'Text content', 'type' => 1],
            ['momentId' => 'image_moment', 'content' => 'Image content', 'type' => 2, 'images' => ['image.jpg']],
            ['momentId' => 'video_moment', 'content' => 'Video content', 'type' => 3, 'videoUrl' => 'video.mp4'],
            ['momentId' => 'link_moment', 'content' => 'Link content', 'type' => 4, 'linkUrl' => 'http://link.com'],
        ];

        // Act
        $result = new MomentsResult(
            moments: $moments,
            nextMaxId: 'mixed_next_id',
            hasMore: true
        );

        // Assert
        $this->assertSame($moments, $result->moments);
        $this->assertCount(4, $result->moments);
        $this->assertSame('mixed_next_id', $result->nextMaxId);
        $this->assertTrue($result->hasMore);

        // Verify different moment types
        $this->assertSame(1, $result->moments[0]['type']);
        $this->assertSame(2, $result->moments[1]['type']);
        $this->assertSame(3, $result->moments[2]['type']);
        $this->assertSame(4, $result->moments[3]['type']);
    }

    public function testConstructWithNumericNextMaxId(): void
    {
        // Act
        $result = new MomentsResult(
            moments: [['momentId' => 'moment1', 'content' => 'Content 1']],
            nextMaxId: '123456789',
            hasMore: true
        );

        // Assert
        $this->assertSame('123456789', $result->nextMaxId);
        $this->assertIsString($result->nextMaxId);
    }

    public function testConstructPreservesOriginalMomentArrayStructure(): void
    {
        // Arrange
        $originalMoments = [
            [
                'momentId' => 'preserve_test',
                'content' => 'Original content',
                'customField' => 'custom_value',
                'nestedData' => ['key1' => 'value1', 'key2' => 'value2'],
            ],
        ];

        // Act
        $result = new MomentsResult(
            moments: $originalMoments,
            nextMaxId: 'preserve_next_id',
            hasMore: false
        );

        // Assert
        $this->assertSame($originalMoments, $result->moments);
        $this->assertSame('custom_value', $result->moments[0]['customField']);
        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $result->moments[0]['nestedData']);
    }
}
