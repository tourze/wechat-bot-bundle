<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatMessage;

/**
 * WeChatMessage 实体单元测试
 *
 * @internal
 */
#[CoversClass(WeChatMessage::class)]
final class WeChatMessageTest extends AbstractEntityTestCase
{
    private WeChatAccount $account;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = new WeChatAccount();
    }

    public function testConstructorSetsDefaultValues(): void
    {
        $message = new WeChatMessage();

        // 验证默认值
        $this->assertNull($message->getId());
        $this->assertNull($message->getMessageId());
        $this->assertNull($message->getSenderId());
        $this->assertNull($message->getSenderName());
        $this->assertNull($message->getReceiverId());
        $this->assertNull($message->getReceiverName());
        $this->assertNull($message->getGroupId());
        $this->assertNull($message->getGroupName());
        $this->assertNull($message->getContent());
        $this->assertNull($message->getMediaUrl());
        $this->assertNull($message->getMediaFileName());
        $this->assertNull($message->getMediaFileSize());
        $this->assertNull($message->getRawData());
        $this->assertInstanceOf(\DateTimeInterface::class, $message->getMessageTime());
        $this->assertFalse($message->isRead());
        $this->assertNull($message->getReadTime());
        $this->assertFalse($message->isReplied());
        $this->assertTrue($message->isValid());
    }

    public function testConstructorSetsMessageTimeToCurrentTime(): void
    {
        $beforeCreation = new \DateTimeImmutable();
        $message = new WeChatMessage();
        $afterCreation = new \DateTimeImmutable();

        $messageTime = $message->getMessageTime();
        if ($messageTime instanceof \DateTime) {
            $this->assertGreaterThanOrEqual($beforeCreation, $messageTime);
            $this->assertLessThanOrEqual($afterCreation, $messageTime);
        } else {
            $this->assertInstanceOf(\DateTimeInterface::class, $messageTime);
        }
    }

    public function testSettersAndGettersWorkCorrectly(): void
    {
        $message = new WeChatMessage();

        // 测试账号
        $message->setAccount($this->account);
        $this->assertSame($this->account, $message->getAccount());

        // 测试消息ID
        $messageId = 'test_message_123';
        $message->setMessageId($messageId);
        $this->assertEquals($messageId, $message->getMessageId());

        // 测试消息类型
        $messageType = 'text';
        $message->setMessageType($messageType);
        $this->assertEquals($messageType, $message->getMessageType());

        // 测试消息方向
        $direction = 'inbound';
        $message->setDirection($direction);
        $this->assertEquals($direction, $message->getDirection());

        // 测试发送者ID
        $senderId = 'sender_123';
        $message->setSenderId($senderId);
        $this->assertEquals($senderId, $message->getSenderId());

        // 测试发送者名称
        $senderName = 'Sender Name';
        $message->setSenderName($senderName);
        $this->assertEquals($senderName, $message->getSenderName());

        // 测试接收者ID
        $receiverId = 'receiver_123';
        $message->setReceiverId($receiverId);
        $this->assertEquals($receiverId, $message->getReceiverId());

        // 测试接收者名称
        $receiverName = 'Receiver Name';
        $message->setReceiverName($receiverName);
        $this->assertEquals($receiverName, $message->getReceiverName());

        // 测试群组ID
        $groupId = 'group_123';
        $message->setGroupId($groupId);
        $this->assertEquals($groupId, $message->getGroupId());

        // 测试群组名称
        $groupName = 'Group Name';
        $message->setGroupName($groupName);
        $this->assertEquals($groupName, $message->getGroupName());

        // 测试内容
        $content = 'Hello World';
        $message->setContent($content);
        $this->assertEquals($content, $message->getContent());

        // 测试媒体URL
        $mediaUrl = 'https://example.com/image.jpg';
        $message->setMediaUrl($mediaUrl);
        $this->assertEquals($mediaUrl, $message->getMediaUrl());

        // 测试媒体文件名
        $mediaFileName = 'image.jpg';
        $message->setMediaFileName($mediaFileName);
        $this->assertEquals($mediaFileName, $message->getMediaFileName());

        // 测试媒体文件大小
        $mediaFileSize = 1024;
        $message->setMediaFileSize($mediaFileSize);
        $this->assertEquals($mediaFileSize, $message->getMediaFileSize());

        // 测试原始数据
        $rawData = '{"type":"text","content":"Hello"}';
        $message->setRawData($rawData);
        $this->assertEquals($rawData, $message->getRawData());

        // 测试消息时间
        $messageTime = new \DateTimeImmutable('2024-01-01 12:00:00');
        $message->setMessageTime($messageTime);
        $this->assertEquals($messageTime, $message->getMessageTime());

        // 测试是否已读
        $message->setIsRead(true);
        $this->assertTrue($message->isRead());

        // 测试已读时间
        $readTime = new \DateTimeImmutable('2024-01-01 12:05:00');
        $message->setReadTime($readTime);
        $this->assertEquals($readTime, $message->getReadTime());

        // 测试是否已回复
        $message->setIsReplied(true);
        $this->assertTrue($message->isReplied());

        // 测试有效性
        $message->setValid(false);
        $this->assertFalse($message->isValid());
    }

    public function testToStringFormatsCorrectly(): void
    {
        $message = new WeChatMessage();
        $message->setMessageType('text');
        $message->setSenderId('sender_123');
        $message->setSenderName('Sender Name');
        $message->setReceiverId('receiver_123');
        $message->setReceiverName('Receiver Name');
        $message->setContent('Hello World');
        $message->setDirection('inbound');

        $result = (string) $message;
        $this->assertStringContainsString('[text]', $result);
        $this->assertStringContainsString('Sender Name', $result);
        $this->assertStringContainsString('Receiver Name', $result);
        $this->assertStringContainsString('Hello World', $result);
        $this->assertStringContainsString('inbound', $result);
    }

    public function testGetDisplayContentForTextMessage(): void
    {
        $message = new WeChatMessage();
        $message->setMessageType('text');
        $message->setContent('Hello World');

        $this->assertEquals('Hello World', $message->getDisplayContent());
    }

    public function testGetDisplayContentForLongTextMessage(): void
    {
        $message = new WeChatMessage();
        $message->setMessageType('text');
        $message->setContent(str_repeat('a', 60));

        $displayContent = $message->getDisplayContent();
        $this->assertEquals(53, mb_strlen($displayContent)); // 50 + '...'
        $this->assertStringEndsWith('...', $displayContent);
    }

    public function testGetDisplayContentForMediaMessage(): void
    {
        $message = new WeChatMessage();
        $message->setMessageType('image');
        $message->setMediaFileName('photo.jpg');

        $this->assertEquals('[image] photo.jpg', $message->getDisplayContent());
    }

    public function testGetDisplayContentForUnsupportedMessage(): void
    {
        $message = new WeChatMessage();
        $message->setMessageType('unknown');

        $this->assertEquals('[unknown]', $message->getDisplayContent());
    }

    public function testIsTextMessageReturnsTrueForTextType(): void
    {
        $message = new WeChatMessage();
        $message->setMessageType('text');

        $this->assertTrue($message->isTextMessage());
    }

    public function testIsTextMessageReturnsFalseForNonTextType(): void
    {
        $message = new WeChatMessage();
        $message->setMessageType('image');

        $this->assertFalse($message->isTextMessage());
    }

    public function testIsMediaMessageReturnsTrueForMediaTypes(): void
    {
        $message = new WeChatMessage();

        $mediaTypes = ['image', 'voice', 'video', 'file'];
        foreach ($mediaTypes as $type) {
            $message->setMessageType($type);
            $this->assertTrue($message->isMediaMessage(), "Failed for type: {$type}");
        }
    }

    public function testIsMediaMessageReturnsFalseForNonMediaTypes(): void
    {
        $message = new WeChatMessage();
        $message->setMessageType('text');

        $this->assertFalse($message->isMediaMessage());
    }

    public function testIsInboundReturnsTrueForInboundDirection(): void
    {
        $message = new WeChatMessage();
        $message->setDirection('inbound');

        $this->assertTrue($message->isInbound());
    }

    public function testIsInboundReturnsFalseForOutboundDirection(): void
    {
        $message = new WeChatMessage();
        $message->setDirection('outbound');

        $this->assertFalse($message->isInbound());
    }

    public function testIsOutboundReturnsTrueForOutboundDirection(): void
    {
        $message = new WeChatMessage();
        $message->setDirection('outbound');

        $this->assertTrue($message->isOutbound());
    }

    public function testIsOutboundReturnsFalseForInboundDirection(): void
    {
        $message = new WeChatMessage();
        $message->setDirection('inbound');

        $this->assertFalse($message->isOutbound());
    }

    public function testIsGroupMessageReturnsTrueWhenGroupIdIsSet(): void
    {
        $message = new WeChatMessage();
        $message->setGroupId('group_123');

        $this->assertTrue($message->isGroupMessage());
    }

    public function testIsGroupMessageReturnsFalseWhenGroupIdIsEmpty(): void
    {
        $message = new WeChatMessage();
        $message->setGroupId('');

        $this->assertFalse($message->isGroupMessage());
    }

    public function testIsGroupMessageReturnsFalseWhenGroupIdIsNull(): void
    {
        $message = new WeChatMessage();
        $message->setGroupId(null);

        $this->assertFalse($message->isGroupMessage());
    }

    public function testMarkAsReadSetsReadStateAndTime(): void
    {
        $message = new WeChatMessage();
        $beforeMark = new \DateTimeImmutable();

        $message->markAsRead();

        $afterMark = new \DateTimeImmutable();
        $this->assertTrue($message->isRead());

        $readTime = $message->getReadTime();
        $this->assertNotNull($readTime);

        if ($readTime instanceof \DateTime) {
            $this->assertGreaterThanOrEqual($beforeMark, $readTime);
            $this->assertLessThanOrEqual($afterMark, $readTime);
        }
    }

    public function testMarkAsRepliedSetsRepliedState(): void
    {
        $message = new WeChatMessage();
        $this->assertFalse($message->isReplied());

        $message->markAsReplied();
        $this->assertTrue($message->isReplied());
    }

    public function testSetterMethods(): void
    {
        $message = new WeChatMessage();

        $message->setAccount($this->account);
        $message->setMessageId('test_message');
        $message->setMessageType('text');
        $message->setDirection('inbound');
        $message->setSenderId('sender_123');
        $message->setSenderName('Sender Name');
        $message->setReceiverId('receiver_123');
        $message->setReceiverName('Receiver Name');
        $message->setGroupId('group_123');
        $message->setGroupName('Group Name');
        $message->setContent('Hello World');
        $message->setMediaUrl('https://example.com/media.jpg');
        $message->setMediaFileName('media.jpg');
        $message->setMediaFileSize(1024);
        $message->setRawData('{"test": "data"}');
        $message->setIsRead(true);
        $message->setIsReplied(true);
        $message->setValid(true);

        // Verify all values are set correctly
        $this->assertSame($this->account, $message->getAccount());
        $this->assertEquals('test_message', $message->getMessageId());
        $this->assertEquals('text', $message->getMessageType());
        $this->assertEquals('inbound', $message->getDirection());
        $this->assertEquals('sender_123', $message->getSenderId());
        $this->assertEquals('Sender Name', $message->getSenderName());
        $this->assertEquals('receiver_123', $message->getReceiverId());
        $this->assertEquals('Receiver Name', $message->getReceiverName());
        $this->assertEquals('group_123', $message->getGroupId());
        $this->assertEquals('Group Name', $message->getGroupName());
        $this->assertEquals('Hello World', $message->getContent());
        $this->assertEquals('https://example.com/media.jpg', $message->getMediaUrl());
        $this->assertEquals('media.jpg', $message->getMediaFileName());
        $this->assertEquals(1024, $message->getMediaFileSize());
        $this->assertEquals('{"test": "data"}', $message->getRawData());
        $this->assertTrue($message->isRead());
        $this->assertTrue($message->isReplied());
        $this->assertTrue($message->isValid());
    }

    public function testBusinessMethods(): void
    {
        $message = new WeChatMessage();

        // Test markAsRead
        $this->assertFalse($message->isRead());
        $message->markAsRead();
        $this->assertTrue($message->isRead());

        // Test markAsReplied
        $this->assertFalse($message->isReplied());
        $message->markAsReplied();
        $this->assertTrue($message->isReplied());
    }

    public function testToStringWithMissingNames(): void
    {
        $message = new WeChatMessage();
        $message->setMessageType('text');
        $message->setSenderId('sender_123');
        $message->setReceiverId('receiver_123');
        $message->setContent('Hello World');
        $message->setDirection('inbound');

        $result = (string) $message;
        $this->assertStringContainsString('sender_123', $result);
        $this->assertStringContainsString('receiver_123', $result);
    }

    public function testToStringWithUnknownSenderAndReceiver(): void
    {
        $message = new WeChatMessage();
        $message->setMessageType('text');
        $message->setContent('Hello World');
        $message->setDirection('inbound');

        $result = (string) $message;
        $this->assertStringContainsString('Unknown', $result);
    }

    public function testGetDisplayContentForEmptyTextMessage(): void
    {
        $message = new WeChatMessage();
        $message->setMessageType('text');
        $message->setContent('');

        $this->assertEquals('[text]', $message->getDisplayContent());
    }

    public function testGetDisplayContentForMediaMessageWithoutFileName(): void
    {
        $message = new WeChatMessage();
        $message->setMessageType('image');
        $message->setMediaFileName('');

        $this->assertEquals('[image]', $message->getDisplayContent());
    }

    public function testMessageTimeCanBeSetToDateTimeImmutable(): void
    {
        $message = new WeChatMessage();
        $immutableTime = new \DateTimeImmutable('2024-01-01 12:00:00');

        $message->setMessageTime($immutableTime);
        $this->assertEquals($immutableTime, $message->getMessageTime());
    }

    public function testSetNullValuesWorkCorrectly(): void
    {
        $message = new WeChatMessage();

        // 测试可以设置为null的字段
        $message->setMessageId(null);
        $this->assertNull($message->getMessageId());

        $message->setSenderId(null);
        $this->assertNull($message->getSenderId());

        $message->setContent(null);
        $this->assertNull($message->getContent());

        $message->setMediaUrl(null);
        $this->assertNull($message->getMediaUrl());

        $message->setReadTime(null);
        $this->assertNull($message->getReadTime());
    }

    protected function createEntity(): WeChatMessage
    {
        return new WeChatMessage();
    }

    /**
     * 提供 WeChatMessage 实体的属性数据进行自动测试。
     *
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'messageId' => ['messageId', 'msg_123'];
        yield 'messageType' => ['messageType', 'text'];
        yield 'direction' => ['direction', 'inbound'];
        yield 'senderId' => ['senderId', 'sender_123'];
        yield 'senderName' => ['senderName', 'Sender Name'];
        yield 'receiverId' => ['receiverId', 'receiver_123'];
        yield 'receiverName' => ['receiverName', 'Receiver Name'];
        yield 'groupId' => ['groupId', 'group_123'];
        yield 'groupName' => ['groupName', 'Group Name'];
        yield 'content' => ['content', 'Hello World'];
        yield 'mediaUrl' => ['mediaUrl', 'https://example.com/media.jpg'];
        yield 'mediaFileName' => ['mediaFileName', 'media.jpg'];
        yield 'mediaFileSize' => ['mediaFileSize', 1024];
        yield 'rawData' => ['rawData', '{"type":"text"}'];
        yield 'messageTime' => ['messageTime', new \DateTimeImmutable('2024-01-01')];
        yield 'readTime' => ['readTime', new \DateTimeImmutable('2024-01-02')];
        // 注意：isRead, isReplied, valid 属性有特殊的 getter 方法，暂时跳过自动测试
    }
}
