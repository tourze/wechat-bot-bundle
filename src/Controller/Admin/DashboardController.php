<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Entity\WeChatContact;
use Tourze\WechatBotBundle\Entity\WeChatGroup;
use Tourze\WechatBotBundle\Entity\WeChatMessage;
use Tourze\WechatBotBundle\Entity\WeChatMoment;
use Tourze\WechatBotBundle\Entity\WeChatTag;

#[AdminDashboard(routePath: '/wechat-bot/admin', routeName: 'wechat_bot_admin')]
final class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('@EasyAdmin/welcome.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('微信机器人管理')
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('首页', 'fa fa-home');
        yield MenuItem::linkToCrud('微信账号', 'fa fa-user', WeChatAccount::class);
        yield MenuItem::linkToCrud('API账号', 'fa fa-key', WeChatApiAccount::class);
        yield MenuItem::linkToCrud('联系人', 'fa fa-address-book', WeChatContact::class);
        yield MenuItem::linkToCrud('群组', 'fa fa-users', WeChatGroup::class);
        yield MenuItem::linkToCrud('消息', 'fa fa-comment', WeChatMessage::class);
        yield MenuItem::linkToCrud('朋友圈', 'fa fa-circle', WeChatMoment::class);
        yield MenuItem::linkToCrud('标签', 'fa fa-tag', WeChatTag::class);
    }
}
