<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\TestDox;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Entity\WeChatMessage;
use Tourze\WechatBotBundle\Repository\WeChatMessageRepository;

/**
 * 微信消息仓储测试
 *
 * 测试微信消息数据访问层的各种查询方法：
 * - 基础查询方法
 * - 按账号过滤查询
 * - 按类型过滤查询（群组/私聊）
 * - 未读消息查询
 * - 统计查询
 *
 * @template-extends AbstractRepositoryTestCase<WeChatMessage>
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(WeChatMessageRepository::class)]
final class WeChatMessageRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 此测试类不需要特殊的初始化逻辑
    }

    protected function createNewEntity(): object
    {
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('Test API Account ' . uniqid());
        $apiAccount->setBaseUrl('http://localhost:8080');
        $apiAccount->setUsername('test_user_' . uniqid());
        $apiAccount->setPassword('test_password');
        $apiAccount->setValid(true);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device-' . uniqid());
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $entity = new WeChatMessage();
        $entity->setAccount($account);
        $entity->setMessageId('test-message-' . uniqid());
        $entity->setMessageType('text');
        $entity->setDirection('inbound');
        $entity->setSenderId('sender-' . uniqid());
        $entity->setSenderName('Test Sender');
        $entity->setContent('Test message content');
        $entity->setMessageTime(new \DateTimeImmutable());
        $entity->setValid(true);

        return $entity;
    }

    protected function getRepository(): WeChatMessageRepository
    {
        return self::getService(WeChatMessageRepository::class);
    }

    private function createApiAccount(): WeChatApiAccount
    {
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('Test API Account ' . uniqid());
        $apiAccount->setBaseUrl('http://localhost:8080');
        $apiAccount->setUsername('test_user_' . uniqid());
        $apiAccount->setPassword('test_password');
        $apiAccount->setValid(true);

        return $apiAccount;
    }

    #[TestDox('按账号查找消息')]
    public function testFindByAccount(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $message1 = new WeChatMessage();
        $message1->setAccount($account);
        $message1->setMessageId('msg-1');
        $message1->setContent('Message 1');
        $message1->setMessageType('text');
        $message1->setDirection('inbound');
        $message1->setMessageTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $message1->setValid(true);

        $message2 = new WeChatMessage();
        $message2->setAccount($account);
        $message2->setMessageId('msg-2');
        $message2->setContent('Message 2');
        $message2->setMessageType('text');
        $message2->setDirection('outbound');
        $message2->setMessageTime(new \DateTimeImmutable('2023-01-01 11:00:00'));
        $message2->setValid(true);

        $invalidMessage = new WeChatMessage();
        $invalidMessage->setAccount($account);
        $invalidMessage->setMessageId('invalid-msg');
        $invalidMessage->setContent('Invalid Message');
        $invalidMessage->setMessageType('text');
        $invalidMessage->setDirection('inbound');
        $invalidMessage->setMessageTime(new \DateTimeImmutable('2023-01-01 12:00:00'));
        $invalidMessage->setValid(false);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($message1);
        self::getEntityManager()->persist($message2);
        self::getEntityManager()->persist($invalidMessage);
        self::getEntityManager()->flush();

        $messages = $this->getRepository()->findByAccount($account, 10);

        $this->assertCount(2, $messages);
        // 验证按messageTime DESC排序
        $this->assertArrayHasKey(0, $messages);
        $this->assertArrayHasKey(1, $messages);
        $this->assertSame('msg-2', $messages[0]->getMessageId());
        $this->assertSame('msg-1', $messages[1]->getMessageId());
    }

    #[TestDox('按账号查找消息时应用数量限制')]
    public function testFindByAccountWithLimit(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建3条消息
        for ($i = 0; $i < 3; ++$i) {
            $message = new WeChatMessage();
            $message->setAccount($account);
            $message->setMessageId('msg-' . $i);
            $message->setContent('Message ' . $i);
            $message->setMessageType('text');
            $message->setDirection('inbound');
            $message->setMessageTime(new \DateTimeImmutable('2023-01-01 ' . (10 + $i) . ':00:00'));
            $message->setValid(true);
            self::getEntityManager()->persist($message);
        }

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $messages = $this->getRepository()->findByAccount($account, 2);

        $this->assertCount(2, $messages);
        // 验证返回最新的2条消息
        $this->assertArrayHasKey(0, $messages);
        $this->assertArrayHasKey(1, $messages);
        $this->assertSame('msg-2', $messages[0]->getMessageId());
        $this->assertSame('msg-1', $messages[1]->getMessageId());
    }

    #[TestDox('查找未读消息')]
    public function testFindUnreadMessages(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $unreadMessage1 = new WeChatMessage();
        $unreadMessage1->setAccount($account);
        $unreadMessage1->setMessageId('unread-1');
        $unreadMessage1->setContent('Unread Message 1');
        $unreadMessage1->setMessageType('text');
        $unreadMessage1->setDirection('inbound');
        $unreadMessage1->setIsRead(false);
        $unreadMessage1->setMessageTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $unreadMessage1->setValid(true);

        $unreadMessage2 = new WeChatMessage();
        $unreadMessage2->setAccount($account);
        $unreadMessage2->setMessageId('unread-2');
        $unreadMessage2->setContent('Unread Message 2');
        $unreadMessage2->setMessageType('text');
        $unreadMessage2->setDirection('inbound');
        $unreadMessage2->setIsRead(false);
        $unreadMessage2->setMessageTime(new \DateTimeImmutable('2023-01-01 11:00:00'));
        $unreadMessage2->setValid(true);

        $readMessage = new WeChatMessage();
        $readMessage->setAccount($account);
        $readMessage->setMessageId('read-msg');
        $readMessage->setContent('Read Message');
        $readMessage->setMessageType('text');
        $readMessage->setDirection('inbound');
        $readMessage->setIsRead(true);
        $readMessage->setMessageTime(new \DateTimeImmutable('2023-01-01 12:00:00'));
        $readMessage->setValid(true);

        $outboundMessage = new WeChatMessage();
        $outboundMessage->setAccount($account);
        $outboundMessage->setMessageId('outbound-msg');
        $outboundMessage->setContent('Outbound Message');
        $outboundMessage->setMessageType('text');
        $outboundMessage->setDirection('outbound');
        $outboundMessage->setIsRead(false);
        $outboundMessage->setMessageTime(new \DateTimeImmutable('2023-01-01 13:00:00'));
        $outboundMessage->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($unreadMessage1);
        self::getEntityManager()->persist($unreadMessage2);
        self::getEntityManager()->persist($readMessage);
        self::getEntityManager()->persist($outboundMessage);
        self::getEntityManager()->flush();

        $unreadMessages = $this->getRepository()->findUnreadMessages($account);

        $this->assertCount(2, $unreadMessages);
        // 验证按messageTime ASC排序
        $this->assertArrayHasKey(0, $unreadMessages);
        $this->assertArrayHasKey(1, $unreadMessages);
        $this->assertSame('unread-1', $unreadMessages[0]->getMessageId());
        $this->assertSame('unread-2', $unreadMessages[1]->getMessageId());
        $this->assertFalse($unreadMessages[0]->isRead());
        $this->assertFalse($unreadMessages[1]->isRead());
        $this->assertSame('inbound', $unreadMessages[0]->getDirection());
        $this->assertSame('inbound', $unreadMessages[1]->getDirection());
    }

    #[TestDox('查找群组消息')]
    public function testFindGroupMessages(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $groupMessage1 = new WeChatMessage();
        $groupMessage1->setAccount($account);
        $groupMessage1->setMessageId('group-msg-1');
        $groupMessage1->setContent('Group Message 1');
        $groupMessage1->setMessageType('text');
        $groupMessage1->setDirection('inbound');
        $groupMessage1->setGroupId('group-123');
        $groupMessage1->setMessageTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $groupMessage1->setValid(true);

        $groupMessage2 = new WeChatMessage();
        $groupMessage2->setAccount($account);
        $groupMessage2->setMessageId('group-msg-2');
        $groupMessage2->setContent('Group Message 2');
        $groupMessage2->setMessageType('text');
        $groupMessage2->setDirection('outbound');
        $groupMessage2->setGroupId('group-123');
        $groupMessage2->setMessageTime(new \DateTimeImmutable('2023-01-01 11:00:00'));
        $groupMessage2->setValid(true);

        $otherGroupMessage = new WeChatMessage();
        $otherGroupMessage->setAccount($account);
        $otherGroupMessage->setMessageId('other-group-msg');
        $otherGroupMessage->setContent('Other Group Message');
        $otherGroupMessage->setMessageType('text');
        $otherGroupMessage->setDirection('inbound');
        $otherGroupMessage->setGroupId('group-456');
        $otherGroupMessage->setMessageTime(new \DateTimeImmutable('2023-01-01 12:00:00'));
        $otherGroupMessage->setValid(true);

        $privateMessage = new WeChatMessage();
        $privateMessage->setAccount($account);
        $privateMessage->setMessageId('private-msg');
        $privateMessage->setContent('Private Message');
        $privateMessage->setMessageType('text');
        $privateMessage->setDirection('inbound');
        $privateMessage->setMessageTime(new \DateTimeImmutable('2023-01-01 13:00:00'));
        $privateMessage->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($groupMessage1);
        self::getEntityManager()->persist($groupMessage2);
        self::getEntityManager()->persist($otherGroupMessage);
        self::getEntityManager()->persist($privateMessage);
        self::getEntityManager()->flush();

        $groupMessages = $this->getRepository()->findGroupMessages($account, 'group-123', 10);

        $this->assertCount(2, $groupMessages);
        // 验证按messageTime DESC排序
        $this->assertArrayHasKey(0, $groupMessages);
        $this->assertArrayHasKey(1, $groupMessages);
        $this->assertSame('group-msg-2', $groupMessages[0]->getMessageId());
        $this->assertSame('group-msg-1', $groupMessages[1]->getMessageId());
        $this->assertSame('group-123', $groupMessages[0]->getGroupId());
        $this->assertSame('group-123', $groupMessages[1]->getGroupId());
    }

    #[TestDox('查找私聊消息')]
    public function testFindPrivateMessages(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $privateMessage1 = new WeChatMessage();
        $privateMessage1->setAccount($account);
        $privateMessage1->setMessageId('private-msg-1');
        $privateMessage1->setContent('Private Message 1');
        $privateMessage1->setMessageType('text');
        $privateMessage1->setDirection('inbound');
        $privateMessage1->setSenderId('contact-123');
        $privateMessage1->setMessageTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $privateMessage1->setValid(true);

        $privateMessage2 = new WeChatMessage();
        $privateMessage2->setAccount($account);
        $privateMessage2->setMessageId('private-msg-2');
        $privateMessage2->setContent('Private Message 2');
        $privateMessage2->setMessageType('text');
        $privateMessage2->setDirection('outbound');
        $privateMessage2->setReceiverId('contact-123');
        $privateMessage2->setMessageTime(new \DateTimeImmutable('2023-01-01 11:00:00'));
        $privateMessage2->setValid(true);

        $otherPrivateMessage = new WeChatMessage();
        $otherPrivateMessage->setAccount($account);
        $otherPrivateMessage->setMessageId('other-private-msg');
        $otherPrivateMessage->setContent('Other Private Message');
        $otherPrivateMessage->setMessageType('text');
        $otherPrivateMessage->setDirection('inbound');
        $otherPrivateMessage->setSenderId('contact-456');
        $otherPrivateMessage->setMessageTime(new \DateTimeImmutable('2023-01-01 12:00:00'));
        $otherPrivateMessage->setValid(true);

        $groupMessage = new WeChatMessage();
        $groupMessage->setAccount($account);
        $groupMessage->setMessageId('group-msg');
        $groupMessage->setContent('Group Message');
        $groupMessage->setMessageType('text');
        $groupMessage->setDirection('inbound');
        $groupMessage->setGroupId('group-123');
        $groupMessage->setMessageTime(new \DateTimeImmutable('2023-01-01 13:00:00'));
        $groupMessage->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($privateMessage1);
        self::getEntityManager()->persist($privateMessage2);
        self::getEntityManager()->persist($otherPrivateMessage);
        self::getEntityManager()->persist($groupMessage);
        self::getEntityManager()->flush();

        $privateMessages = $this->getRepository()->findPrivateMessages($account, 'contact-123', 10);

        $this->assertCount(2, $privateMessages);
        // 验证按messageTime DESC排序
        $this->assertArrayHasKey(0, $privateMessages);
        $this->assertArrayHasKey(1, $privateMessages);
        $this->assertSame('private-msg-2', $privateMessages[0]->getMessageId());
        $this->assertSame('private-msg-1', $privateMessages[1]->getMessageId());
        $this->assertNull($privateMessages[0]->getGroupId());
        $this->assertNull($privateMessages[1]->getGroupId());
    }

    #[TestDox('统计账号的未读消息数量')]
    public function testCountUnreadByAccount(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建未读入站消息
        for ($i = 0; $i < 3; ++$i) {
            $unreadMessage = new WeChatMessage();
            $unreadMessage->setAccount($account);
            $unreadMessage->setMessageId('unread-' . $i);
            $unreadMessage->setContent('Unread Message ' . $i);
            $unreadMessage->setMessageType('text');
            $unreadMessage->setDirection('inbound');
            $unreadMessage->setIsRead(false);
            $unreadMessage->setMessageTime(new \DateTimeImmutable());
            $unreadMessage->setValid(true);
            self::getEntityManager()->persist($unreadMessage);
        }

        // 创建已读入站消息
        $readMessage = new WeChatMessage();
        $readMessage->setAccount($account);
        $readMessage->setMessageId('read-msg');
        $readMessage->setContent('Read Message');
        $readMessage->setMessageType('text');
        $readMessage->setDirection('inbound');
        $readMessage->setIsRead(true);
        $readMessage->setMessageTime(new \DateTimeImmutable());
        $readMessage->setValid(true);

        // 创建未读出站消息（不应该被统计）
        $unreadOutboundMessage = new WeChatMessage();
        $unreadOutboundMessage->setAccount($account);
        $unreadOutboundMessage->setMessageId('unread-outbound');
        $unreadOutboundMessage->setContent('Unread Outbound Message');
        $unreadOutboundMessage->setMessageType('text');
        $unreadOutboundMessage->setDirection('outbound');
        $unreadOutboundMessage->setIsRead(false);
        $unreadOutboundMessage->setMessageTime(new \DateTimeImmutable());
        $unreadOutboundMessage->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($readMessage);
        self::getEntityManager()->persist($unreadOutboundMessage);
        self::getEntityManager()->flush();

        $unreadCount = $this->getRepository()->countUnreadByAccount($account);

        $this->assertSame(3, $unreadCount);
    }

    #[TestDox('按消息类型统计')]
    public function testCountByMessageType(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建不同类型的消息
        $messageTypes = [
            'text' => 3,
            'image' => 2,
            'voice' => 1,
        ];

        foreach ($messageTypes as $type => $count) {
            for ($i = 0; $i < $count; ++$i) {
                $message = new WeChatMessage();
                $message->setAccount($account);
                $message->setMessageId($type . '-msg-' . $i);
                $message->setContent('Message ' . $i);
                $message->setMessageType($type);
                $message->setDirection('inbound');
                $message->setMessageTime(new \DateTimeImmutable());
                $message->setValid(true);
                self::getEntityManager()->persist($message);
            }
        }

        // 创建无效消息（不应该被统计）
        $invalidMessage = new WeChatMessage();
        $invalidMessage->setAccount($account);
        $invalidMessage->setMessageId('invalid-msg');
        $invalidMessage->setContent('Invalid Message');
        $invalidMessage->setMessageType('text');
        $invalidMessage->setDirection('inbound');
        $invalidMessage->setMessageTime(new \DateTimeImmutable());
        $invalidMessage->setValid(false);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($invalidMessage);
        self::getEntityManager()->flush();

        $typeCounts = $this->getRepository()->countByMessageType($account);

        $this->assertSame(3, $typeCounts['text']);
        $this->assertSame(2, $typeCounts['image']);
        $this->assertSame(1, $typeCounts['voice']);
        $this->assertArrayNotHasKey('invalid', $typeCounts);
    }

    #[TestDox('不同账号的消息相互独立')]
    public function testMessagesAreAccountSpecific(): void
    {
        $apiAccount1 = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount1);
        $apiAccount2 = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount2);

        $account1 = new WeChatAccount();
        $account1->setApiAccount($apiAccount1);
        $account1->setDeviceId('test-device-1');
        $account1->setNickname('Test Account 1');
        $account1->setStatus('online');
        $account1->setValid(true);

        $account2 = new WeChatAccount();
        $account2->setApiAccount($apiAccount2);
        $account2->setDeviceId('test-device-2');
        $account2->setNickname('Test Account 2');
        $account2->setStatus('online');
        $account2->setValid(true);

        $message1 = new WeChatMessage();
        $message1->setAccount($account1);
        $message1->setMessageId('msg-1');
        $message1->setContent('Message 1');
        $message1->setMessageType('text');
        $message1->setDirection('inbound');
        $message1->setMessageTime(new \DateTimeImmutable());
        $message1->setValid(true);

        $message2 = new WeChatMessage();
        $message2->setAccount($account2);
        $message2->setMessageId('msg-2');
        $message2->setContent('Message 2');
        $message2->setMessageType('text');
        $message2->setDirection('inbound');
        $message2->setMessageTime(new \DateTimeImmutable());
        $message2->setValid(true);

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->persist($message1);
        self::getEntityManager()->persist($message2);
        self::getEntityManager()->flush();

        $account1Messages = $this->getRepository()->findByAccount($account1);
        $account2Messages = $this->getRepository()->findByAccount($account2);

        $this->assertCount(1, $account1Messages);
        $this->assertCount(1, $account2Messages);
        $this->assertArrayHasKey(0, $account1Messages);
        $this->assertArrayHasKey(0, $account2Messages);
        $this->assertSame('msg-1', $account1Messages[0]->getMessageId());
        $this->assertSame('msg-2', $account2Messages[0]->getMessageId());
    }

    #[TestDox('空数据库时的查询方法')]
    public function testEmptyDatabase(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $this->assertEmpty($this->getRepository()->findByAccount($account));
        $this->assertEmpty($this->getRepository()->findUnreadMessages($account));
        $this->assertEmpty($this->getRepository()->findGroupMessages($account, 'any-group'));
        $this->assertEmpty($this->getRepository()->findPrivateMessages($account, 'any-contact'));
        $this->assertSame(0, $this->getRepository()->countUnreadByAccount($account));
        $this->assertEmpty($this->getRepository()->countByMessageType($account));
    }

    // ================== 基础 Doctrine 方法测试 ==================

    #[TestDox('save方法应持久化新实体')]
    public function testSave(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $message = new WeChatMessage();
        $message->setAccount($account);
        $message->setMessageId('new-message');
        $message->setContent('New Message');
        $message->setMessageType('text');
        $message->setDirection('inbound');
        $message->setMessageTime(new \DateTimeImmutable());
        $message->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $this->getRepository()->save($message, true);

        $foundMessage = $this->getRepository()->findOneBy(['messageId' => 'new-message']);
        $this->assertInstanceOf(WeChatMessage::class, $foundMessage);
        $this->assertSame('new-message', $foundMessage->getMessageId());
    }

    #[TestDox('remove方法应删除实体')]
    public function testRemove(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $message = new WeChatMessage();
        $message->setAccount($account);
        $message->setMessageId('to-delete');
        $message->setContent('To Delete');
        $message->setMessageType('text');
        $message->setDirection('inbound');
        $message->setMessageTime(new \DateTimeImmutable());
        $message->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($message);
        self::getEntityManager()->flush();

        $this->getRepository()->remove($message, true);

        $foundMessage = $this->getRepository()->findOneBy(['messageId' => 'to-delete']);
        $this->assertNull($foundMessage);
    }

    // ================== 健壮性测试 ==================

    // ================== 关联查询测试 ==================

    #[TestDox('查询包含关联实体')]
    public function testQueryWithAssociations(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account1 = new WeChatAccount();
        $account1->setApiAccount($apiAccount);
        $account1->setDeviceId('test-device-1');
        $account1->setNickname('Test Account 1');
        $account1->setStatus('online');
        $account1->setValid(true);

        $account2 = new WeChatAccount();
        $account2->setApiAccount($apiAccount);
        $account2->setDeviceId('test-device-2');
        $account2->setNickname('Test Account 2');
        $account2->setStatus('online');
        $account2->setValid(true);

        $message1 = new WeChatMessage();
        $message1->setAccount($account1);
        $message1->setMessageId('message-1');
        $message1->setContent('Message 1');
        $message1->setMessageType('text');
        $message1->setDirection('inbound');
        $message1->setMessageTime(new \DateTimeImmutable());
        $message1->setValid(true);

        $message2 = new WeChatMessage();
        $message2->setAccount($account2);
        $message2->setMessageId('message-2');
        $message2->setContent('Message 2');
        $message2->setMessageType('text');
        $message2->setDirection('inbound');
        $message2->setMessageTime(new \DateTimeImmutable());
        $message2->setValid(true);

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->persist($message1);
        self::getEntityManager()->persist($message2);
        self::getEntityManager()->flush();

        // 测试按关联实体查询
        $messagesForAccount1 = $this->getRepository()->findBy(['account' => $account1]);
        $messagesForAccount2 = $this->getRepository()->findBy(['account' => $account2]);

        $this->assertCount(1, $messagesForAccount1);
        $this->assertCount(1, $messagesForAccount2);
        $this->assertArrayHasKey(0, $messagesForAccount1);
        $this->assertArrayHasKey(0, $messagesForAccount2);
        $this->assertInstanceOf(WeChatMessage::class, $messagesForAccount1[0]);
        $this->assertInstanceOf(WeChatMessage::class, $messagesForAccount2[0]);
        $this->assertSame($account1, $messagesForAccount1[0]->getAccount());
        $this->assertSame($account2, $messagesForAccount2[0]->getAccount());
    }

    #[TestDox('统计关联查询')]
    public function testCountWithAssociations(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account1 = new WeChatAccount();
        $account1->setApiAccount($apiAccount);
        $account1->setDeviceId('test-device-1');
        $account1->setNickname('Test Account 1');
        $account1->setStatus('online');
        $account1->setValid(true);

        $account2 = new WeChatAccount();
        $account2->setApiAccount($apiAccount);
        $account2->setDeviceId('test-device-2');
        $account2->setNickname('Test Account 2');
        $account2->setStatus('online');
        $account2->setValid(true);

        for ($i = 1; $i <= 3; ++$i) {
            $message = new WeChatMessage();
            $message->setAccount($account1);
            $message->setMessageId('message-' . $i);
            $message->setContent('Message ' . $i);
            $message->setMessageType('text');
            $message->setDirection('inbound');
            $message->setMessageTime(new \DateTimeImmutable());
            $message->setValid(true);
            self::getEntityManager()->persist($message);
        }

        for ($i = 1; $i <= 2; ++$i) {
            $message = new WeChatMessage();
            $message->setAccount($account2);
            $message->setMessageId('message-acc2-' . $i);
            $message->setContent('Message Acc2 ' . $i);
            $message->setMessageType('text');
            $message->setDirection('inbound');
            $message->setMessageTime(new \DateTimeImmutable());
            $message->setValid(true);
            self::getEntityManager()->persist($message);
        }

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->flush();

        $account1Count = $this->getRepository()->count(['account' => $account1]);
        $account2Count = $this->getRepository()->count(['account' => $account2]);

        $this->assertSame(3, $account1Count);
        $this->assertSame(2, $account2Count);
    }

    // ================== NULL 查询测试 ==================

    #[TestDox('查询所有可空字段为NULL的记录')]
    public function testFindByAllNullableFields(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建有消息ID的消息
        $messageWithId = new WeChatMessage();
        $messageWithId->setAccount($account);
        $messageWithId->setMessageId('test-message-id');
        $messageWithId->setContent('Test message with ID');
        $messageWithId->setMessageType('text');
        $messageWithId->setDirection('inbound');
        $messageWithId->setMessageTime(new \DateTimeImmutable());
        $messageWithId->setValid(true);

        // 创建没有消息ID的消息
        $messageWithoutId = new WeChatMessage();
        $messageWithoutId->setAccount($account);
        $messageWithoutId->setMessageId(null);
        $messageWithoutId->setContent('Test message without ID');
        $messageWithoutId->setMessageType('text');
        $messageWithoutId->setDirection('inbound');
        $messageWithoutId->setMessageTime(new \DateTimeImmutable());
        $messageWithoutId->setValid(true);

        // 创建有发送者ID的消息
        $messageWithSenderId = new WeChatMessage();
        $messageWithSenderId->setAccount($account);
        $messageWithSenderId->setSenderId('sender123');
        $messageWithSenderId->setContent('Message with sender ID');
        $messageWithSenderId->setMessageType('text');
        $messageWithSenderId->setDirection('inbound');
        $messageWithSenderId->setMessageTime(new \DateTimeImmutable());
        $messageWithSenderId->setValid(true);

        // 创建有媒体URL的消息
        $messageWithMediaUrl = new WeChatMessage();
        $messageWithMediaUrl->setAccount($account);
        $messageWithMediaUrl->setMediaUrl('https://example.com/media.jpg');
        $messageWithMediaUrl->setMessageType('image');
        $messageWithMediaUrl->setDirection('inbound');
        $messageWithMediaUrl->setMessageTime(new \DateTimeImmutable());
        $messageWithMediaUrl->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($messageWithId);
        self::getEntityManager()->persist($messageWithoutId);
        self::getEntityManager()->persist($messageWithSenderId);
        self::getEntityManager()->persist($messageWithMediaUrl);
        self::getEntityManager()->flush();

        // 测试查询消息ID为NULL的消息（限制在当前测试创建的账号下）
        $messagesWithoutId = $this->getRepository()->findBy(['messageId' => null, 'account' => $account]);
        $this->assertCount(3, $messagesWithoutId); // without-id, with-sender-id, with-media-url

        // 测试查询发送者ID为NULL的消息（限制在当前测试创建的账号下）
        $messagesWithoutSenderId = $this->getRepository()->findBy(['senderId' => null, 'account' => $account]);
        $this->assertCount(3, $messagesWithoutSenderId); // with-id, without-id, with-media-url

        // 测试查询发送者昵称为NULL的消息（限制在当前测试创建的账号下）
        $messagesWithoutSenderName = $this->getRepository()->findBy(['senderName' => null, 'account' => $account]);
        $this->assertCount(4, $messagesWithoutSenderName);

        // 测试查询接收者ID为NULL的消息（限制在当前测试创建的账号下）
        $messagesWithoutReceiverId = $this->getRepository()->findBy(['receiverId' => null, 'account' => $account]);
        $this->assertCount(4, $messagesWithoutReceiverId);

        // 测试查询接收者昵称为NULL的消息（限制在当前测试创建的账号下）
        $messagesWithoutReceiverName = $this->getRepository()->findBy(['receiverName' => null, 'account' => $account]);
        $this->assertCount(4, $messagesWithoutReceiverName);

        // 测试查询群组ID为NULL的消息（限制在当前测试创建的账号下）
        $messagesWithoutGroupId = $this->getRepository()->findBy(['groupId' => null, 'account' => $account]);
        $this->assertCount(4, $messagesWithoutGroupId);

        // 测试查询群组名称为NULL的消息（限制在当前测试创建的账号下）
        $messagesWithoutGroupName = $this->getRepository()->findBy(['groupName' => null, 'account' => $account]);
        $this->assertCount(4, $messagesWithoutGroupName);

        // 测试查询内容为NULL的消息（限制在当前测试创建的账号下）
        $messagesWithoutContent = $this->getRepository()->findBy(['content' => null, 'account' => $account]);
        $this->assertCount(1, $messagesWithoutContent); // with-media-url

        // 测试查询媒体URL为NULL的消息（限制在当前测试创建的账号下）
        $messagesWithoutMediaUrl = $this->getRepository()->findBy(['mediaUrl' => null, 'account' => $account]);
        $this->assertCount(3, $messagesWithoutMediaUrl); // with-id, without-id, with-sender-id

        // 测试查询媒体文件名为NULL的消息（限制在当前测试创建的账号下）
        $messagesWithoutMediaFileName = $this->getRepository()->findBy(['mediaFileName' => null, 'account' => $account]);
        $this->assertCount(4, $messagesWithoutMediaFileName);

        // 测试查询媒体文件大小为NULL的消息（限制在当前测试创建的账号下）
        $messagesWithoutMediaFileSize = $this->getRepository()->findBy(['mediaFileSize' => null, 'account' => $account]);
        $this->assertCount(4, $messagesWithoutMediaFileSize);

        // 测试查询原始数据为NULL的消息（限制在当前测试创建的账号下）
        $messagesWithoutRawData = $this->getRepository()->findBy(['rawData' => null, 'account' => $account]);
        $this->assertCount(4, $messagesWithoutRawData);

        // 测试查询已读时间为NULL的消息（限制在当前测试创建的账号下）
        $messagesWithoutReadTime = $this->getRepository()->findBy(['readTime' => null, 'account' => $account]);
        $this->assertCount(4, $messagesWithoutReadTime);
    }

    #[TestDox('统计所有可空字段为NULL的记录数量')]
    public function testCountByAllNullableFields(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 记录添加新数据前各字段为NULL的记录数量
        $initialCounts = [
            'messageId' => $this->getRepository()->count(['messageId' => null]),
            'senderId' => $this->getRepository()->count(['senderId' => null]),
            'senderName' => $this->getRepository()->count(['senderName' => null]),
            'receiverId' => $this->getRepository()->count(['receiverId' => null]),
            'receiverName' => $this->getRepository()->count(['receiverName' => null]),
            'groupId' => $this->getRepository()->count(['groupId' => null]),
            'groupName' => $this->getRepository()->count(['groupName' => null]),
            'content' => $this->getRepository()->count(['content' => null]),
            'mediaUrl' => $this->getRepository()->count(['mediaUrl' => null]),
            'mediaFileName' => $this->getRepository()->count(['mediaFileName' => null]),
            'mediaFileSize' => $this->getRepository()->count(['mediaFileSize' => null]),
            'rawData' => $this->getRepository()->count(['rawData' => null]),
            'readTime' => $this->getRepository()->count(['readTime' => null]),
        ];

        // 创建具有各种可空字段值的消息
        $messageFull = new WeChatMessage();
        $messageFull->setAccount($account);
        $messageFull->setMessageId('full-message');
        $messageFull->setSenderId('sender123');
        $messageFull->setSenderName('Full Sender');
        $messageFull->setReceiverId('receiver123');
        $messageFull->setReceiverName('Full Receiver');
        $messageFull->setGroupId('group123');
        $messageFull->setGroupName('Full Group');
        $messageFull->setContent('Full message content');
        $messageFull->setMediaUrl('https://example.com/media.jpg');
        $messageFull->setMediaFileName('media.jpg');
        $messageFull->setMediaFileSize(1024);
        $messageFull->setRawData('{"test": "data"}');
        $messageFull->setReadTime(new \DateTimeImmutable());
        $messageFull->setMessageType('text');
        $messageFull->setDirection('inbound');
        $messageFull->setMessageTime(new \DateTimeImmutable());
        $messageFull->setValid(true);

        // 创建空字段消息
        $messageEmpty = new WeChatMessage();
        $messageEmpty->setAccount($account);
        $messageEmpty->setMessageType('text');
        $messageEmpty->setDirection('inbound');
        $messageEmpty->setMessageTime(new \DateTimeImmutable());
        $messageEmpty->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($messageFull);
        self::getEntityManager()->persist($messageEmpty);
        self::getEntityManager()->flush();

        // 验证新增记录后，每个可空字段的NULL记录数量符合预期（初始数量 + 新增的NULL记录数）
        $this->assertSame($initialCounts['messageId'] + 1, $this->getRepository()->count(['messageId' => null]));
        $this->assertSame($initialCounts['senderId'] + 1, $this->getRepository()->count(['senderId' => null]));
        $this->assertSame($initialCounts['senderName'] + 1, $this->getRepository()->count(['senderName' => null]));
        $this->assertSame($initialCounts['receiverId'] + 1, $this->getRepository()->count(['receiverId' => null]));
        $this->assertSame($initialCounts['receiverName'] + 1, $this->getRepository()->count(['receiverName' => null]));
        $this->assertSame($initialCounts['groupId'] + 1, $this->getRepository()->count(['groupId' => null]));
        $this->assertSame($initialCounts['groupName'] + 1, $this->getRepository()->count(['groupName' => null]));
        $this->assertSame($initialCounts['content'] + 1, $this->getRepository()->count(['content' => null]));
        $this->assertSame($initialCounts['mediaUrl'] + 1, $this->getRepository()->count(['mediaUrl' => null]));
        $this->assertSame($initialCounts['mediaFileName'] + 1, $this->getRepository()->count(['mediaFileName' => null]));
        $this->assertSame($initialCounts['mediaFileSize'] + 1, $this->getRepository()->count(['mediaFileSize' => null]));
        $this->assertSame($initialCounts['rawData'] + 1, $this->getRepository()->count(['rawData' => null]));
        $this->assertSame($initialCounts['readTime'] + 1, $this->getRepository()->count(['readTime' => null]));
    }

    // ================== findOneBy 排序测试 ==================

    #[TestDox('findOneBy应遵循排序参数')]
    public function testFindOneByShouldRespectOrderByClause(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $message1 = new WeChatMessage();
        $message1->setAccount($account);
        $message1->setMessageId('message-1');
        $message1->setContent('Message B');
        $message1->setMessageType('text');
        $message1->setDirection('inbound');
        $message1->setMessageTime(new \DateTimeImmutable('2023-01-01'));
        $message1->setValid(true);

        $message2 = new WeChatMessage();
        $message2->setAccount($account);
        $message2->setMessageId('message-2');
        $message2->setContent('Message A');
        $message2->setMessageType('text');
        $message2->setDirection('inbound');
        $message2->setMessageTime(new \DateTimeImmutable('2023-01-02'));
        $message2->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($message1);
        self::getEntityManager()->persist($message2);
        self::getEntityManager()->flush();

        // 按内容升序查询，应返回第一个匹配的记录（限制在当前测试创建的账号下）
        $message = $this->getRepository()->findOneBy(
            ['messageType' => 'text', 'account' => $account],
            ['content' => 'ASC']
        );

        $this->assertInstanceOf(WeChatMessage::class, $message);
        $this->assertSame('Message A', $message->getContent());

        // 按内容降序查询，应返回第一个匹配的记录（限制在当前测试创建的账号下）
        $messageDesc = $this->getRepository()->findOneBy(
            ['messageType' => 'text', 'account' => $account],
            ['content' => 'DESC']
        );

        $this->assertInstanceOf(WeChatMessage::class, $messageDesc);
        $this->assertSame('Message B', $messageDesc->getContent());

        // 按消息时间升序查询（限制在当前测试创建的账号下）
        $messageByTime = $this->getRepository()->findOneBy(
            ['messageType' => 'text', 'account' => $account],
            ['messageTime' => 'ASC']
        );

        $this->assertInstanceOf(WeChatMessage::class, $messageByTime);
        $this->assertSame('Message B', $messageByTime->getContent()); // 2023-01-01的消息

        // 按消息时间降序查询（限制在当前测试创建的账号下）
        $messageByTimeDesc = $this->getRepository()->findOneBy(
            ['messageType' => 'text', 'account' => $account],
            ['messageTime' => 'DESC']
        );

        $this->assertInstanceOf(WeChatMessage::class, $messageByTimeDesc);
        $this->assertSame('Message A', $messageByTimeDesc->getContent()); // 2023-01-02的消息
    }
}
