<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatBotBundle\Exception\InvalidResponseException;

/**
 * @internal
 */
#[CoversClass(InvalidResponseException::class)]
class InvalidResponseExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return InvalidResponseException::class;
    }
}
