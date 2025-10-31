<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\RouteCollection;
use Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface;
use Tourze\WechatBotBundle\Controller\QrCode\CheckStatusController;
use Tourze\WechatBotBundle\Controller\QrCode\ConfirmLoginController;
use Tourze\WechatBotBundle\Controller\QrCode\GenerateQrCodeController;
use Tourze\WechatBotBundle\Controller\QrCode\LoginPageController;
use Tourze\WechatBotBundle\Controller\QrCode\LogoutController;

/**
 * 属性控制器加载器
 *
 * 用于自动加载带有路由属性的控制器类
 */
#[Autoconfigure(public: true)]
#[AutoconfigureTag(name: 'routing.loader')]
class AttributeControllerLoader extends Loader implements RoutingAutoLoaderInterface
{
    private AttributeRouteControllerLoader $controllerLoader;

    public function __construct()
    {
        parent::__construct();
        $this->controllerLoader = new AttributeRouteControllerLoader();
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        return $this->autoload();
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return false;
    }

    public function autoload(): RouteCollection
    {
        $collection = new RouteCollection();

        // 注册二维码相关控制器
        $collection->addCollection($this->controllerLoader->load(LoginPageController::class));
        $collection->addCollection($this->controllerLoader->load(GenerateQrCodeController::class));
        $collection->addCollection($this->controllerLoader->load(CheckStatusController::class));
        $collection->addCollection($this->controllerLoader->load(ConfirmLoginController::class));
        $collection->addCollection($this->controllerLoader->load(LogoutController::class));

        return $collection;
    }
}
