<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatGroup;

class WeChatGroupFixtures extends Fixture implements DependentFixtureInterface
{
    public const GROUP_ACTIVE_1 = 'wechat-group-active-1';
    public const GROUP_ACTIVE_2 = 'wechat-group-active-2';
    public const GROUP_INACTIVE = 'wechat-group-inactive';

    public function load(ObjectManager $manager): void
    {
        // 第一个活跃群组
        $activeGroup1 = new WeChatGroup();
        $activeGroup1->setAccount($this->getReference(WeChatAccountFixtures::ACCOUNT_ONLINE, WeChatAccount::class));
        $activeGroup1->setGroupId('fixtures-group-active-1-' . uniqid());
        $activeGroup1->setGroupName('测试活跃群组1');
        $activeGroup1->setRemarkName('我的重要群');
        $activeGroup1->setAvatar('data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect width="64" height="64" fill="#e74c3c"/><text x="32" y="38" text-anchor="middle" fill="white" font-family="Arial" font-size="12">Group1</text></svg>'));
        $activeGroup1->setOwnerId('owner_001');
        $activeGroup1->setOwnerName('群主小王');
        $activeGroup1->setMemberCount(25);
        $activeGroup1->setAnnouncement('欢迎加入测试群组！');
        $activeGroup1->setDescription('这是一个用于测试的群组');
        $activeGroup1->setInGroup(true);
        $activeGroup1->setJoinTime(new \DateTimeImmutable('-30 days'));
        $activeGroup1->setLastActiveTime(new \DateTimeImmutable('-1 hour'));
        $activeGroup1->setValid(true);

        $manager->persist($activeGroup1);
        $this->addReference(self::GROUP_ACTIVE_1, $activeGroup1);

        // 第二个活跃群组
        $activeGroup2 = new WeChatGroup();
        $activeGroup2->setAccount($this->getReference(WeChatAccountFixtures::ACCOUNT_ONLINE, WeChatAccount::class));
        $activeGroup2->setGroupId('fixtures-group-active-2-' . uniqid());
        $activeGroup2->setGroupName('测试活跃群组2');
        $activeGroup2->setAvatar('data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect width="64" height="64" fill="#3498db"/><text x="32" y="38" text-anchor="middle" fill="white" font-family="Arial" font-size="12">Group2</text></svg>'));
        $activeGroup2->setOwnerId('owner_002');
        $activeGroup2->setOwnerName('群主小李');
        $activeGroup2->setMemberCount(15);
        $activeGroup2->setInGroup(true);
        $activeGroup2->setJoinTime(new \DateTimeImmutable('-15 days'));
        $activeGroup2->setLastActiveTime(new \DateTimeImmutable('-2 hours'));
        $activeGroup2->setValid(true);

        $manager->persist($activeGroup2);
        $this->addReference(self::GROUP_ACTIVE_2, $activeGroup2);

        // 非活跃群组（已退出的群）
        $inactiveGroup = new WeChatGroup();
        $inactiveGroup->setAccount($this->getReference(WeChatAccountFixtures::ACCOUNT_OFFLINE, WeChatAccount::class));
        $inactiveGroup->setGroupId('fixtures-group-inactive-' . uniqid());
        $inactiveGroup->setGroupName('测试非活跃群组');
        $inactiveGroup->setAvatar('data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect width="64" height="64" fill="#95a5a6"/><text x="32" y="38" text-anchor="middle" fill="white" font-family="Arial" font-size="12">Group3</text></svg>'));
        $inactiveGroup->setOwnerId('owner_003');
        $inactiveGroup->setOwnerName('群主小张');
        $inactiveGroup->setMemberCount(0);
        $inactiveGroup->setInGroup(false); // 已退出群组
        $inactiveGroup->setJoinTime(new \DateTimeImmutable('-60 days'));
        $inactiveGroup->setLastActiveTime(new \DateTimeImmutable('-30 days'));
        $inactiveGroup->setRemark('已退出的群组');
        $inactiveGroup->setValid(true);

        $manager->persist($inactiveGroup);
        $this->addReference(self::GROUP_INACTIVE, $inactiveGroup);

        $manager->flush();
    }

    /**
     * @return array<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            WeChatAccountFixtures::class,
        ];
    }
}
