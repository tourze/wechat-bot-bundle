<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatBotBundle\Exception\MessageException;

/**
 * @internal
 */
#[CoversClass(MessageException::class)]
class MessageExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return MessageException::class;
    }
}
