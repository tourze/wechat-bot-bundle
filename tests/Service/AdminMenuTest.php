<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;
use Tourze\WechatBotBundle\Service\AdminMenu;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private AdminMenu $service;

    private LinkGeneratorInterface $linkGenerator;

    protected function onSetUp(): void
    {
        $this->linkGenerator = new class implements LinkGeneratorInterface {
            public function getCurdListPage(string $entityClass): string
            {
                return '/admin/mock-url';
            }

            public function extractEntityFqcn(string $url): ?string
            {
                return null;
            }

            public function setDashboard(string $dashboardControllerFqcn): void
            {
                // Mock implementation - do nothing
            }
        };

        // 替换容器中的LinkGenerator服务为我们的Mock
        self::getContainer()->set(LinkGeneratorInterface::class, $this->linkGenerator);
        $this->service = self::getService(AdminMenu::class);
    }

    public function testServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(AdminMenu::class, $this->service);
    }

    public function testInvokeMethodAddsWeChatMenuItems(): void
    {
        /** @var MockObject&ItemInterface $menuItem */
        /** @var ItemInterface&MockObject $menuItem */
        $menuItem = $this->createMock(ItemInterface::class);

        /** @var MockObject&ItemInterface $wechatMenu */
        /** @var ItemInterface&MockObject $wechatMenu */
        $wechatMenu = $this->createMock(ItemInterface::class);

        /** @var MockObject&ItemInterface $subMenuItem */
        /** @var ItemInterface&MockObject $subMenuItem */
        $subMenuItem = $this->createMock(ItemInterface::class);

        // Mock the menu item structure
        $menuItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('微信机器人')
            ->willReturnOnConsecutiveCalls(null, $wechatMenu)
        ;

        $menuItem->expects($this->once())
            ->method('addChild')
            ->with('微信机器人')
            ->willReturn($wechatMenu)
        ;

        $wechatMenu->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fa fa-wechat')
            ->willReturn($wechatMenu)
        ;

        // Mock the sub-menu items - expect 7 calls
        $wechatMenu->expects($this->exactly(7))
            ->method('addChild')
            ->willReturn($subMenuItem)
        ;

        $subMenuItem->expects($this->exactly(7))
            ->method('setUri')
            ->willReturn($subMenuItem)
        ;

        $subMenuItem->expects($this->exactly(7))
            ->method('setAttribute')
            ->willReturn($subMenuItem)
        ;

        // Invoke the service
        $this->service->__invoke($menuItem);
    }

    public function testInvokeMethodWithExistingWeChatMenu(): void
    {
        /** @var MockObject&ItemInterface $menuItem */
        /** @var ItemInterface&MockObject $menuItem */
        $menuItem = $this->createMock(ItemInterface::class);

        /** @var MockObject&ItemInterface $wechatMenu */
        /** @var ItemInterface&MockObject $wechatMenu */
        $wechatMenu = $this->createMock(ItemInterface::class);

        /** @var MockObject&ItemInterface $subMenuItem */
        /** @var ItemInterface&MockObject $subMenuItem */
        $subMenuItem = $this->createMock(ItemInterface::class);

        // Mock existing menu
        $menuItem->expects($this->exactly(2))
            ->method('getChild')
            ->with('微信机器人')
            ->willReturn($wechatMenu)
        ;

        $menuItem->expects($this->never())
            ->method('addChild')
        ;

        // Mock the sub-menu items - expect 7 calls
        $wechatMenu->expects($this->exactly(7))
            ->method('addChild')
            ->willReturn($subMenuItem)
        ;

        $subMenuItem->expects($this->exactly(7))
            ->method('setUri')
            ->willReturn($subMenuItem)
        ;

        $subMenuItem->expects($this->exactly(7))
            ->method('setAttribute')
            ->willReturn($subMenuItem)
        ;

        // Invoke the service
        $this->service->__invoke($menuItem);
    }

    public function testServiceImplementsMenuProviderInterface(): void
    {
        $this->assertInstanceOf(MenuProviderInterface::class, $this->service);
    }
}
