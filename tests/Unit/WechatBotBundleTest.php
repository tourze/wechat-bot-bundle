<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Unit;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\WechatBotBundle\WechatBotBundle;

class WechatBotBundleTest extends TestCase
{
    private WechatBotBundle $bundle;

    protected function setUp(): void
    {
        $this->bundle = new WechatBotBundle();
    }

    public function testBundleIsInstanceOfBundle(): void
    {
        $this->assertInstanceOf(Bundle::class, $this->bundle);
    }

    public function testBundleImplementsBundleDependencyInterface(): void
    {
        $this->assertInstanceOf(BundleDependencyInterface::class, $this->bundle);
    }

    public function testGetBundleDependencies(): void
    {
        $dependencies = WechatBotBundle::getBundleDependencies();
        
        $this->assertArrayHasKey(DoctrineBundle::class, $dependencies);
        $this->assertEquals(['all' => true], $dependencies[DoctrineBundle::class]);
    }
}