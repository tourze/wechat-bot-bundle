<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatBotBundle\Exception\ApiException;

/**
 * @internal
 */
#[CoversClass(ApiException::class)]
class ApiExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return ApiException::class;
    }
}
