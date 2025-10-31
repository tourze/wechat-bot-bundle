<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\WechatBotBundle\Exception\ConfigurationException;

/**
 * @internal
 */
#[CoversClass(ConfigurationException::class)]
class ConfigurationExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return ConfigurationException::class;
    }
}
