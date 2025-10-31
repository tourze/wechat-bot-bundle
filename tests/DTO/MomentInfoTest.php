<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\MomentInfo;

/**
 * MomentInfo DTO 单元测试
 *
 * @internal
 */
#[CoversClass(MomentInfo::class)]
final class MomentInfoTest extends RequestTestCase
{
    public function testConstructWithValidParametersSetsPropertiesCorrectly(): void
    {
        // Arrange
        $momentId = 'moment_123456789';
        $wxid = 'user_wxid_123';
        $nickname = 'Test User';
        $content = 'This is a test moment content';
        $type = 1; // 文本类型
        $createTime = 1640995200; // 2022-01-01 00:00:00
        $images = ['image1.jpg', 'image2.jpg'];
        $videoUrl = 'https://example.com/video.mp4';
        $linkTitle = 'Test Link';
        $linkDesc = 'Test Link Description';
        $linkUrl = 'https://example.com/link';
        $likeCount = 10;
        $commentCount = 5;
        $likes = ['user1' => 'User 1', 'user2' => 'User 2'];
        $comments = ['comment1' => 'Comment 1', 'comment2' => 'Comment 2'];

        // Act
        $result = new MomentInfo(
            momentId: $momentId,
            wxid: $wxid,
            nickname: $nickname,
            content: $content,
            type: $type,
            createTime: $createTime,
            images: $images,
            videoUrl: $videoUrl,
            linkTitle: $linkTitle,
            linkDesc: $linkDesc,
            linkUrl: $linkUrl,
            likeCount: $likeCount,
            commentCount: $commentCount,
            likes: $likes,
            comments: $comments
        );

        // Assert
        $this->assertSame($momentId, $result->momentId);
        $this->assertSame($wxid, $result->wxid);
        $this->assertSame($nickname, $result->nickname);
        $this->assertSame($content, $result->content);
        $this->assertSame($type, $result->type);
        $this->assertSame($createTime, $result->createTime);
        $this->assertSame($images, $result->images);
        $this->assertSame($videoUrl, $result->videoUrl);
        $this->assertSame($linkTitle, $result->linkTitle);
        $this->assertSame($linkDesc, $result->linkDesc);
        $this->assertSame($linkUrl, $result->linkUrl);
        $this->assertSame($likeCount, $result->likeCount);
        $this->assertSame($commentCount, $result->commentCount);
        $this->assertSame($likes, $result->likes);
        $this->assertSame($comments, $result->comments);
    }

    public function testGetTypeDescriptionWithTextType(): void
    {
        // Act
        $result = new MomentInfo(
            momentId: 'text_moment',
            wxid: 'user_wxid',
            nickname: 'User',
            content: 'Text content',
            type: 1,
            createTime: 1640995200,
            images: [],
            videoUrl: '',
            linkTitle: '',
            linkDesc: '',
            linkUrl: '',
            likeCount: 0,
            commentCount: 0,
            likes: [],
            comments: []
        );

        // Assert
        $this->assertSame('文本', $result->getTypeDescription());
    }

    public function testGetTypeDescriptionWithImageType(): void
    {
        // Act
        $result = new MomentInfo(
            momentId: 'image_moment',
            wxid: 'user_wxid',
            nickname: 'User',
            content: 'Image content',
            type: 2,
            createTime: 1640995200,
            images: ['image1.jpg', 'image2.jpg'],
            videoUrl: '',
            linkTitle: '',
            linkDesc: '',
            linkUrl: '',
            likeCount: 0,
            commentCount: 0,
            likes: [],
            comments: []
        );

        // Assert
        $this->assertSame('图片', $result->getTypeDescription());
    }

    public function testGetTypeDescriptionWithVideoType(): void
    {
        // Act
        $result = new MomentInfo(
            momentId: 'video_moment',
            wxid: 'user_wxid',
            nickname: 'User',
            content: 'Video content',
            type: 3,
            createTime: 1640995200,
            images: [],
            videoUrl: 'https://example.com/video.mp4',
            linkTitle: '',
            linkDesc: '',
            linkUrl: '',
            likeCount: 0,
            commentCount: 0,
            likes: [],
            comments: []
        );

        // Assert
        $this->assertSame('视频', $result->getTypeDescription());
    }

    public function testGetTypeDescriptionWithLinkType(): void
    {
        // Act
        $result = new MomentInfo(
            momentId: 'link_moment',
            wxid: 'user_wxid',
            nickname: 'User',
            content: 'Link content',
            type: 4,
            createTime: 1640995200,
            images: [],
            videoUrl: '',
            linkTitle: 'Link Title',
            linkDesc: 'Link Description',
            linkUrl: 'https://example.com/link',
            likeCount: 0,
            commentCount: 0,
            likes: [],
            comments: []
        );

        // Assert
        $this->assertSame('链接', $result->getTypeDescription());
    }

    public function testGetTypeDescriptionWithUnknownType(): void
    {
        // Act
        $result = new MomentInfo(
            momentId: 'unknown_moment',
            wxid: 'user_wxid',
            nickname: 'User',
            content: 'Unknown content',
            type: 999,
            createTime: 1640995200,
            images: [],
            videoUrl: '',
            linkTitle: '',
            linkDesc: '',
            linkUrl: '',
            likeCount: 0,
            commentCount: 0,
            likes: [],
            comments: []
        );

        // Assert
        $this->assertSame('未知', $result->getTypeDescription());
    }

    public function testHasImagesWithImages(): void
    {
        // Act
        $result = new MomentInfo(
            momentId: 'moment_with_images',
            wxid: 'user_wxid',
            nickname: 'User',
            content: 'Content with images',
            type: 2,
            createTime: 1640995200,
            images: ['image1.jpg', 'image2.jpg', 'image3.jpg'],
            videoUrl: '',
            linkTitle: '',
            linkDesc: '',
            linkUrl: '',
            likeCount: 0,
            commentCount: 0,
            likes: [],
            comments: []
        );

        // Assert
        $this->assertTrue($result->hasImages());
    }

    public function testHasImagesWithoutImages(): void
    {
        // Act
        $result = new MomentInfo(
            momentId: 'moment_without_images',
            wxid: 'user_wxid',
            nickname: 'User',
            content: 'Content without images',
            type: 1,
            createTime: 1640995200,
            images: [],
            videoUrl: '',
            linkTitle: '',
            linkDesc: '',
            linkUrl: '',
            likeCount: 0,
            commentCount: 0,
            likes: [],
            comments: []
        );

        // Assert
        $this->assertFalse($result->hasImages());
    }

    public function testHasVideoWithVideo(): void
    {
        // Act
        $result = new MomentInfo(
            momentId: 'moment_with_video',
            wxid: 'user_wxid',
            nickname: 'User',
            content: 'Content with video',
            type: 3,
            createTime: 1640995200,
            images: [],
            videoUrl: 'https://example.com/video.mp4',
            linkTitle: '',
            linkDesc: '',
            linkUrl: '',
            likeCount: 0,
            commentCount: 0,
            likes: [],
            comments: []
        );

        // Assert
        $this->assertTrue($result->hasVideo());
    }

    public function testHasVideoWithoutVideo(): void
    {
        // Act
        $result = new MomentInfo(
            momentId: 'moment_without_video',
            wxid: 'user_wxid',
            nickname: 'User',
            content: 'Content without video',
            type: 1,
            createTime: 1640995200,
            images: [],
            videoUrl: '',
            linkTitle: '',
            linkDesc: '',
            linkUrl: '',
            likeCount: 0,
            commentCount: 0,
            likes: [],
            comments: []
        );

        // Assert
        $this->assertFalse($result->hasVideo());
    }

    public function testIsLinkWithLinkType(): void
    {
        // Act
        $result = new MomentInfo(
            momentId: 'link_moment',
            wxid: 'user_wxid',
            nickname: 'User',
            content: 'Link content',
            type: 4,
            createTime: 1640995200,
            images: [],
            videoUrl: '',
            linkTitle: 'Link Title',
            linkDesc: 'Link Description',
            linkUrl: 'https://example.com/link',
            likeCount: 0,
            commentCount: 0,
            likes: [],
            comments: []
        );

        // Assert
        $this->assertTrue($result->isLink());
    }

    public function testIsLinkWithLinkTypeButNoUrl(): void
    {
        // Act
        $result = new MomentInfo(
            momentId: 'link_moment_no_url',
            wxid: 'user_wxid',
            nickname: 'User',
            content: 'Link content',
            type: 4,
            createTime: 1640995200,
            images: [],
            videoUrl: '',
            linkTitle: 'Link Title',
            linkDesc: 'Link Description',
            linkUrl: '',
            likeCount: 0,
            commentCount: 0,
            likes: [],
            comments: []
        );

        // Assert
        $this->assertFalse($result->isLink());
    }

    public function testIsLinkWithNonLinkType(): void
    {
        // Act
        $result = new MomentInfo(
            momentId: 'text_moment',
            wxid: 'user_wxid',
            nickname: 'User',
            content: 'Text content',
            type: 1,
            createTime: 1640995200,
            images: [],
            videoUrl: '',
            linkTitle: '',
            linkDesc: '',
            linkUrl: 'https://example.com/link',
            likeCount: 0,
            commentCount: 0,
            likes: [],
            comments: []
        );

        // Assert
        $this->assertFalse($result->isLink());
    }

    public function testGetFormattedCreateTime(): void
    {
        // Arrange
        $createTime = 1640995200; // 2022-01-01 00:00:00

        // Act
        $result = new MomentInfo(
            momentId: 'moment_formatted_time',
            wxid: 'user_wxid',
            nickname: 'User',
            content: 'Content',
            type: 1,
            createTime: $createTime,
            images: [],
            videoUrl: '',
            linkTitle: '',
            linkDesc: '',
            linkUrl: '',
            likeCount: 0,
            commentCount: 0,
            likes: [],
            comments: []
        );

        // Assert
        $expected = date('Y-m-d H:i:s', $createTime);
        $this->assertSame($expected, $result->getFormattedCreateTime());
    }

    public function testConstructWithEmptyArrays(): void
    {
        // Act
        $result = new MomentInfo(
            momentId: 'empty_arrays_moment',
            wxid: 'user_wxid',
            nickname: 'User',
            content: 'Content',
            type: 1,
            createTime: 1640995200,
            images: [],
            videoUrl: '',
            linkTitle: '',
            linkDesc: '',
            linkUrl: '',
            likeCount: 0,
            commentCount: 0,
            likes: [],
            comments: []
        );

        // Assert
        $this->assertSame([], $result->images);
        $this->assertSame([], $result->likes);
        $this->assertSame([], $result->comments);
        $this->assertEmpty($result->images);
        $this->assertEmpty($result->likes);
        $this->assertEmpty($result->comments);
    }

    public function testConstructWithMultipleLikesAndComments(): void
    {
        // Arrange
        $likes = ['user1' => 'User 1', 'user2' => 'User 2', 'user3' => 'User 3', 'user4' => 'User 4', 'user5' => 'User 5'];
        $comments = ['comment1' => 'Great post!', 'comment2' => 'Nice picture', 'comment3' => 'Thanks for sharing', 'comment4' => 'Amazing'];

        // Act
        $result = new MomentInfo(
            momentId: 'popular_moment',
            wxid: 'user_wxid',
            nickname: 'Popular User',
            content: 'Popular content',
            type: 2,
            createTime: 1640995200,
            images: ['popular1.jpg', 'popular2.jpg'],
            videoUrl: '',
            linkTitle: '',
            linkDesc: '',
            linkUrl: '',
            likeCount: 5,
            commentCount: 4,
            likes: $likes,
            comments: $comments
        );

        // Assert
        $this->assertSame($likes, $result->likes);
        $this->assertSame($comments, $result->comments);
        $this->assertCount(5, $result->likes);
        $this->assertCount(4, $result->comments);
        $this->assertSame(5, $result->likeCount);
        $this->assertSame(4, $result->commentCount);
    }
}
