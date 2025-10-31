<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatContact;

class WeChatContactFixtures extends Fixture implements DependentFixtureInterface
{
    public const CONTACT_FRIEND = 'wechat-contact-friend';
    public const CONTACT_BLOCKED = 'wechat-contact-blocked';
    public const CONTACT_COLLEAGUE = 'wechat-contact-colleague';
    public const CONTACT_FAMILY = 'wechat-contact-family';

    public function load(ObjectManager $manager): void
    {
        $onlineAccount = $this->getReference(WeChatAccountFixtures::ACCOUNT_ONLINE, WeChatAccount::class);

        // 好友联系人
        $friendContact = new WeChatContact();
        $friendContact->setAccount($onlineAccount);
        $friendContact->setContactId('fixtures-contact-friend-' . uniqid());
        $friendContact->setNickname('好友小张');
        $friendContact->setRemarkName('张三');
        $friendContact->setAvatar('data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect width="64" height="64" fill="#3498db"/><text x="32" y="38" text-anchor="middle" fill="white" font-family="Arial" font-size="12">Friend</text></svg>'));
        $friendContact->setGender('male');
        $friendContact->setRegion('北京市北京');
        $friendContact->setSignature('生活美好，友谊长存');
        $friendContact->setContactType('friend');
        $friendContact->setValid(true);

        $manager->persist($friendContact);
        $this->addReference(self::CONTACT_FRIEND, $friendContact);

        // 被拉黑的联系人
        $blockedContact = new WeChatContact();
        $blockedContact->setAccount($onlineAccount);
        $blockedContact->setContactId('fixtures-contact-blocked-' . uniqid());
        $blockedContact->setNickname('被拉黑用户');
        $blockedContact->setGender('unknown');
        $blockedContact->setContactType('blocked');
        $blockedContact->setValid(true);

        $manager->persist($blockedContact);
        $this->addReference(self::CONTACT_BLOCKED, $blockedContact);

        // 同事联系人
        $colleagueContact = new WeChatContact();
        $colleagueContact->setAccount($onlineAccount);
        $colleagueContact->setContactId('fixtures-contact-colleague-' . uniqid());
        $colleagueContact->setNickname('同事李四');
        $colleagueContact->setRemarkName('李四');
        $colleagueContact->setAvatar('data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect width="64" height="64" fill="#e67e22"/><text x="32" y="38" text-anchor="middle" fill="white" font-family="Arial" font-size="10">Colleague</text></svg>'));
        $colleagueContact->setGender('female');
        $colleagueContact->setRegion('上海市上海');
        $colleagueContact->setSignature('努力工作，快乐生活');
        $colleagueContact->setContactType('friend');
        $colleagueContact->setRemark('工作同事');
        $colleagueContact->setValid(true);

        $manager->persist($colleagueContact);
        $this->addReference(self::CONTACT_COLLEAGUE, $colleagueContact);

        // 家人联系人
        $familyContact = new WeChatContact();
        $familyContact->setAccount($onlineAccount);
        $familyContact->setContactId('fixtures-contact-family-' . uniqid());
        $familyContact->setNickname('家人');
        $familyContact->setRemarkName('妈妈');
        $familyContact->setAvatar('data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64"><rect width="64" height="64" fill="#e74c3c"/><text x="32" y="38" text-anchor="middle" fill="white" font-family="Arial" font-size="12">Family</text></svg>'));
        $familyContact->setGender('female');
        $familyContact->setRegion('广东省深圳市');
        $familyContact->setSignature('家和万事兴');
        $familyContact->setContactType('friend');
        $familyContact->setRemark('亲爱的妈妈');
        $familyContact->setValid(true);

        $manager->persist($familyContact);
        $this->addReference(self::CONTACT_FAMILY, $familyContact);

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
