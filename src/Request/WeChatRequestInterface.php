<?php

namespace Tourze\WechatBotBundle\Request;

use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * 微信API请求接口
 * 所有微信相关的请求都应该实现此接口
 */
interface WeChatRequestInterface
{
    /**
     * 获取API账号配置
     */
    public function getApiAccount(): WeChatApiAccount;
}
