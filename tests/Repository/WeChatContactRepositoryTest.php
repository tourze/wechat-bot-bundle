<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\TestDox;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Entity\WeChatContact;
use Tourze\WechatBotBundle\Repository\WeChatContactRepository;

/**
 * 微信联系人仓储测试
 *
 * 测试微信联系人数据访问层的各种查询方法：
 * - 基础查询方法
 * - 按账号和类型过滤查询
 * - 搜索查询
 * - 统计查询
 *
 * @template-extends AbstractRepositoryTestCase<WeChatContact>
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(WeChatContactRepository::class)]
final class WeChatContactRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 创建测试数据以满足基类要求
        $entity = $this->createNewEntity();
        $account = $entity->getAccount();
        // createNewEntity 总是创建并设置 account，所以这里不会是 null
        $apiAccount = $account->getApiAccount();
        if (null !== $apiAccount) {
            self::getEntityManager()->persist($apiAccount);
        }
        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($entity);
        self::getEntityManager()->flush();
    }

    protected function createNewEntity(): WeChatContact
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

        $entity = new WeChatContact();
        $entity->setAccount($account);
        $entity->setContactId('test-contact-' . uniqid());
        $entity->setNickname('Test Contact');
        $entity->setContactType('friend');
        $entity->setValid(true);

        return $entity;
    }

    protected function getRepository(): WeChatContactRepository
    {
        return self::getService(WeChatContactRepository::class);
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

    #[TestDox('通过账号和联系人ID查找联系人')]
    public function testFindByAccountAndContactId(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $contact = new WeChatContact();
        $contact->setAccount($account);
        $contact->setContactId('test-contact-123');
        $contact->setNickname('Test Contact');
        $contact->setContactType('friend');
        $contact->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($contact);
        self::getEntityManager()->flush();

        $foundContact = $this->getRepository()->findByAccountAndContactId($account, 'test-contact-123');

        $this->assertInstanceOf(WeChatContact::class, $foundContact);
        $this->assertSame('test-contact-123', $foundContact->getContactId());
        $this->assertSame('Test Contact', $foundContact->getNickname());
        $this->assertSame($account, $foundContact->getAccount());
    }

    #[TestDox('通过账号和联系人ID查找不存在的联系人返回null')]
    public function testFindByAccountAndContactIdNotFound(): void
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

        $foundContact = $this->getRepository()->findByAccountAndContactId($account, 'non-existent-contact');

        $this->assertNull($foundContact);
    }

    #[TestDox('通过账号和联系人ID查找无效联系人返回null')]
    public function testFindByAccountAndContactIdInvalid(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $contact = new WeChatContact();
        $contact->setAccount($account);
        $contact->setContactId('test-contact-123');
        $contact->setNickname('Test Contact');
        $contact->setContactType('friend');
        $contact->setValid(false); // 无效联系人

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($contact);
        self::getEntityManager()->flush();

        $foundContact = $this->getRepository()->findByAccountAndContactId($account, 'test-contact-123');

        $this->assertNull($foundContact);
    }

    #[TestDox('查找账号的朋友联系人')]
    public function testFindFriendsByAccount(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $friend1 = new WeChatContact();
        $friend1->setAccount($account);
        $friend1->setContactId('friend-1');
        $friend1->setNickname('Friend A');
        $friend1->setContactType('friend');
        $friend1->setValid(true);

        $friend2 = new WeChatContact();
        $friend2->setAccount($account);
        $friend2->setContactId('friend-2');
        $friend2->setNickname('Friend B');
        $friend2->setContactType('friend');
        $friend2->setValid(true);

        $group = new WeChatContact();
        $group->setAccount($account);
        $group->setContactId('group-1');
        $group->setNickname('Group Chat');
        $group->setContactType('group');
        $group->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($friend1);
        self::getEntityManager()->persist($friend2);
        self::getEntityManager()->persist($group);
        self::getEntityManager()->flush();

        $friends = $this->getRepository()->findFriendsByAccount($account);
        $friendsList = array_values($friends);

        $this->assertCount(2, $friends);
        // 验证按昵称ASC排序
        $this->assertSame('Friend A', $friendsList[0]->getNickname());
        $this->assertSame('Friend B', $friendsList[1]->getNickname());
        $this->assertSame('friend', $friendsList[0]->getContactType());
        $this->assertSame('friend', $friendsList[1]->getContactType());
    }

    #[TestDox('通过账号和联系人类型查找联系人')]
    public function testFindByAccountAndType(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $friend = new WeChatContact();
        $friend->setAccount($account);
        $friend->setContactId('friend-1');
        $friend->setNickname('Friend Test');
        $friend->setContactType('friend');
        $friend->setValid(true);

        $group1 = new WeChatContact();
        $group1->setAccount($account);
        $group1->setContactId('group-1');
        $group1->setNickname('Group A');
        $group1->setContactType('group');
        $group1->setValid(true);

        $group2 = new WeChatContact();
        $group2->setAccount($account);
        $group2->setContactId('group-2');
        $group2->setNickname('Group B');
        $group2->setContactType('group');
        $group2->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($friend);
        self::getEntityManager()->persist($group1);
        self::getEntityManager()->persist($group2);
        self::getEntityManager()->flush();

        $groups = $this->getRepository()->findByAccountAndType($account, 'group');
        $groupsList = array_values($groups);

        $this->assertCount(2, $groups);
        // 验证按昵称ASC排序
        $this->assertSame('Group A', $groupsList[0]->getNickname());
        $this->assertSame('Group B', $groupsList[1]->getNickname());
        $this->assertSame('group', $groupsList[0]->getContactType());
        $this->assertSame('group', $groupsList[1]->getContactType());
    }

    #[TestDox('通过名称搜索联系人')]
    public function testSearchByName(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $contact1 = new WeChatContact();
        $contact1->setAccount($account);
        $contact1->setContactId('contact-1');
        $contact1->setNickname('Alice Smith');
        $contact1->setContactType('friend');
        $contact1->setValid(true);

        $contact2 = new WeChatContact();
        $contact2->setAccount($account);
        $contact2->setContactId('contact-2');
        $contact2->setNickname('Bob Johnson');
        $contact2->setRemarkName('Alice Friend');
        $contact2->setContactType('friend');
        $contact2->setValid(true);

        $contact3 = new WeChatContact();
        $contact3->setAccount($account);
        $contact3->setContactId('alice-contact-3');
        $contact3->setNickname('Charlie Brown');
        $contact3->setContactType('friend');
        $contact3->setValid(true);

        $contact4 = new WeChatContact();
        $contact4->setAccount($account);
        $contact4->setContactId('contact-4');
        $contact4->setNickname('David Wilson');
        $contact4->setContactType('friend');
        $contact4->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($contact1);
        self::getEntityManager()->persist($contact2);
        self::getEntityManager()->persist($contact3);
        self::getEntityManager()->persist($contact4);
        self::getEntityManager()->flush();

        $results = $this->getRepository()->searchByName($account, 'Alice');

        $this->assertCount(3, $results);
        $nicknames = array_map(fn (WeChatContact $contact) => $contact->getNickname(), $results);
        $this->assertContains('Alice Smith', $nicknames); // 昵称匹配
        $this->assertContains('Bob Johnson', $nicknames); // 备注名匹配
        $this->assertContains('Charlie Brown', $nicknames); // 联系人ID匹配
        $this->assertNotContains('David Wilson', $nicknames); // 不匹配
    }

    #[TestDox('通过名称搜索联系人时只返回有效联系人')]
    public function testSearchByNameOnlyValidContacts(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $validContact = new WeChatContact();
        $validContact->setAccount($account);
        $validContact->setContactId('valid-contact');
        $validContact->setNickname('Valid Alice');
        $validContact->setContactType('friend');
        $validContact->setValid(true);

        $invalidContact = new WeChatContact();
        $invalidContact->setAccount($account);
        $invalidContact->setContactId('invalid-contact');
        $invalidContact->setNickname('Invalid Alice');
        $invalidContact->setContactType('friend');
        $invalidContact->setValid(false);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($validContact);
        self::getEntityManager()->persist($invalidContact);
        self::getEntityManager()->flush();

        $results = $this->getRepository()->searchByName($account, 'Alice');
        $resultsList = array_values($results);

        $this->assertCount(1, $results);
        $this->assertSame('Valid Alice', $resultsList[0]->getNickname());
    }

    #[TestDox('按账号和类型统计联系人数量')]
    public function testCountByAccountAndType(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建朋友联系人
        for ($i = 0; $i < 3; ++$i) {
            $friend = new WeChatContact();
            $friend->setAccount($account);
            $friend->setContactId('friend-' . $i);
            $friend->setNickname('Friend ' . $i);
            $friend->setContactType('friend');
            $friend->setValid(true);
            self::getEntityManager()->persist($friend);
        }

        // 创建群聊联系人
        for ($i = 0; $i < 2; ++$i) {
            $group = new WeChatContact();
            $group->setAccount($account);
            $group->setContactId('group-' . $i);
            $group->setNickname('Group ' . $i);
            $group->setContactType('group');
            $group->setValid(true);
            self::getEntityManager()->persist($group);
        }

        // 创建无效联系人（不应该被统计）
        $invalidContact = new WeChatContact();
        $invalidContact->setAccount($account);
        $invalidContact->setContactId('invalid-contact');
        $invalidContact->setNickname('Invalid Contact');
        $invalidContact->setContactType('friend');
        $invalidContact->setValid(false);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($invalidContact);
        self::getEntityManager()->flush();

        $friendCount = $this->getRepository()->countByAccountAndType($account, 'friend');
        $groupCount = $this->getRepository()->countByAccountAndType($account, 'group');
        $unknownCount = $this->getRepository()->countByAccountAndType($account, 'unknown');

        $this->assertSame(3, $friendCount);
        $this->assertSame(2, $groupCount);
        $this->assertSame(0, $unknownCount);
    }

    #[TestDox('不同账号的联系人相互独立')]
    public function testContactsAreAccountSpecific(): void
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

        $contact1 = new WeChatContact();
        $contact1->setAccount($account1);
        $contact1->setContactId('contact-1');
        $contact1->setNickname('Contact 1');
        $contact1->setContactType('friend');
        $contact1->setValid(true);

        $contact2 = new WeChatContact();
        $contact2->setAccount($account2);
        $contact2->setContactId('contact-2');
        $contact2->setNickname('Contact 2');
        $contact2->setContactType('friend');
        $contact2->setValid(true);

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->persist($contact1);
        self::getEntityManager()->persist($contact2);
        self::getEntityManager()->flush();

        $account1Contacts = $this->getRepository()->findFriendsByAccount($account1);
        $account2Contacts = $this->getRepository()->findFriendsByAccount($account2);
        $account1ContactsList = array_values($account1Contacts);
        $account2ContactsList = array_values($account2Contacts);

        $this->assertCount(1, $account1Contacts);
        $this->assertCount(1, $account2Contacts);
        $this->assertSame('Contact 1', $account1ContactsList[0]->getNickname());
        $this->assertSame('Contact 2', $account2ContactsList[0]->getNickname());
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

        $this->assertNull($this->getRepository()->findByAccountAndContactId($account, 'any-contact'));
        $this->assertEmpty($this->getRepository()->findFriendsByAccount($account));
        $this->assertEmpty($this->getRepository()->findByAccountAndType($account, 'friend'));
        $this->assertEmpty($this->getRepository()->searchByName($account, 'any-name'));
        $this->assertSame(0, $this->getRepository()->countByAccountAndType($account, 'friend'));
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

        $contact = new WeChatContact();
        $contact->setAccount($account);
        $contact->setContactId('new-contact');
        $contact->setNickname('New Contact');
        $contact->setContactType('friend');
        $contact->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $this->getRepository()->save($contact, true);

        $foundContact = $this->getRepository()->findOneBy(['contactId' => 'new-contact']);
        $this->assertInstanceOf(WeChatContact::class, $foundContact);
        $this->assertSame('new-contact', $foundContact->getContactId());
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

        $contact = new WeChatContact();
        $contact->setAccount($account);
        $contact->setContactId('to-delete');
        $contact->setNickname('To Delete');
        $contact->setContactType('friend');
        $contact->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($contact);
        self::getEntityManager()->flush();

        $this->getRepository()->remove($contact, true);

        $foundContact = $this->getRepository()->findOneBy(['contactId' => 'to-delete']);
        $this->assertNull($foundContact);
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

        $contact1 = new WeChatContact();
        $contact1->setAccount($account1);
        $contact1->setContactId('contact-1');
        $contact1->setNickname('Contact 1');
        $contact1->setContactType('friend');
        $contact1->setValid(true);

        $contact2 = new WeChatContact();
        $contact2->setAccount($account2);
        $contact2->setContactId('contact-2');
        $contact2->setNickname('Contact 2');
        $contact2->setContactType('friend');
        $contact2->setValid(true);

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->persist($contact1);
        self::getEntityManager()->persist($contact2);
        self::getEntityManager()->flush();

        // 测试按关联实体查询
        $contactsForAccount1 = $this->getRepository()->findBy(['account' => $account1]);
        $contactsForAccount2 = $this->getRepository()->findBy(['account' => $account2]);
        $contactsForAccount1List = array_values($contactsForAccount1);
        $contactsForAccount2List = array_values($contactsForAccount2);

        $this->assertCount(1, $contactsForAccount1);
        $this->assertCount(1, $contactsForAccount2);
        $this->assertSame($account1, $contactsForAccount1List[0]->getAccount());
        $this->assertSame($account2, $contactsForAccount2List[0]->getAccount());
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
            $contact = new WeChatContact();
            $contact->setAccount($account1);
            $contact->setContactId('contact-' . $i);
            $contact->setNickname('Contact ' . $i);
            $contact->setContactType('friend');
            $contact->setValid(true);
            self::getEntityManager()->persist($contact);
        }

        for ($i = 1; $i <= 2; ++$i) {
            $contact = new WeChatContact();
            $contact->setAccount($account2);
            $contact->setContactId('contact-acc2-' . $i);
            $contact->setNickname('Contact Acc2 ' . $i);
            $contact->setContactType('friend');
            $contact->setValid(true);
            self::getEntityManager()->persist($contact);
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

    #[TestDox('查询可空字段为NULL的记录')]
    public function testFindByNullableFields(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $contactWithRemark = new WeChatContact();
        $contactWithRemark->setAccount($account);
        $contactWithRemark->setContactId('contact-with-remark');
        $contactWithRemark->setNickname('Contact With Remark');
        $contactWithRemark->setRemarkName('My Friend');
        $contactWithRemark->setContactType('friend');
        $contactWithRemark->setValid(true);

        $contactWithoutRemark = new WeChatContact();
        $contactWithoutRemark->setAccount($account);
        $contactWithoutRemark->setContactId('contact-without-remark');
        $contactWithoutRemark->setNickname('Contact Without Remark');
        $contactWithoutRemark->setRemarkName(null);
        $contactWithoutRemark->setContactType('friend');
        $contactWithoutRemark->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($contactWithRemark);
        self::getEntityManager()->persist($contactWithoutRemark);
        self::getEntityManager()->flush();

        // 查询备注名为NULL的联系人
        $contactsWithoutRemark = $this->getRepository()->findBy(['remarkName' => null]);

        $this->assertGreaterThanOrEqual(1, \count($contactsWithoutRemark));

        // 查找我们刚创建的特定联系人
        $ourContact = null;
        foreach ($contactsWithoutRemark as $contact) {
            if ('contact-without-remark' === $contact->getContactId()) {
                $ourContact = $contact;
                break;
            }
        }

        $this->assertNotNull($ourContact, 'Should find our created contact without remark');
        $this->assertSame('contact-without-remark', $ourContact->getContactId());
        $this->assertNull($ourContact->getRemarkName());
    }

    #[TestDox('统计可空字段为NULL的记录数量')]
    public function testCountByNullableFields(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建有备注名的联系人
        for ($i = 1; $i <= 2; ++$i) {
            $contact = new WeChatContact();
            $contact->setAccount($account);
            $contact->setContactId('with-remark-' . $i);
            $contact->setNickname('With Remark ' . $i);
            $contact->setRemarkName('Remark ' . $i);
            $contact->setContactType('friend');
            $contact->setValid(true);
            self::getEntityManager()->persist($contact);
        }

        // 创建没有备注名的联系人
        for ($i = 1; $i <= 3; ++$i) {
            $contact = new WeChatContact();
            $contact->setAccount($account);
            $contact->setContactId('without-remark-' . $i);
            $contact->setNickname('Without Remark ' . $i);
            $contact->setRemarkName(null);
            $contact->setContactType('friend');
            $contact->setValid(true);
            self::getEntityManager()->persist($contact);
        }

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $countWithoutRemark = $this->getRepository()->count(['remarkName' => null]);

        // 验证至少创建了3个没有备注名的联系人（可能有其他测试的数据干扰）
        $this->assertGreaterThanOrEqual(3, $countWithoutRemark);

        // 验证我们创建的具体联系人确实存在
        $ourContacts = $this->getRepository()->findBy([
            'account' => $account,
            'remarkName' => null,
        ]);
        $this->assertCount(3, $ourContacts, 'Should find exactly 3 contacts without remark for our account');
    }

    // ================== 完整的可空字段测试 ==================

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

        // 创建有昵称的联系人
        $contactWithNickname = new WeChatContact();
        $contactWithNickname->setAccount($account);
        $contactWithNickname->setContactId('with-nickname');
        $contactWithNickname->setNickname('Test Nickname');
        $contactWithNickname->setContactType('friend');
        $contactWithNickname->setValid(true);

        // 创建没有昵称的联系人
        $contactWithoutNickname = new WeChatContact();
        $contactWithoutNickname->setAccount($account);
        $contactWithoutNickname->setContactId('without-nickname');
        $contactWithoutNickname->setNickname(null);
        $contactWithoutNickname->setContactType('friend');
        $contactWithoutNickname->setValid(true);

        // 创建有备注名的联系人
        $contactWithRemarkName = new WeChatContact();
        $contactWithRemarkName->setAccount($account);
        $contactWithRemarkName->setContactId('with-remark-name');
        $contactWithRemarkName->setRemarkName('Test Remark');
        $contactWithRemarkName->setContactType('friend');
        $contactWithRemarkName->setValid(true);

        // 创建有头像的联系人
        $contactWithAvatar = new WeChatContact();
        $contactWithAvatar->setAccount($account);
        $contactWithAvatar->setContactId('with-avatar');
        $contactWithAvatar->setAvatar('https://example.com/avatar.jpg');
        $contactWithAvatar->setContactType('friend');
        $contactWithAvatar->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($contactWithNickname);
        self::getEntityManager()->persist($contactWithoutNickname);
        self::getEntityManager()->persist($contactWithRemarkName);
        self::getEntityManager()->persist($contactWithAvatar);
        self::getEntityManager()->flush();

        // 测试查询昵称为NULL的联系人 - 仅查询属于我们账户的
        $contactsWithoutNickname = $this->getRepository()->findBy(['nickname' => null, 'account' => $account]);
        $this->assertCount(3, $contactsWithoutNickname); // without-nickname, with-remark-name, with-avatar

        // 测试查询备注名为NULL的联系人 - 仅查询属于我们账户的
        $contactsWithoutRemarkName = $this->getRepository()->findBy(['remarkName' => null, 'account' => $account]);
        $this->assertCount(3, $contactsWithoutRemarkName); // with-nickname, without-nickname, with-avatar

        // 测试查询头像为NULL的联系人 - 仅查询属于我们账户的
        $contactsWithoutAvatar = $this->getRepository()->findBy(['avatar' => null, 'account' => $account]);
        $this->assertCount(3, $contactsWithoutAvatar); // with-nickname, without-nickname, with-remark-name

        // 测试查询性别为NULL的联系人（所有联系人都没有设置性别） - 仅查询属于我们账户的
        $contactsWithoutGender = $this->getRepository()->findBy(['gender' => null, 'account' => $account]);
        $this->assertCount(4, $contactsWithoutGender);

        // 测试查询地区为NULL的联系人 - 仅查询属于我们账户的
        $contactsWithoutRegion = $this->getRepository()->findBy(['region' => null, 'account' => $account]);
        $this->assertCount(4, $contactsWithoutRegion);

        // 测试查询个性签名为NULL的联系人 - 仅查询属于我们账户的
        $contactsWithoutSignature = $this->getRepository()->findBy(['signature' => null, 'account' => $account]);
        $this->assertCount(4, $contactsWithoutSignature);

        // 测试查询标签为NULL的联系人 - 仅查询属于我们账户的
        $contactsWithoutTags = $this->getRepository()->findBy(['tags' => null, 'account' => $account]);
        $this->assertCount(4, $contactsWithoutTags);

        // 测试查询添加好友时间为NULL的联系人 - 仅查询属于我们账户的
        $contactsWithoutAddFriendTime = $this->getRepository()->findBy(['addFriendTime' => null, 'account' => $account]);
        $this->assertCount(4, $contactsWithoutAddFriendTime);

        // 测试查询最后聊天时间为NULL的联系人 - 仅查询属于我们账户的
        $contactsWithoutLastChatTime = $this->getRepository()->findBy(['lastChatTime' => null, 'account' => $account]);
        $this->assertCount(4, $contactsWithoutLastChatTime);

        // 测试查询备注为NULL的联系人 - 仅查询属于我们账户的
        $contactsWithoutRemark = $this->getRepository()->findBy(['remark' => null, 'account' => $account]);
        $this->assertCount(4, $contactsWithoutRemark);
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

        // 创建具有各种可空字段值的联系人
        $contactFull = new WeChatContact();
        $contactFull->setAccount($account);
        $contactFull->setContactId('full-contact');
        $contactFull->setNickname('Full Contact');
        $contactFull->setRemarkName('Full Remark');
        $contactFull->setAvatar('https://example.com/avatar.jpg');
        $contactFull->setGender('male');
        $contactFull->setRegion('Beijing');
        $contactFull->setSignature('My signature');
        $contactFull->setTags('tag1,tag2');
        $contactFull->setAddFriendTime(new \DateTimeImmutable());
        $contactFull->setLastChatTime(new \DateTimeImmutable());
        $contactFull->setRemark('test-remark');
        $contactFull->setContactType('friend');
        $contactFull->setValid(true);

        // 创建空字段联系人
        $contactEmpty = new WeChatContact();
        $contactEmpty->setAccount($account);
        $contactEmpty->setContactId('empty-contact');
        $contactEmpty->setContactType('friend');
        $contactEmpty->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($contactFull);
        self::getEntityManager()->persist($contactEmpty);
        self::getEntityManager()->flush();

        // 统计各个可空字段为NULL的记录数量 - 仅统计属于我们账户的记录
        $this->assertSame(1, $this->getRepository()->count(['nickname' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['remarkName' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['avatar' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['gender' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['region' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['signature' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['tags' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['addFriendTime' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['lastChatTime' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['remark' => null, 'account' => $account]));
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

        $contact1 = new WeChatContact();
        $contact1->setAccount($account);
        $contact1->setContactId('contact-1');
        $contact1->setNickname('Contact B');
        $contact1->setContactType('friend');
        $contact1->setValid(true);

        $contact2 = new WeChatContact();
        $contact2->setAccount($account);
        $contact2->setContactId('contact-2');
        $contact2->setNickname('Contact A');
        $contact2->setContactType('friend');
        $contact2->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($contact1);
        self::getEntityManager()->persist($contact2);
        self::getEntityManager()->flush();

        // 按昵称升序查询，应返回第一个匹配的记录 - 仅查询属于我们账户的
        $contact = $this->getRepository()->findOneBy(
            ['contactType' => 'friend', 'account' => $account],
            ['nickname' => 'ASC']
        );

        $this->assertInstanceOf(WeChatContact::class, $contact);
        $this->assertSame('Contact A', $contact->getNickname());

        // 按昵称降序查询，应返回第一个匹配的记录 - 仅查询属于我们账户的
        $contactDesc = $this->getRepository()->findOneBy(
            ['contactType' => 'friend', 'account' => $account],
            ['nickname' => 'DESC']
        );

        $this->assertInstanceOf(WeChatContact::class, $contactDesc);
        $this->assertSame('Contact B', $contactDesc->getNickname());
    }
}
