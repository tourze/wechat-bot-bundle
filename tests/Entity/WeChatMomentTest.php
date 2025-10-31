<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatMoment;

/**
 * WeChatMoment 实体单元测试
 *
 * @internal
 */
#[CoversClass(WeChatMoment::class)]
final class WeChatMomentTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new WeChatMoment();
    }

    /**
     * 提供 WeChatMoment 实体的属性数据进行自动测试。
     *
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'momentId' => ['momentId', 'test_moment_123'],
            'authorWxid' => ['authorWxid', 'test_author_wxid'],
            'authorNickname' => ['authorNickname', 'Test Author'],
            'authorAvatar' => ['authorAvatar', 'http://example.com/avatar.jpg'],
            'momentType' => ['momentType', 'text'],
            'textContent' => ['textContent', 'Test content'],
            'images' => ['images', ['http://example.com/image1.jpg']],
            'video' => ['video', ['url' => 'http://example.com/video.mp4']],
            'link' => ['link', ['title' => 'Test Link', 'url' => 'http://example.com']],
            'location' => ['location', 'Test Location'],
            'likeCount' => ['likeCount', 10],
            'commentCount' => ['commentCount', 5],
            'likeUsers' => ['likeUsers', ['user1', 'user2']],
            'comments' => ['comments', [['user' => 'user1', 'content' => 'test comment']]],
        ];
    }

    public function testConstructorSetsDefaultValues(): void
    {
        $moment = new WeChatMoment();

        // 验证默认值
        $this->assertNull($moment->getId());
        $this->assertNull($moment->getAccount());
        $this->assertNull($moment->getMomentId());
        $this->assertNull($moment->getAuthorWxid());
        $this->assertNull($moment->getAuthorNickname());
        $this->assertNull($moment->getAuthorAvatar());
        $this->assertNull($moment->getMomentType());
        $this->assertNull($moment->getTextContent());
        $this->assertNull($moment->getImages());
        $this->assertNull($moment->getVideo());
        $this->assertNull($moment->getLink());
        $this->assertNull($moment->getLocation());
        $this->assertEquals(0, $moment->getLikeCount());
        $this->assertEquals(0, $moment->getCommentCount());
        $this->assertFalse($moment->isLiked());
        $this->assertNull($moment->getPublishTime());
        $this->assertNull($moment->getLikeUsers());
        $this->assertNull($moment->getComments());
        $this->assertNull($moment->getRawData());
        $this->assertTrue($moment->isValid());
        $this->assertNull($moment->getRemark());
    }

    public function testSettersAndGettersWorkCorrectly(): void
    {
        $moment = new WeChatMoment();
        $account = new WeChatAccount();

        // 测试账号
        $moment->setAccount($account);
        $this->assertSame($account, $moment->getAccount());

        // 测试动态ID
        $momentId = 'moment_123';
        $moment->setMomentId($momentId);
        $this->assertEquals($momentId, $moment->getMomentId());

        // 测试作者微信ID
        $authorWxid = 'author_wxid_123';
        $moment->setAuthorWxid($authorWxid);
        $this->assertEquals($authorWxid, $moment->getAuthorWxid());

        // 测试作者昵称
        $authorNickname = 'Author Name';
        $moment->setAuthorNickname($authorNickname);
        $this->assertEquals($authorNickname, $moment->getAuthorNickname());

        // 测试作者头像
        $authorAvatar = 'https://example.com/avatar.jpg';
        $moment->setAuthorAvatar($authorAvatar);
        $this->assertEquals($authorAvatar, $moment->getAuthorAvatar());

        // 测试动态类型
        $momentType = 'text';
        $moment->setMomentType($momentType);
        $this->assertEquals($momentType, $moment->getMomentType());

        // 测试文本内容
        $textContent = 'This is a test moment';
        $moment->setTextContent($textContent);
        $this->assertEquals($textContent, $moment->getTextContent());

        // 测试图片
        $images = ['https://example.com/image1.jpg', 'https://example.com/image2.jpg'];
        $moment->setImages($images);
        $this->assertEquals($images, $moment->getImages());

        // 测试视频
        $video = ['url' => 'https://example.com/video.mp4', 'thumbnail' => 'https://example.com/thumb.jpg'];
        $moment->setVideo($video);
        $this->assertEquals($video, $moment->getVideo());

        // 测试链接
        $link = ['url' => 'https://example.com', 'title' => 'Example Link'];
        $moment->setLink($link);
        $this->assertEquals($link, $moment->getLink());

        // 测试位置
        $location = 'Beijing, China';
        $moment->setLocation($location);
        $this->assertEquals($location, $moment->getLocation());

        // 测试点赞数
        $likeCount = 10;
        $moment->setLikeCount($likeCount);
        $this->assertEquals($likeCount, $moment->getLikeCount());

        // 测试评论数
        $commentCount = 5;
        $moment->setCommentCount($commentCount);
        $this->assertEquals($commentCount, $moment->getCommentCount());

        // 测试是否已点赞
        $moment->setIsLiked(true);
        $this->assertTrue($moment->isLiked());

        // 测试发布时间
        $publishTime = new \DateTimeImmutable('2024-01-01 12:00:00');
        $moment->setPublishTime($publishTime);
        $this->assertEquals($publishTime, $moment->getPublishTime());

        // 测试点赞用户
        $likeUsers = ['user1' => 'User 1', 'user2' => 'User 2', 'user3' => 'User 3'];
        $moment->setLikeUsers($likeUsers);
        $this->assertEquals($likeUsers, $moment->getLikeUsers());

        // 测试评论
        $comments = [
            'comment1' => ['user' => 'user1', 'content' => 'Nice post!'],
            'comment2' => ['user' => 'user2', 'content' => 'Great!'],
        ];
        $moment->setComments($comments);
        $this->assertEquals($comments, $moment->getComments());

        // 测试原始数据
        $rawData = '{"type":"text","content":"test"}';
        $moment->setRawData($rawData);
        $this->assertEquals($rawData, $moment->getRawData());

        // 测试有效性
        $moment->setValid(false);
        $this->assertFalse($moment->isValid());

        // 测试备注
        $remark = 'Test remark';
        $moment->setRemark($remark);
        $this->assertEquals($remark, $moment->getRemark());
    }

    public function testToStringWithTextContent(): void
    {
        $moment = new WeChatMoment();
        $moment->setAuthorNickname('Test Author');
        $moment->setTextContent('This is a test moment content');

        $result = (string) $moment;
        $this->assertStringContainsString('Test Author', $result);
        $this->assertStringContainsString('This is a test moment content', $result);
    }

    public function testToStringWithLongTextContent(): void
    {
        $moment = new WeChatMoment();
        $moment->setAuthorNickname('Test Author');
        $moment->setTextContent(str_repeat('a', 60));

        $result = (string) $moment;
        $this->assertStringContainsString('Test Author', $result);
        $this->assertStringEndsWith('...', $result);
    }

    public function testToStringWithoutTextContent(): void
    {
        $moment = new WeChatMoment();
        $moment->setAuthorNickname('Test Author');
        $moment->setMomentType('image');

        $result = (string) $moment;
        $this->assertStringContainsString('Test Author', $result);
        $this->assertStringContainsString('【image】', $result);
    }

    public function testToStringWithoutAuthorNickname(): void
    {
        $moment = new WeChatMoment();
        $moment->setAuthorWxid('author_wxid_123');
        $moment->setTextContent('Test content');

        $result = (string) $moment;
        $this->assertStringContainsString('author_wxid_123', $result);
        $this->assertStringContainsString('Test content', $result);
    }

    public function testMomentTypeCheckers(): void
    {
        $moment = new WeChatMoment();

        // 测试文本动态
        $moment->setMomentType('text');
        $this->assertTrue($moment->isTextMoment());
        $this->assertFalse($moment->isImageMoment());
        $this->assertFalse($moment->isVideoMoment());
        $this->assertFalse($moment->isLinkMoment());

        // 测试图片动态
        $moment->setMomentType('image');
        $this->assertFalse($moment->isTextMoment());
        $this->assertTrue($moment->isImageMoment());
        $this->assertFalse($moment->isVideoMoment());
        $this->assertFalse($moment->isLinkMoment());

        // 测试视频动态
        $moment->setMomentType('video');
        $this->assertFalse($moment->isTextMoment());
        $this->assertFalse($moment->isImageMoment());
        $this->assertTrue($moment->isVideoMoment());
        $this->assertFalse($moment->isLinkMoment());

        // 测试链接动态
        $moment->setMomentType('link');
        $this->assertFalse($moment->isTextMoment());
        $this->assertFalse($moment->isImageMoment());
        $this->assertFalse($moment->isVideoMoment());
        $this->assertTrue($moment->isLinkMoment());
    }

    public function testIncrementLikeCount(): void
    {
        $moment = new WeChatMoment();
        $initialCount = $moment->getLikeCount();

        $moment->incrementLikeCount();
        $this->assertEquals($initialCount + 1, $moment->getLikeCount());

        $moment->incrementLikeCount();
        $this->assertEquals($initialCount + 2, $moment->getLikeCount());
    }

    public function testDecrementLikeCount(): void
    {
        $moment = new WeChatMoment();
        $moment->setLikeCount(5);

        $moment->decrementLikeCount();
        $this->assertEquals(4, $moment->getLikeCount());

        $moment->decrementLikeCount();
        $this->assertEquals(3, $moment->getLikeCount());
    }

    public function testDecrementLikeCountNeverGoesBelowZero(): void
    {
        $moment = new WeChatMoment();
        $moment->setLikeCount(1);

        $moment->decrementLikeCount();
        $this->assertEquals(0, $moment->getLikeCount());

        $moment->decrementLikeCount();
        $this->assertEquals(0, $moment->getLikeCount());
    }

    public function testIncrementCommentCount(): void
    {
        $moment = new WeChatMoment();
        $initialCount = $moment->getCommentCount();

        $moment->incrementCommentCount();
        $this->assertEquals($initialCount + 1, $moment->getCommentCount());

        $moment->incrementCommentCount();
        $this->assertEquals($initialCount + 2, $moment->getCommentCount());
    }

    public function testDecrementCommentCount(): void
    {
        $moment = new WeChatMoment();
        $moment->setCommentCount(5);

        $moment->decrementCommentCount();
        $this->assertEquals(4, $moment->getCommentCount());

        $moment->decrementCommentCount();
        $this->assertEquals(3, $moment->getCommentCount());
    }

    public function testDecrementCommentCountNeverGoesBelowZero(): void
    {
        $moment = new WeChatMoment();
        $moment->setCommentCount(1);

        $moment->decrementCommentCount();
        $this->assertEquals(0, $moment->getCommentCount());

        $moment->decrementCommentCount();
        $this->assertEquals(0, $moment->getCommentCount());
    }

    public function testAllSetterMethods(): void
    {
        $moment = new WeChatMoment();
        $account = new WeChatAccount();

        $moment->setAccount($account);
        $moment->setMomentId('moment_123');
        $moment->setAuthorWxid('author_wxid_123');
        $moment->setAuthorNickname('Author Name');
        $moment->setAuthorAvatar('https://example.com/avatar.jpg');
        $moment->setMomentType('text');
        $moment->setTextContent('Test content');
        $moment->setImages(['https://example.com/image.jpg']);
        $moment->setVideo(['url' => 'https://example.com/video.mp4']);
        $moment->setLink(['url' => 'https://example.com']);
        $moment->setLocation('Beijing');
        $moment->setLikeCount(10);
        $moment->setCommentCount(5);
        $moment->setIsLiked(true);
        $moment->setLikeUsers(['user1' => 'user1', 'user2' => 'user2']);
        $moment->setComments(['comment1' => ['user' => 'user1', 'content' => 'Nice!']]);
        $moment->setRawData('{"test": "data"}');
        $moment->setValid(true);
        $moment->setRemark('Test remark');

        // Verify all values are set correctly
        $this->assertSame($account, $moment->getAccount());
        $this->assertEquals('moment_123', $moment->getMomentId());
        $this->assertEquals('author_wxid_123', $moment->getAuthorWxid());
        $this->assertEquals('Author Name', $moment->getAuthorNickname());
        $this->assertEquals('https://example.com/avatar.jpg', $moment->getAuthorAvatar());
        $this->assertEquals('text', $moment->getMomentType());
        $this->assertEquals('Test content', $moment->getTextContent());
        $this->assertEquals(['https://example.com/image.jpg'], $moment->getImages());
        $this->assertEquals(['url' => 'https://example.com/video.mp4'], $moment->getVideo());
        $this->assertEquals(['url' => 'https://example.com'], $moment->getLink());
        $this->assertEquals('Beijing', $moment->getLocation());
        $this->assertEquals(10, $moment->getLikeCount());
        $this->assertEquals(5, $moment->getCommentCount());
        $this->assertTrue($moment->isLiked());
        $this->assertEquals(['user1' => 'user1', 'user2' => 'user2'], $moment->getLikeUsers());
        $this->assertEquals(['comment1' => ['user' => 'user1', 'content' => 'Nice!']], $moment->getComments());
        $this->assertEquals('{"test": "data"}', $moment->getRawData());
        $this->assertTrue($moment->isValid());
        $this->assertEquals('Test remark', $moment->getRemark());
    }

    public function testBusinessMethods(): void
    {
        $moment = new WeChatMoment();
        $moment->setLikeCount(5);
        $moment->setCommentCount(3);

        // Test incrementLikeCount
        $originalLikeCount = $moment->getLikeCount();
        $moment->incrementLikeCount();
        $this->assertEquals($originalLikeCount + 1, $moment->getLikeCount());

        // Test decrementLikeCount
        $moment->decrementLikeCount();
        $this->assertEquals($originalLikeCount, $moment->getLikeCount());

        // Test incrementCommentCount
        $originalCommentCount = $moment->getCommentCount();
        $moment->incrementCommentCount();
        $this->assertEquals($originalCommentCount + 1, $moment->getCommentCount());

        // Test decrementCommentCount
        $moment->decrementCommentCount();
        $this->assertEquals($originalCommentCount, $moment->getCommentCount());
    }

    public function testCountModification(): void
    {
        $moment = new WeChatMoment();
        $moment->setLikeCount(5);
        $moment->setCommentCount(3);

        $moment->incrementLikeCount();
        $moment->incrementCommentCount();
        $moment->decrementLikeCount();
        $moment->incrementCommentCount();

        $this->assertEquals(5, $moment->getLikeCount());
        $this->assertEquals(5, $moment->getCommentCount());
    }

    public function testSetNullValuesWorkCorrectly(): void
    {
        $moment = new WeChatMoment();

        // 测试可以设置为null的字段
        $moment->setAccount(null);
        $this->assertNull($moment->getAccount());

        $moment->setAuthorNickname(null);
        $this->assertNull($moment->getAuthorNickname());

        $moment->setTextContent(null);
        $this->assertNull($moment->getTextContent());

        $moment->setImages(null);
        $this->assertNull($moment->getImages());

        $moment->setVideo(null);
        $this->assertNull($moment->getVideo());

        $moment->setLink(null);
        $this->assertNull($moment->getLink());

        $moment->setLocation(null);
        $this->assertNull($moment->getLocation());

        $moment->setLikeUsers(null);
        $this->assertNull($moment->getLikeUsers());

        $moment->setComments(null);
        $this->assertNull($moment->getComments());

        $moment->setRawData(null);
        $this->assertNull($moment->getRawData());

        $moment->setRemark(null);
        $this->assertNull($moment->getRemark());
    }

    public function testArrayFieldsWithComplexData(): void
    {
        $moment = new WeChatMoment();

        // 测试复杂的图片数组
        $images = [
            'https://example.com/image1.jpg',
            'https://example.com/image2.jpg',
            'https://example.com/image3.jpg',
        ];
        $moment->setImages($images);
        $this->assertEquals($images, $moment->getImages());

        // 测试复杂的视频数据
        $video = [
            'url' => 'https://example.com/video.mp4',
            'thumbnail' => 'https://example.com/thumb.jpg',
            'duration' => 60,
            'size' => 1024000,
        ];
        $moment->setVideo($video);
        $this->assertEquals($video, $moment->getVideo());

        // 测试复杂的链接数据
        $link = [
            'url' => 'https://example.com',
            'title' => 'Example Link',
            'description' => 'This is an example link',
            'thumbnail' => 'https://example.com/link-thumb.jpg',
        ];
        $moment->setLink($link);
        $this->assertEquals($link, $moment->getLink());

        // 测试复杂的点赞用户数组
        $likeUsers = [
            'user1' => ['wxid' => 'user1', 'nickname' => 'User One'],
            'user2' => ['wxid' => 'user2', 'nickname' => 'User Two'],
        ];
        $moment->setLikeUsers($likeUsers);
        $this->assertEquals($likeUsers, $moment->getLikeUsers());

        // 测试复杂的评论数组
        $comments = [
            'comment1' => ['wxid' => 'user1', 'nickname' => 'User One', 'content' => 'Great post!', 'time' => '2024-01-01 12:00:00'],
            'comment2' => ['wxid' => 'user2', 'nickname' => 'User Two', 'content' => 'Nice!', 'time' => '2024-01-01 12:05:00'],
        ];
        $moment->setComments($comments);
        $this->assertEquals($comments, $moment->getComments());
    }

    public function testPublishTimeCanBeSetToDateTimeImmutable(): void
    {
        $moment = new WeChatMoment();
        $immutableTime = new \DateTimeImmutable('2024-01-01 12:00:00');

        $moment->setPublishTime($immutableTime);
        $this->assertEquals($immutableTime, $moment->getPublishTime());
    }

    public function testPublishTimeCanBeSetToDateTime(): void
    {
        $moment = new WeChatMoment();
        $mutableTime = new \DateTime('2024-01-01 12:00:00');

        $moment->setPublishTime($mutableTime);
        $this->assertEquals($mutableTime, $moment->getPublishTime());
    }
}
