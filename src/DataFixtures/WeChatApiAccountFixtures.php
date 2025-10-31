<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

class WeChatApiAccountFixtures extends Fixture
{
    public const API_ACCOUNT_MAIN = 'wechat-api-account-main';

    public function load(ObjectManager $manager): void
    {
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('测试API账号-' . uniqid());
        $apiAccount->setBaseUrl('https://jsonplaceholder.typicode.com');
        $apiAccount->setUsername('test_user_' . uniqid());
        $apiAccount->setPassword('test_password');
        $apiAccount->setValid(true);
        $apiAccount->setRemark('测试环境API账号');

        $manager->persist($apiAccount);
        $this->addReference(self::API_ACCOUNT_MAIN, $apiAccount);

        $manager->flush();
    }
}
