<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\WechatBotBundle\Exception\FileException;
use Tourze\WechatBotBundle\Exception\WeChatException;

class FileExceptionTest extends TestCase
{
    public function testExceptionIsInstanceOfWeChatException(): void
    {
        $exception = new FileException('Test message');
        
        $this->assertInstanceOf(WeChatException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionWithCodePrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new FileException('Test message', 500, $previous);
        
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}