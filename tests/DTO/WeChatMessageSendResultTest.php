<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\DTO;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\DTO\WeChatMessageSendResult;
use Tourze\WechatBotBundle\Entity\WeChatMessage;

/**
 * WeChatMessageSendResult DTO 单元测试
 *
 * @internal
 */
#[CoversClass(WeChatMessageSendResult::class)]
final class WeChatMessageSendResultTest extends RequestTestCase
{
    public function testConstructWithValidParametersSetsPropertiesCorrectly(): void
    {
        // Arrange
        $success = true;
        $message = new WeChatMessage();
        $apiResponse = ['status' => 'ok', 'messageId' => 'msg_123'];
        $errorMessage = null;

        // Act
        $result = new WeChatMessageSendResult(
            success: $success,
            message: $message,
            apiResponse: $apiResponse,
            errorMessage: $errorMessage
        );

        // Assert
        $this->assertSame($success, $result->success);
        $this->assertSame($message, $result->message);
        $this->assertSame($apiResponse, $result->apiResponse);
        $this->assertSame($errorMessage, $result->errorMessage);
    }

    public function testConstructWithSuccessfulResult(): void
    {
        // Arrange
        $message = new WeChatMessage();
        $apiResponse = ['status' => 'success', 'messageId' => 'msg_456'];

        // Act
        $result = new WeChatMessageSendResult(
            success: true,
            message: $message,
            apiResponse: $apiResponse,
            errorMessage: null
        );

        // Assert
        $this->assertTrue($result->success);
        $this->assertSame($message, $result->message);
        $this->assertSame($apiResponse, $result->apiResponse);
        $this->assertNull($result->errorMessage);
    }

    public function testConstructWithFailedResult(): void
    {
        // Act
        $result = new WeChatMessageSendResult(
            success: false,
            message: null,
            apiResponse: null,
            errorMessage: 'Failed to send message'
        );

        // Assert
        $this->assertFalse($result->success);
        $this->assertNull($result->message);
        $this->assertNull($result->apiResponse);
        $this->assertSame('Failed to send message', $result->errorMessage);
    }

    public function testIsSuccessWithTrueSuccess(): void
    {
        // Act
        $result = new WeChatMessageSendResult(
            success: true,
            message: new WeChatMessage(),
            apiResponse: ['status' => 'ok'],
            errorMessage: null
        );

        // Assert
        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->isFailure());
    }

    public function testIsSuccessWithFalseSuccess(): void
    {
        // Act
        $result = new WeChatMessageSendResult(
            success: false,
            message: null,
            apiResponse: null,
            errorMessage: 'Error occurred'
        );

        // Assert
        $this->assertFalse($result->isSuccess());
        $this->assertTrue($result->isFailure());
    }

    public function testIsFailureWithTrueSuccess(): void
    {
        // Act
        $result = new WeChatMessageSendResult(
            success: true,
            message: new WeChatMessage(),
            apiResponse: ['status' => 'ok'],
            errorMessage: null
        );

        // Assert
        $this->assertFalse($result->isFailure());
        $this->assertTrue($result->isSuccess());
    }

    public function testIsFailureWithFalseSuccess(): void
    {
        // Act
        $result = new WeChatMessageSendResult(
            success: false,
            message: null,
            apiResponse: null,
            errorMessage: 'Error occurred'
        );

        // Assert
        $this->assertTrue($result->isFailure());
        $this->assertFalse($result->isSuccess());
    }

    public function testHasMessageWithMessage(): void
    {
        // Arrange
        $message = new WeChatMessage();

        // Act
        $result = new WeChatMessageSendResult(
            success: true,
            message: $message,
            apiResponse: ['status' => 'ok'],
            errorMessage: null
        );

        // Assert
        $this->assertTrue($result->hasMessage());
    }

    public function testHasMessageWithoutMessage(): void
    {
        // Act
        $result = new WeChatMessageSendResult(
            success: false,
            message: null,
            apiResponse: null,
            errorMessage: 'Error occurred'
        );

        // Assert
        $this->assertFalse($result->hasMessage());
    }

    public function testHasErrorWithError(): void
    {
        // Act
        $result = new WeChatMessageSendResult(
            success: false,
            message: null,
            apiResponse: null,
            errorMessage: 'Connection timeout'
        );

        // Assert
        $this->assertTrue($result->hasError());
        $this->assertSame('Connection timeout', $result->errorMessage);
    }

    public function testHasErrorWithoutError(): void
    {
        // Act
        $result = new WeChatMessageSendResult(
            success: true,
            message: new WeChatMessage(),
            apiResponse: ['status' => 'ok'],
            errorMessage: null
        );

        // Assert
        $this->assertFalse($result->hasError());
        $this->assertNull($result->errorMessage);
    }

    public function testGetMessageIdWithMessage(): void
    {
        // Arrange
        $message = new WeChatMessage();
        // 假设 WeChatMessage 有 setId 方法或者通过 reflection 设置 ID
        $reflectionClass = new \ReflectionClass($message);
        if ($reflectionClass->hasProperty('id')) {
            $idProperty = $reflectionClass->getProperty('id');
            $idProperty->setAccessible(true);
            $idProperty->setValue($message, 123);
        }

        // Act
        $result = new WeChatMessageSendResult(
            success: true,
            message: $message,
            apiResponse: ['status' => 'ok'],
            errorMessage: null
        );

        // Assert
        $messageId = $result->getMessageId();
        if ($reflectionClass->hasProperty('id')) {
            $this->assertSame(123, $messageId);
        } else {
            $this->assertNull($messageId);
        }
    }

    public function testGetMessageIdWithoutMessage(): void
    {
        // Act
        $result = new WeChatMessageSendResult(
            success: false,
            message: null,
            apiResponse: null,
            errorMessage: 'Error occurred'
        );

        // Assert
        $this->assertNull($result->getMessageId());
    }

    public function testToStringWithSuccessfulResult(): void
    {
        // Arrange
        $message = new WeChatMessage();
        $reflectionClass = new \ReflectionClass($message);
        if ($reflectionClass->hasProperty('id')) {
            $idProperty = $reflectionClass->getProperty('id');
            $idProperty->setAccessible(true);
            $idProperty->setValue($message, 456);
        }

        // Act
        $result = new WeChatMessageSendResult(
            success: true,
            message: $message,
            apiResponse: ['status' => 'success'],
            errorMessage: null
        );

        // Assert
        $stringResult = (string) $result;
        $this->assertStringContainsString('WeChatMessageSendResult', $stringResult);
        $this->assertStringContainsString('success=true', $stringResult);
        $this->assertStringContainsString('error=none', $stringResult);

        if ($reflectionClass->hasProperty('id')) {
            $this->assertStringContainsString('messageId=456', $stringResult);
        } else {
            $this->assertStringContainsString('messageId=null', $stringResult);
        }
    }

    public function testToStringWithFailedResult(): void
    {
        // Act
        $result = new WeChatMessageSendResult(
            success: false,
            message: null,
            apiResponse: null,
            errorMessage: 'Network error'
        );

        // Assert
        $stringResult = (string) $result;
        $this->assertStringContainsString('WeChatMessageSendResult', $stringResult);
        $this->assertStringContainsString('success=false', $stringResult);
        $this->assertStringContainsString('messageId=null', $stringResult);
        $this->assertStringContainsString('error=Network error', $stringResult);
    }

    public function testConstructWithComplexApiResponse(): void
    {
        // Arrange
        $complexApiResponse = [
            'status' => 'success',
            'messageId' => 'msg_789',
            'timestamp' => 1640995200,
            'details' => [
                'deliveryStatus' => 'sent',
                'recipientCount' => 1,
            ],
        ];

        // Act
        $result = new WeChatMessageSendResult(
            success: true,
            message: new WeChatMessage(),
            apiResponse: $complexApiResponse,
            errorMessage: null
        );

        // Assert
        $this->assertSame($complexApiResponse, $result->apiResponse);
        $this->assertSame('success', $result->apiResponse['status']);
        $this->assertSame('msg_789', $result->apiResponse['messageId']);
        $this->assertSame(['deliveryStatus' => 'sent', 'recipientCount' => 1], $result->apiResponse['details']);
    }

    public function testConstructWithEmptyApiResponse(): void
    {
        // Act
        $result = new WeChatMessageSendResult(
            success: true,
            message: new WeChatMessage(),
            apiResponse: [],
            errorMessage: null
        );

        // Assert
        $this->assertSame([], $result->apiResponse);
        $this->assertEmpty($result->apiResponse);
    }

    public function testConstructWithLongErrorMessage(): void
    {
        // Arrange
        $longErrorMessage = 'This is a very long error message that describes a complex network connectivity issue that occurred during the message sending process and includes detailed technical information about the failure';

        // Act
        $result = new WeChatMessageSendResult(
            success: false,
            message: null,
            apiResponse: null,
            errorMessage: $longErrorMessage
        );

        // Assert
        $this->assertTrue($result->hasError());
        $this->assertSame($longErrorMessage, $result->errorMessage);
        $this->assertStringContainsString('very long error message', $result->errorMessage);
    }

    public function testConstructWithEmptyErrorMessage(): void
    {
        // Act
        $result = new WeChatMessageSendResult(
            success: false,
            message: null,
            apiResponse: null,
            errorMessage: ''
        );

        // Assert
        $this->assertTrue($result->hasError()); // 空字符串也被认为是有错误
        $this->assertSame('', $result->errorMessage);
    }

    public function testConstructWithSuccessButNoMessage(): void
    {
        // Act - 测试成功但没有消息实体的情况
        $result = new WeChatMessageSendResult(
            success: true,
            message: null,
            apiResponse: ['status' => 'queued'],
            errorMessage: null
        );

        // Assert
        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->hasMessage());
        $this->assertNull($result->getMessageId());
        $this->assertFalse($result->hasError());
    }

    public function testConstructWithFailureButHasApiResponse(): void
    {
        // Act - 测试失败但有API响应的情况
        $result = new WeChatMessageSendResult(
            success: false,
            message: null,
            apiResponse: ['status' => 'error', 'code' => 400, 'message' => 'Bad Request'],
            errorMessage: 'API returned error'
        );

        // Assert
        $this->assertTrue($result->isFailure());
        $this->assertFalse($result->hasMessage());
        $this->assertNull($result->getMessageId());
        $this->assertTrue($result->hasError());
        $this->assertSame(['status' => 'error', 'code' => 400, 'message' => 'Bad Request'], $result->apiResponse);
    }
}
