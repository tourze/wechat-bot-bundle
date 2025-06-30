<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\WechatBotBundle\Exception\InvalidArgumentException;

class InvalidArgumentExceptionTest extends TestCase
{
    public function testExceptionIsInstanceOfBaseInvalidArgumentException(): void
    {
        $exception = new InvalidArgumentException('Test message');
        
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertEquals('Test message', $exception->getMessage());
    }

    public function testExceptionWithCodePrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new InvalidArgumentException('Test message', 500, $previous);
        
        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}