<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatContact;
use Tourze\WechatBotBundle\Entity\WeChatMoment;

class WeChatMomentFixtures extends Fixture implements DependentFixtureInterface
{
    public const MOMENT_TEXT = 'wechat-moment-text';
    public const MOMENT_IMAGE = 'wechat-moment-image';
    public const MOMENT_LIKED = 'wechat-moment-liked';
    public const MOMENT_COMMENTED = 'wechat-moment-commented';

    public function load(ObjectManager $manager): void
    {
        $onlineAccount = $this->getReference(WeChatAccountFixtures::ACCOUNT_ONLINE, WeChatAccount::class);
        $testContact = $this->getReference(WeChatContactFixtures::CONTACT_FRIEND, WeChatContact::class);

        // 纯文字朋友圈
        $textMoment = new WeChatMoment();
        $textMoment->setAccount($onlineAccount);
        $textMoment->setMomentId('fixtures-moment-text-' . uniqid());
        $textMoment->setAuthorWxid($testContact->getContactId());
        $textMoment->setAuthorNickname($testContact->getNickname());
        $textMoment->setAuthorAvatar($testContact->getAvatar());
        $textMoment->setMomentType('text');
        $textMoment->setTextContent('今天天气真不错，心情也很好！ #美好生活');
        $textMoment->setPublishTime(new \DateTimeImmutable('-2 hours'));
        $textMoment->setLikeCount(5);
        $textMoment->setCommentCount(2);
        $textMoment->setIsLiked(false);
        $textMoment->setValid(true);

        $manager->persist($textMoment);
        $this->addReference(self::MOMENT_TEXT, $textMoment);

        // 图片朋友圈
        $imageMoment = new WeChatMoment();
        $imageMoment->setAccount($onlineAccount);
        $imageMoment->setMomentId('fixtures-moment-image-' . uniqid());
        $imageMoment->setAuthorWxid($testContact->getContactId());
        $imageMoment->setAuthorNickname($testContact->getNickname());
        $imageMoment->setAuthorAvatar($testContact->getAvatar());
        $imageMoment->setMomentType('image');
        $imageMoment->setTextContent('分享一张美丽的风景照');
        $imageMoment->setImages([
            '/assets/images/placeholder-400x300.jpg',
            '/assets/images/placeholder-400x300-2.jpg',
        ]);
        $imageMoment->setPublishTime(new \DateTimeImmutable('-4 hours'));
        $imageMoment->setLocation('北京市朝阳区');
        $imageMoment->setLikeCount(12);
        $imageMoment->setCommentCount(4);
        $imageMoment->setIsLiked(true);
        $imageMoment->setValid(true);

        $manager->persist($imageMoment);
        $this->addReference(self::MOMENT_IMAGE, $imageMoment);

        // 已点赞朋友圈
        $likedMoment = new WeChatMoment();
        $likedMoment->setAccount($onlineAccount);
        $likedMoment->setMomentId('fixtures-moment-liked-' . uniqid());
        $likedMoment->setAuthorWxid($testContact->getContactId());
        $likedMoment->setAuthorNickname($testContact->getNickname());
        $likedMoment->setAuthorAvatar($testContact->getAvatar());
        $likedMoment->setMomentType('text');
        $likedMoment->setTextContent('分享一段心得体会');
        $likedMoment->setPublishTime(new \DateTimeImmutable('-6 hours'));
        $likedMoment->setLikeCount(8);
        $likedMoment->setCommentCount(1);
        $likedMoment->setIsLiked(true);
        $likedMoment->setValid(true);

        $manager->persist($likedMoment);
        $this->addReference(self::MOMENT_LIKED, $likedMoment);

        // 已评论朋友圈
        $commentedMoment = new WeChatMoment();
        $commentedMoment->setAccount($onlineAccount);
        $commentedMoment->setMomentId('fixtures-moment-commented-' . uniqid());
        $commentedMoment->setAuthorWxid($testContact->getContactId());
        $commentedMoment->setAuthorNickname($testContact->getNickname());
        $commentedMoment->setAuthorAvatar($testContact->getAvatar());
        $commentedMoment->setMomentType('video');
        $commentedMoment->setTextContent('精彩视频分享');
        $commentedMoment->setVideo(['url' => 'https://sample-videos.com/test.mp4', 'thumb' => '/assets/images/placeholder-200x200.jpg']);
        $commentedMoment->setPublishTime(new \DateTimeImmutable('-8 hours'));
        $commentedMoment->setLikeCount(20);
        $commentedMoment->setCommentCount(6);
        $commentedMoment->setIsLiked(false);
        $commentedMoment->setValid(true);

        $manager->persist($commentedMoment);
        $this->addReference(self::MOMENT_COMMENTED, $commentedMoment);

        $manager->flush();
    }

    /**
     * @return array<class-string<FixtureInterface>>
     */
    public function getDependencies(): array
    {
        return [
            WeChatAccountFixtures::class,
            WeChatContactFixtures::class,
        ];
    }
}
