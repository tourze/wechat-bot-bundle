<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

class WeChatAccountFixtures extends Fixture implements DependentFixtureInterface
{
    public const ACCOUNT_ONLINE = 'wechat-account-online';
    public const ACCOUNT_OFFLINE = 'wechat-account-offline';
    public const ACCOUNT_PENDING = 'wechat-account-pending';

    public function load(ObjectManager $manager): void
    {
        // 在线账号
        $onlineAccount = new WeChatAccount();
        $onlineAccount->setDeviceId('fixtures-device-online-' . uniqid());
        $onlineAccount->setWechatId('fixtures_wx_online_user_' . uniqid());
        $onlineAccount->setNickname('在线测试用户');
        $onlineAccount->setAvatar('data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect width="64" height="64" fill="#36d7b7"/><text x="32" y="38" text-anchor="middle" fill="white" font-family="Arial" font-size="12">Avatar1</text></svg>'));
        $onlineAccount->setStatus('online');
        $onlineAccount->setAccessToken('token_online_123');
        $onlineAccount->setLastActiveTime(new \DateTimeImmutable('now'));
        $onlineAccount->setLastLoginTime(new \DateTimeImmutable('-1 hour'));
        $onlineAccount->setApiAccount($this->getReference(WeChatApiAccountFixtures::API_ACCOUNT_MAIN, WeChatApiAccount::class));
        $onlineAccount->setValid(true);

        $manager->persist($onlineAccount);
        $this->addReference(self::ACCOUNT_ONLINE, $onlineAccount);

        // 离线账号
        $offlineAccount = new WeChatAccount();
        $offlineAccount->setDeviceId('fixtures-device-offline-' . uniqid());
        $offlineAccount->setWechatId('fixtures_wx_offline_user_' . uniqid());
        $offlineAccount->setNickname('离线测试用户');
        $offlineAccount->setAvatar('data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect width="64" height="64" fill="#f39c12"/><text x="32" y="38" text-anchor="middle" fill="white" font-family="Arial" font-size="12">Avatar2</text></svg>'));
        $offlineAccount->setStatus('offline');
        $offlineAccount->setLastActiveTime(new \DateTimeImmutable('-2 hours'));
        $offlineAccount->setLastLoginTime(new \DateTimeImmutable('-3 hours'));
        $offlineAccount->setApiAccount($this->getReference(WeChatApiAccountFixtures::API_ACCOUNT_MAIN, WeChatApiAccount::class));
        $offlineAccount->setValid(true);

        $manager->persist($offlineAccount);
        $this->addReference(self::ACCOUNT_OFFLINE, $offlineAccount);

        // 待登录账号
        $pendingAccount = new WeChatAccount();
        $pendingAccount->setDeviceId('fixtures-device-pending-' . uniqid());
        $pendingAccount->setStatus('pending_login');
        $pendingAccount->setQrCodeUrl('data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200"><rect width="200" height="200" fill="#2ecc71"/><text x="100" y="100" text-anchor="middle" fill="white" font-family="Arial" font-size="16">QR Code</text></svg>'));
        $pendingAccount->setApiAccount($this->getReference(WeChatApiAccountFixtures::API_ACCOUNT_MAIN, WeChatApiAccount::class));
        $pendingAccount->setValid(true);

        $manager->persist($pendingAccount);
        $this->addReference(self::ACCOUNT_PENDING, $pendingAccount);

        $manager->flush();
    }

    /**
     * @return array<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            WeChatApiAccountFixtures::class,
        ];
    }
}
