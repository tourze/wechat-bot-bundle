<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\WeChatMessageData;

/**
 * WeChatMessageData DTO 单元测试
 *
 * @internal
 */
#[CoversClass(WeChatMessageData::class)]
final class WeChatMessageDataTest extends RequestTestCase
{
    public function testConstructWithValidParametersSetsPropertiesCorrectly(): void
    {
        // Arrange
        $deviceId = 'device_123456';
        $messageId = 'msg_789012';
        $messageType = 'text';
        $senderId = 'sender_wxid';
        $senderName = 'Sender Name';
        $receiverId = 'receiver_wxid';
        $receiverName = 'Receiver Name';
        $groupId = 'group_wxid@chatroom';
        $groupName = 'Group Name';
        $content = 'Hello, world!';
        $mediaUrl = 'https://example.com/media.jpg';
        $mediaFileName = 'media.jpg';
        $messageTime = new \DateTime('2022-01-01 12:00:00');

        // Act
        $result = new WeChatMessageData(
            deviceId: $deviceId,
            messageId: $messageId,
            messageType: $messageType,
            senderId: $senderId,
            senderName: $senderName,
            receiverId: $receiverId,
            receiverName: $receiverName,
            groupId: $groupId,
            groupName: $groupName,
            content: $content,
            mediaUrl: $mediaUrl,
            mediaFileName: $mediaFileName,
            messageTime: $messageTime
        );

        // Assert
        $this->assertSame($deviceId, $result->deviceId);
        $this->assertSame($messageId, $result->messageId);
        $this->assertSame($messageType, $result->messageType);
        $this->assertSame($senderId, $result->senderId);
        $this->assertSame($senderName, $result->senderName);
        $this->assertSame($receiverId, $result->receiverId);
        $this->assertSame($receiverName, $result->receiverName);
        $this->assertSame($groupId, $result->groupId);
        $this->assertSame($groupName, $result->groupName);
        $this->assertSame($content, $result->content);
        $this->assertSame($mediaUrl, $result->mediaUrl);
        $this->assertSame($mediaFileName, $result->mediaFileName);
        $this->assertSame($messageTime, $result->messageTime);
    }

    public function testIsGroupMessageWithGroupId(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'text',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: 'group@chatroom',
            groupName: 'Group',
            content: 'Group message',
            mediaUrl: null,
            mediaFileName: null,
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $this->assertTrue($result->isGroupMessage());
        $this->assertFalse($result->isPrivateMessage());
    }

    public function testIsGroupMessageWithoutGroupId(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'text',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: 'Private message',
            mediaUrl: null,
            mediaFileName: null,
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $this->assertFalse($result->isGroupMessage());
        $this->assertTrue($result->isPrivateMessage());
    }

    public function testIsPrivateMessageWithoutGroupId(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'text',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: 'Private message',
            mediaUrl: null,
            mediaFileName: null,
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $this->assertTrue($result->isPrivateMessage());
        $this->assertFalse($result->isGroupMessage());
    }

    public function testIsTextMessageWithTextType(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'text',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: 'Text message',
            mediaUrl: null,
            mediaFileName: null,
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $this->assertTrue($result->isTextMessage());
        $this->assertFalse($result->isMediaMessage());
    }

    public function testIsTextMessageWithNonTextType(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'image',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: null,
            mediaUrl: 'https://example.com/image.jpg',
            mediaFileName: 'image.jpg',
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $this->assertFalse($result->isTextMessage());
        $this->assertTrue($result->isMediaMessage());
    }

    public function testIsMediaMessageWithImageType(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'image',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: null,
            mediaUrl: 'https://example.com/image.jpg',
            mediaFileName: 'image.jpg',
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $this->assertTrue($result->isMediaMessage());
        $this->assertFalse($result->isTextMessage());
    }

    public function testIsMediaMessageWithVoiceType(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'voice',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: null,
            mediaUrl: 'https://example.com/voice.amr',
            mediaFileName: 'voice.amr',
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $this->assertTrue($result->isMediaMessage());
    }

    public function testIsMediaMessageWithVideoType(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'video',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: null,
            mediaUrl: 'https://example.com/video.mp4',
            mediaFileName: 'video.mp4',
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $this->assertTrue($result->isMediaMessage());
    }

    public function testIsMediaMessageWithFileType(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'file',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: null,
            mediaUrl: 'https://example.com/document.pdf',
            mediaFileName: 'document.pdf',
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $this->assertTrue($result->isMediaMessage());
    }

    public function testIsMediaMessageWithNonMediaType(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'text',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: 'Text message',
            mediaUrl: null,
            mediaFileName: null,
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $this->assertFalse($result->isMediaMessage());
    }

    public function testGetDisplayContentWithTextContent(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'text',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: 'This is a text message',
            mediaUrl: null,
            mediaFileName: null,
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $this->assertSame('This is a text message', $result->getDisplayContent());
    }

    public function testGetDisplayContentWithLongTextContent(): void
    {
        // Arrange
        $longContent = str_repeat('This is a very long text message that exceeds 100 characters. ', 5);

        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'text',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: $longContent,
            mediaUrl: null,
            mediaFileName: null,
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $displayContent = $result->getDisplayContent();
        $this->assertLessThanOrEqual(100, mb_strlen($displayContent));
        $this->assertStringStartsWith('This is a very long text message', $displayContent);
    }

    public function testGetDisplayContentWithImageType(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'image',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: null,
            mediaUrl: 'https://example.com/image.jpg',
            mediaFileName: 'image.jpg',
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $this->assertSame('[图片]', $result->getDisplayContent());
    }

    public function testGetDisplayContentWithVoiceType(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'voice',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: null,
            mediaUrl: 'https://example.com/voice.amr',
            mediaFileName: 'voice.amr',
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $this->assertSame('[语音]', $result->getDisplayContent());
    }

    public function testGetDisplayContentWithVideoType(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'video',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: null,
            mediaUrl: 'https://example.com/video.mp4',
            mediaFileName: 'video.mp4',
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $this->assertSame('[视频]', $result->getDisplayContent());
    }

    public function testGetDisplayContentWithFileType(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'file',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: null,
            mediaUrl: 'https://example.com/document.pdf',
            mediaFileName: 'document.pdf',
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $this->assertSame('[文件]: document.pdf', $result->getDisplayContent());
    }

    public function testGetDisplayContentWithFileTypeNoFileName(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'file',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: null,
            mediaUrl: 'https://example.com/document.pdf',
            mediaFileName: null,
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $this->assertSame('[文件]', $result->getDisplayContent());
    }

    public function testGetDisplayContentWithOtherMessageTypes(): void
    {
        $testCases = [
            'link' => '[链接]',
            'card' => '[名片]',
            'emoji' => '[表情]',
            'unknown_type' => '[unknown_type]',
        ];

        foreach ($testCases as $type => $expected) {
            $result = new WeChatMessageData(
                deviceId: 'device_123',
                messageId: 'msg_123',
                messageType: $type,
                senderId: 'sender_wxid',
                senderName: 'Sender',
                receiverId: 'receiver_wxid',
                receiverName: 'Receiver',
                groupId: null,
                groupName: null,
                content: null,
                mediaUrl: null,
                mediaFileName: null,
                messageTime: new \DateTimeImmutable()
            );

            $this->assertSame($expected, $result->getDisplayContent());
        }
    }

    public function testToString(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'text',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: 'group@chatroom',
            groupName: 'Group',
            content: 'Hello, world!',
            mediaUrl: null,
            mediaFileName: null,
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $stringResult = (string) $result;
        $this->assertStringContainsString('WeChatMessageData', $stringResult);
        $this->assertStringContainsString('type=text', $stringResult);
        $this->assertStringContainsString('from=sender_wxid', $stringResult);
        $this->assertStringContainsString('to=receiver_wxid', $stringResult);
        $this->assertStringContainsString('group=group@chatroom', $stringResult);
    }

    public function testToStringWithNullValues(): void
    {
        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: null,
            messageType: 'text',
            senderId: null,
            senderName: null,
            receiverId: null,
            receiverName: null,
            groupId: null,
            groupName: null,
            content: 'Hello, world!',
            mediaUrl: null,
            mediaFileName: null,
            messageTime: new \DateTimeImmutable()
        );

        // Assert
        $stringResult = (string) $result;
        $this->assertStringContainsString('from=null', $stringResult);
        $this->assertStringContainsString('to=null', $stringResult);
        $this->assertStringContainsString('group=null', $stringResult);
    }

    public function testConstructWithDateTimeImmutable(): void
    {
        // Arrange
        $messageTime = new \DateTimeImmutable('2022-01-01 15:30:00');

        // Act
        $result = new WeChatMessageData(
            deviceId: 'device_123',
            messageId: 'msg_123',
            messageType: 'text',
            senderId: 'sender_wxid',
            senderName: 'Sender',
            receiverId: 'receiver_wxid',
            receiverName: 'Receiver',
            groupId: null,
            groupName: null,
            content: 'Hello, world!',
            mediaUrl: null,
            mediaFileName: null,
            messageTime: $messageTime
        );

        // Assert
        $this->assertSame($messageTime, $result->messageTime);
        $this->assertInstanceOf(\DateTimeInterface::class, $result->messageTime);
    }
}
