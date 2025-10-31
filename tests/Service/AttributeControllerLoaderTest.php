<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Routing\RouteCollection;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\WechatBotBundle\Service\AttributeControllerLoader;

/**
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(AttributeControllerLoader::class)]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    private AttributeControllerLoader $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(AttributeControllerLoader::class);
    }

    public function testServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(AttributeControllerLoader::class, $this->service);
    }

    public function testAutoload(): void
    {
        $collection = $this->service->autoload();

        $this->assertInstanceOf(RouteCollection::class, $collection);
        $this->assertGreaterThan(0, count($collection));
    }

    public function testSupports(): void
    {
        $result = $this->service->supports('any_resource');

        $this->assertFalse($result);
    }

    public function testLoad(): void
    {
        $collection = $this->service->load('any_resource');

        $this->assertInstanceOf(RouteCollection::class, $collection);
        $this->assertGreaterThan(0, count($collection));
        // load() 方法应该与 autoload() 返回相同的结果
        $this->assertEquals($this->service->autoload(), $collection);
    }
}
