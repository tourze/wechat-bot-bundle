<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Entity\WeChatContact;
use Tourze\WechatBotBundle\Entity\WeChatGroup;
use Tourze\WechatBotBundle\Entity\WeChatMessage;
use Tourze\WechatBotBundle\Entity\WeChatMoment;
use Tourze\WechatBotBundle\Entity\WeChatTag;

/**
 * 微信机器人Bundle管理菜单服务
 *
 * 为EasyAdmin提供微信机器人相关的管理菜单配置
 *
 * @author AI Assistant
 */
#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        // 创建微信机器人顶级菜单
        if (null === $item->getChild('微信机器人')) {
            $item->addChild('微信机器人')
                ->setAttribute('icon', 'fa fa-wechat')
            ;
        }

        $wechatMenu = $item->getChild('微信机器人');
        if (null === $wechatMenu) {
            return;
        }

        // 微信账号管理
        $wechatMenu
            ->addChild('微信账号')
            ->setUri($this->linkGenerator->getCurdListPage(WeChatAccount::class))
            ->setAttribute('icon', 'fa fa-user')
        ;

        // 消息管理
        $wechatMenu
            ->addChild('消息管理')
            ->setUri($this->linkGenerator->getCurdListPage(WeChatMessage::class))
            ->setAttribute('icon', 'fa fa-comments')
        ;

        // 联系人管理
        $wechatMenu
            ->addChild('联系人管理')
            ->setUri($this->linkGenerator->getCurdListPage(WeChatContact::class))
            ->setAttribute('icon', 'fa fa-address-book')
        ;

        // 群组管理
        $wechatMenu
            ->addChild('群组管理')
            ->setUri($this->linkGenerator->getCurdListPage(WeChatGroup::class))
            ->setAttribute('icon', 'fa fa-users')
        ;

        // 朋友圈管理
        $wechatMenu
            ->addChild('朋友圈管理')
            ->setUri($this->linkGenerator->getCurdListPage(WeChatMoment::class))
            ->setAttribute('icon', 'fa fa-circle')
        ;

        // 标签管理
        $wechatMenu
            ->addChild('标签管理')
            ->setUri($this->linkGenerator->getCurdListPage(WeChatTag::class))
            ->setAttribute('icon', 'fa fa-tags')
        ;

        // API账号管理
        $wechatMenu
            ->addChild('API账号')
            ->setUri($this->linkGenerator->getCurdListPage(WeChatApiAccount::class))
            ->setAttribute('icon', 'fa fa-key')
        ;
    }
}
