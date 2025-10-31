<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatBotBundle\Exception\LoginException;

/**
 * @internal
 */
#[CoversClass(LoginException::class)]
class LoginExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return LoginException::class;
    }
}
