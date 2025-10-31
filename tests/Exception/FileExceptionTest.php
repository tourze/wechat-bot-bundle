<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatBotBundle\Exception\FileException;

/**
 * @internal
 */
#[CoversClass(FileException::class)]
class FileExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return FileException::class;
    }
}
