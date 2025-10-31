<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;
use Tourze\WechatBotBundle\WechatBotBundle;

/**
 * @internal
 * @phpstan-ignore symplify.forbiddenExtendOfNonAbstractClass
 */
#[CoversClass(WechatBotBundle::class)]
#[RunTestsInSeparateProcesses]
final class WechatBotBundleTest extends AbstractBundleTestCase
{
}
