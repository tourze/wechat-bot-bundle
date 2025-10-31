<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatMessage;

class WeChatMessageFixtures extends Fixture implements DependentFixtureInterface
{
    public const MESSAGE_TEXT_INBOUND = 'wechat-message-text-inbound';
    public const MESSAGE_TEXT_OUTBOUND = 'wechat-message-text-outbound';
    public const MESSAGE_IMAGE_INBOUND = 'wechat-message-image-inbound';
    public const MESSAGE_GROUP_MESSAGE = 'wechat-message-group';

    public function load(ObjectManager $manager): void
    {
        $onlineAccount = $this->getReference(WeChatAccountFixtures::ACCOUNT_ONLINE, WeChatAccount::class);

        // 入站文本消息
        $inboundTextMessage = new WeChatMessage();
        $inboundTextMessage->setAccount($onlineAccount);
        $inboundTextMessage->setMessageId('fixtures-msg-text-in-' . uniqid());
        $inboundTextMessage->setMessageType('text');
        $inboundTextMessage->setDirection('inbound');
        $inboundTextMessage->setSenderId('contact-123');
        $inboundTextMessage->setSenderName('测试联系人');
        $inboundTextMessage->setContent('这是一条入站文本消息');
        $inboundTextMessage->setMessageTime(new \DateTimeImmutable('-10 minutes'));
        $inboundTextMessage->setIsRead(false);
        $inboundTextMessage->setValid(true);

        $manager->persist($inboundTextMessage);
        $this->addReference(self::MESSAGE_TEXT_INBOUND, $inboundTextMessage);

        // 出站文本消息
        $outboundTextMessage = new WeChatMessage();
        $outboundTextMessage->setAccount($onlineAccount);
        $outboundTextMessage->setMessageId('fixtures-msg-text-out-' . uniqid());
        $outboundTextMessage->setMessageType('text');
        $outboundTextMessage->setDirection('outbound');
        $outboundTextMessage->setReceiverId('contact-123');
        $outboundTextMessage->setReceiverName('测试联系人');
        $outboundTextMessage->setContent('这是一条出站文本消息');
        $outboundTextMessage->setMessageTime(new \DateTimeImmutable('-5 minutes'));
        $outboundTextMessage->setIsRead(true);
        $outboundTextMessage->setValid(true);

        $manager->persist($outboundTextMessage);
        $this->addReference(self::MESSAGE_TEXT_OUTBOUND, $outboundTextMessage);

        // 入站图片消息
        $inboundImageMessage = new WeChatMessage();
        $inboundImageMessage->setAccount($onlineAccount);
        $inboundImageMessage->setMessageId('fixtures-msg-image-' . uniqid());
        $inboundImageMessage->setMessageType('image');
        $inboundImageMessage->setDirection('inbound');
        $inboundImageMessage->setSenderId('contact-456');
        $inboundImageMessage->setSenderName('图片发送者');
        $inboundImageMessage->setContent('[图片]');
        $inboundImageMessage->setMediaUrl('/assets/images/placeholder-300x200.jpg');
        $inboundImageMessage->setMediaFileName('test-image.jpg');
        $inboundImageMessage->setMediaFileSize(1024);
        $inboundImageMessage->setMessageTime(new \DateTimeImmutable('-15 minutes'));
        $inboundImageMessage->setIsRead(false);
        $inboundImageMessage->setValid(true);

        $manager->persist($inboundImageMessage);
        $this->addReference(self::MESSAGE_IMAGE_INBOUND, $inboundImageMessage);

        // 群组消息
        $groupMessage = new WeChatMessage();
        $groupMessage->setAccount($onlineAccount);
        $groupMessage->setMessageId('fixtures-msg-group-' . uniqid());
        $groupMessage->setMessageType('text');
        $groupMessage->setDirection('inbound');
        $groupMessage->setGroupId('group-789');
        $groupMessage->setGroupName('测试群组');
        $groupMessage->setSenderId('group-member-123');
        $groupMessage->setSenderName('群组成员');
        $groupMessage->setContent('这是一条群组消息');
        $groupMessage->setMessageTime(new \DateTimeImmutable('-20 minutes'));
        $groupMessage->setIsRead(true);
        $groupMessage->setValid(true);

        $manager->persist($groupMessage);
        $this->addReference(self::MESSAGE_GROUP_MESSAGE, $groupMessage);

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
