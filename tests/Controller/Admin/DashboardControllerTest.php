<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Controller\Admin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\WechatBotBundle\Controller\Admin\DashboardController;

/**
 * DashboardController 控制器测试
 *
 * @internal
 */
#[CoversClass(DashboardController::class)]
#[RunTestsInSeparateProcesses]
final class DashboardControllerTest extends AbstractWebTestCase
{
    public function testControllerExists(): void
    {
        $controller = new DashboardController();
        $this->assertInstanceOf(DashboardController::class, $controller);
    }

    public function testConfigureMenuItemsReturnsGenerator(): void
    {
        $controller = new DashboardController();
        $menuItems = $controller->configureMenuItems();

        // 验证返回的是可迭代对象
        $this->assertIsIterable($menuItems);

        // 转换为数组以便计数
        $items = iterator_to_array($menuItems);
        $this->assertNotEmpty($items);

        // 验证菜单项数量大于0
        $this->assertGreaterThan(0, count($items));
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        // 实现抽象方法 - Dashboard控制器不需要测试HTTP方法限制
        self::markTestSkipped('Dashboard controller does not require HTTP method restriction testing');
    }
}
