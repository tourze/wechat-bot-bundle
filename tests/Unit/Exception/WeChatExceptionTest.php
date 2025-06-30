<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tourze\WechatBotBundle\Exception\WeChatException;

class WeChatExceptionTest extends TestCase
{
    public function testExceptionIsInstanceOfRuntimeException(): void
    {
        $exception = new WeChatException('Test message');
        
        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionWithCodePrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new WeChatException('Test message', 500, $previous);
        
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}