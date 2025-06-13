<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Service;

/**
 * 微信机器人Bundle管理菜单服务
 * 
 * 为EasyAdmin提供微信机器人相关的管理菜单配置
 * 
 * @author AI Assistant
 */
class AdminMenu
{
    /**
     * 获取微信机器人管理菜单配置
     */
    public function getMenuItems(): array
    {
        return [
            [
                'label' => '微信机器人',
                'icon' => 'fa fa-wechat',
                'children' => [
                    [
                        'label' => '微信账号',
                        'icon' => 'fa fa-user',
                        'route' => 'wechat_bot_account',
                        'permission' => 'ROLE_ADMIN',
                        'description' => '管理微信机器人账号，查看登录状态'
                    ],
                    [
                        'label' => '消息管理',
                        'icon' => 'fa fa-comments',
                        'route' => 'wechat_bot_message',
                        'permission' => 'ROLE_ADMIN',
                        'description' => '查看和管理微信消息记录'
                    ],
                    [
                        'label' => '联系人管理',
                        'icon' => 'fa fa-address-book',
                        'route' => 'wechat_bot_contact',
                        'permission' => 'ROLE_ADMIN',
                        'description' => '管理微信好友和联系人'
                    ],
                    [
                        'label' => '群组管理',
                        'icon' => 'fa fa-users',
                        'route' => 'wechat_bot_group',
                        'permission' => 'ROLE_ADMIN',
                        'description' => '管理微信群组和群成员'
                    ],
                    [
                        'label' => 'API账号',
                        'icon' => 'fa fa-key',
                        'route' => 'wechat_bot_api_account',
                        'permission' => 'ROLE_SUPER_ADMIN',
                        'description' => '管理微信API平台账号配置'
                    ]
                ]
            ]
        ];
    }

    /**
     * 获取菜单标识
     */
    public function getMenuKey(): string
    {
        return 'wechat_bot';
    }

    /**
     * 获取菜单优先级
     */
    public function getMenuPriority(): int
    {
        return 100;
    }
} 