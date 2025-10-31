<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatBotBundle\Exception\WeChatException;

/**
 * @internal
 */
#[CoversClass(WeChatException::class)]
class WeChatExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return WeChatException::class;
    }
}
