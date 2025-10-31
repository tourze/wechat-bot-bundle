<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatTag;

class WeChatTagFixtures extends Fixture implements DependentFixtureInterface
{
    public const TAG_WORK = 'wechat-tag-work';
    public const TAG_FAMILY = 'wechat-tag-family';
    public const TAG_FRIENDS = 'wechat-tag-friends';
    public const TAG_SYSTEM_VIP = 'wechat-tag-system-vip';
    public const TAG_EMPTY = 'wechat-tag-empty';

    public function load(ObjectManager $manager): void
    {
        $onlineAccount = $this->getReference(WeChatAccountFixtures::ACCOUNT_ONLINE, WeChatAccount::class);
        $offlineAccount = $this->getReference(WeChatAccountFixtures::ACCOUNT_OFFLINE, WeChatAccount::class);

        // 工作标签 - 在线账号
        $workTag = new WeChatTag();
        $workTag->setAccount($onlineAccount);
        $workTag->setTagId('tag_work_' . uniqid());
        $workTag->setTagName('工作');
        $workTag->setColor('blue');
        $workTag->setFriendList(['wxid_work1', 'wxid_work2', 'wxid_work3']);
        $workTag->setSortOrder(100);
        $workTag->setIsSystem(false);
        $workTag->setValid(true);
        $workTag->setRemark('工作相关联系人');

        $manager->persist($workTag);
        $this->addReference(self::TAG_WORK, $workTag);

        // 家庭标签 - 在线账号
        $familyTag = new WeChatTag();
        $familyTag->setAccount($onlineAccount);
        $familyTag->setTagId('tag_family_' . uniqid());
        $familyTag->setTagName('家庭');
        $familyTag->setColor('red');
        $familyTag->setFriendList(['wxid_mom', 'wxid_dad', 'wxid_brother']);
        $familyTag->setSortOrder(90);
        $familyTag->setIsSystem(false);
        $familyTag->setValid(true);
        $familyTag->setRemark('家庭成员');

        $manager->persist($familyTag);
        $this->addReference(self::TAG_FAMILY, $familyTag);

        // 朋友标签 - 在线账号
        $friendsTag = new WeChatTag();
        $friendsTag->setAccount($onlineAccount);
        $friendsTag->setTagId('tag_friends_' . uniqid());
        $friendsTag->setTagName('朋友');
        $friendsTag->setColor('green');
        $friendsTag->setFriendList(['wxid_friend1', 'wxid_friend2']);
        $friendsTag->setSortOrder(80);
        $friendsTag->setIsSystem(false);
        $friendsTag->setValid(true);

        $manager->persist($friendsTag);
        $this->addReference(self::TAG_FRIENDS, $friendsTag);

        // 系统VIP标签 - 在线账号
        $systemVipTag = new WeChatTag();
        $systemVipTag->setAccount($onlineAccount);
        $systemVipTag->setTagId('tag_system_vip_' . uniqid());
        $systemVipTag->setTagName('VIP用户');
        $systemVipTag->setColor('gold');
        $systemVipTag->setFriendList(['wxid_vip1', 'wxid_vip2', 'wxid_vip3', 'wxid_vip4']);
        $systemVipTag->setSortOrder(200);
        $systemVipTag->setIsSystem(true);
        $systemVipTag->setValid(true);
        $systemVipTag->setRemark('系统自动生成的VIP标签');

        $manager->persist($systemVipTag);
        $this->addReference(self::TAG_SYSTEM_VIP, $systemVipTag);

        // 空标签 - 离线账号
        $emptyTag = new WeChatTag();
        $emptyTag->setAccount($offlineAccount);
        $emptyTag->setTagId('tag_empty_' . uniqid());
        $emptyTag->setTagName('空标签');
        $emptyTag->setFriendList(null);
        $emptyTag->setSortOrder(10);
        $emptyTag->setIsSystem(false);
        $emptyTag->setValid(true);
        $emptyTag->setRemark('没有好友的标签');

        $manager->persist($emptyTag);
        $this->addReference(self::TAG_EMPTY, $emptyTag);

        // 无效标签 - 离线账号
        $invalidTag = new WeChatTag();
        $invalidTag->setAccount($offlineAccount);
        $invalidTag->setTagId('tag_invalid_' . uniqid());
        $invalidTag->setTagName('无效标签');
        $invalidTag->setColor('gray');
        $invalidTag->setFriendList(['wxid_old1']);
        $invalidTag->setSortOrder(5);
        $invalidTag->setIsSystem(false);
        $invalidTag->setValid(false);
        $invalidTag->setRemark('已废弃的标签');

        $manager->persist($invalidTag);

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
