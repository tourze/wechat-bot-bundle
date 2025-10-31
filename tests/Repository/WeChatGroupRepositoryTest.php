<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\TestDox;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Entity\WeChatGroup;
use Tourze\WechatBotBundle\Repository\WeChatGroupRepository;

/**
 * 微信群组仓储测试
 *
 * 测试微信群组数据访问层的各种查询方法：
 * - 基础查询方法
 * - 按账号过滤查询
 * - 活跃群组查询
 * - 搜索查询
 * - 统计查询
 *
 * @template-extends AbstractRepositoryTestCase<WeChatGroup>
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(WeChatGroupRepository::class)]
final class WeChatGroupRepositoryTest extends AbstractRepositoryTestCase
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

        $entity = new WeChatGroup();
        $entity->setAccount($account);
        $entity->setGroupId('test-group-' . uniqid());
        $entity->setGroupName('Test Group');
        $entity->setMemberCount(5);
        $entity->setValid(true);

        return $entity;
    }

    protected function getRepository(): WeChatGroupRepository
    {
        return self::getService(WeChatGroupRepository::class);
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

    #[TestDox('通过账号和群组ID查找群组')]
    public function testFindByAccountAndGroupId(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $group = new WeChatGroup();
        $group->setAccount($account);
        $group->setGroupId('test-group-123');
        $group->setGroupName('Test Group');
        $group->setInGroup(true);
        $group->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($group);
        self::getEntityManager()->flush();

        $foundGroup = $this->getRepository()->findByAccountAndGroupId($account, 'test-group-123');

        $this->assertInstanceOf(WeChatGroup::class, $foundGroup);
        $this->assertSame('test-group-123', $foundGroup->getGroupId());
        $this->assertSame('Test Group', $foundGroup->getGroupName());
        $this->assertSame($account, $foundGroup->getAccount());
    }

    #[TestDox('通过账号和群组ID查找不存在的群组返回null')]
    public function testFindByAccountAndGroupIdNotFound(): void
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

        $foundGroup = $this->getRepository()->findByAccountAndGroupId($account, 'non-existent-group');

        $this->assertNull($foundGroup);
    }

    #[TestDox('通过账号和群组ID查找无效群组返回null')]
    public function testFindByAccountAndGroupIdInvalid(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $group = new WeChatGroup();
        $group->setAccount($account);
        $group->setGroupId('test-group-123');
        $group->setGroupName('Test Group');
        $group->setInGroup(true);
        $group->setValid(false); // 无效群组

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($group);
        self::getEntityManager()->flush();

        $foundGroup = $this->getRepository()->findByAccountAndGroupId($account, 'test-group-123');

        $this->assertNull($foundGroup);
    }

    #[TestDox('查找账号的活跃群组')]
    public function testFindActiveGroupsByAccount(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $activeGroup1 = new WeChatGroup();
        $activeGroup1->setAccount($account);
        $activeGroup1->setGroupId('active-group-1');
        $activeGroup1->setGroupName('Active Group A');
        $activeGroup1->setInGroup(true);
        $activeGroup1->setValid(true);

        $activeGroup2 = new WeChatGroup();
        $activeGroup2->setAccount($account);
        $activeGroup2->setGroupId('active-group-2');
        $activeGroup2->setGroupName('Active Group B');
        $activeGroup2->setInGroup(true);
        $activeGroup2->setValid(true);

        $inactiveGroup = new WeChatGroup();
        $inactiveGroup->setAccount($account);
        $inactiveGroup->setGroupId('inactive-group');
        $inactiveGroup->setGroupName('Inactive Group');
        $inactiveGroup->setInGroup(false);
        $inactiveGroup->setValid(true);

        $invalidGroup = new WeChatGroup();
        $invalidGroup->setAccount($account);
        $invalidGroup->setGroupId('invalid-group');
        $invalidGroup->setGroupName('Invalid Group');
        $invalidGroup->setInGroup(true);
        $invalidGroup->setValid(false);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($activeGroup1);
        self::getEntityManager()->persist($activeGroup2);
        self::getEntityManager()->persist($inactiveGroup);
        self::getEntityManager()->persist($invalidGroup);
        self::getEntityManager()->flush();

        $activeGroups = $this->getRepository()->findActiveGroupsByAccount($account);

        $this->assertCount(2, $activeGroups);
        // 验证按群组名称ASC排序
        $this->assertArrayHasKey(0, $activeGroups);
        $this->assertArrayHasKey(1, $activeGroups);
        $this->assertSame('Active Group A', $activeGroups[0]->getGroupName());
        $this->assertSame('Active Group B', $activeGroups[1]->getGroupName());
        $this->assertTrue($activeGroups[0]->isInGroup());
        $this->assertTrue($activeGroups[1]->isInGroup());
    }

    #[TestDox('查找账号的所有群组')]
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

        $group1 = new WeChatGroup();
        $group1->setAccount($account);
        $group1->setGroupId('group-1');
        $group1->setGroupName('Group A');
        $group1->setInGroup(true);
        $group1->setValid(true);

        $group2 = new WeChatGroup();
        $group2->setAccount($account);
        $group2->setGroupId('group-2');
        $group2->setGroupName('Group B');
        $group2->setInGroup(false);
        $group2->setValid(true);

        $invalidGroup = new WeChatGroup();
        $invalidGroup->setAccount($account);
        $invalidGroup->setGroupId('invalid-group');
        $invalidGroup->setGroupName('Invalid Group');
        $invalidGroup->setInGroup(true);
        $invalidGroup->setValid(false);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($group1);
        self::getEntityManager()->persist($group2);
        self::getEntityManager()->persist($invalidGroup);
        self::getEntityManager()->flush();

        $groups = $this->getRepository()->findByAccount($account);

        $this->assertCount(2, $groups);
        // 验证按群组名称ASC排序
        $this->assertSame('Group A', $groups[0]->getGroupName());
        $this->assertSame('Group B', $groups[1]->getGroupName());
        $this->assertTrue($groups[0]->isValid());
        $this->assertTrue($groups[1]->isValid());
    }

    #[TestDox('通过名称搜索群组')]
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

        $group1 = new WeChatGroup();
        $group1->setAccount($account);
        $group1->setGroupId('group-1');
        $group1->setGroupName('Team Alpha');
        $group1->setInGroup(true);
        $group1->setValid(true);

        $group2 = new WeChatGroup();
        $group2->setAccount($account);
        $group2->setGroupId('group-2');
        $group2->setGroupName('Beta Team');
        $group2->setRemarkName('Alpha Project');
        $group2->setInGroup(true);
        $group2->setValid(true);

        $group3 = new WeChatGroup();
        $group3->setAccount($account);
        $group3->setGroupId('group-3');
        $group3->setGroupName('Gamma Team');
        $group3->setInGroup(true);
        $group3->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($group1);
        self::getEntityManager()->persist($group2);
        self::getEntityManager()->persist($group3);
        self::getEntityManager()->flush();

        $results = $this->getRepository()->searchByName($account, 'Alpha');

        $this->assertCount(2, $results);
        $groupNames = array_map(fn (WeChatGroup $group) => $group->getGroupName(), $results);
        $this->assertContains('Team Alpha', $groupNames); // 群组名称匹配
        $this->assertContains('Beta Team', $groupNames); // 备注名匹配
        $this->assertNotContains('Gamma Team', $groupNames); // 不匹配
    }

    #[TestDox('通过名称搜索群组时只返回有效群组')]
    public function testSearchByNameOnlyValidGroups(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $validGroup = new WeChatGroup();
        $validGroup->setAccount($account);
        $validGroup->setGroupId('valid-group');
        $validGroup->setGroupName('Valid Alpha Team');
        $validGroup->setInGroup(true);
        $validGroup->setValid(true);

        $invalidGroup = new WeChatGroup();
        $invalidGroup->setAccount($account);
        $invalidGroup->setGroupId('invalid-group');
        $invalidGroup->setGroupName('Invalid Alpha Team');
        $invalidGroup->setInGroup(true);
        $invalidGroup->setValid(false);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($validGroup);
        self::getEntityManager()->persist($invalidGroup);
        self::getEntityManager()->flush();

        $results = $this->getRepository()->searchByName($account, 'Alpha');

        $this->assertCount(1, $results);
        $this->assertArrayHasKey(0, $results);
        $this->assertSame('Valid Alpha Team', $results[0]->getGroupName());
    }

    #[TestDox('统计账号的活跃群组数量')]
    public function testCountActiveByAccount(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建活跃群组
        for ($i = 0; $i < 3; ++$i) {
            $activeGroup = new WeChatGroup();
            $activeGroup->setAccount($account);
            $activeGroup->setGroupId('active-group-' . $i);
            $activeGroup->setGroupName('Active Group ' . $i);
            $activeGroup->setInGroup(true);
            $activeGroup->setValid(true);
            self::getEntityManager()->persist($activeGroup);
        }

        // 创建非活跃群组
        for ($i = 0; $i < 2; ++$i) {
            $inactiveGroup = new WeChatGroup();
            $inactiveGroup->setAccount($account);
            $inactiveGroup->setGroupId('inactive-group-' . $i);
            $inactiveGroup->setGroupName('Inactive Group ' . $i);
            $inactiveGroup->setInGroup(false);
            $inactiveGroup->setValid(true);
            self::getEntityManager()->persist($inactiveGroup);
        }

        // 创建无效群组（不应该被统计）
        $invalidGroup = new WeChatGroup();
        $invalidGroup->setAccount($account);
        $invalidGroup->setGroupId('invalid-group');
        $invalidGroup->setGroupName('Invalid Group');
        $invalidGroup->setInGroup(true);
        $invalidGroup->setValid(false);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($invalidGroup);
        self::getEntityManager()->flush();

        $activeCount = $this->getRepository()->countActiveByAccount($account);

        $this->assertSame(3, $activeCount);
    }

    #[TestDox('不同账号的群组相互独立')]
    public function testGroupsAreAccountSpecific(): void
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

        $group1 = new WeChatGroup();
        $group1->setAccount($account1);
        $group1->setGroupId('group-1');
        $group1->setGroupName('Group 1');
        $group1->setInGroup(true);
        $group1->setValid(true);

        $group2 = new WeChatGroup();
        $group2->setAccount($account2);
        $group2->setGroupId('group-2');
        $group2->setGroupName('Group 2');
        $group2->setInGroup(true);
        $group2->setValid(true);

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->persist($group1);
        self::getEntityManager()->persist($group2);
        self::getEntityManager()->flush();

        $account1Groups = $this->getRepository()->findActiveGroupsByAccount($account1);
        $account2Groups = $this->getRepository()->findActiveGroupsByAccount($account2);

        $this->assertCount(1, $account1Groups);
        $this->assertCount(1, $account2Groups);
        $this->assertArrayHasKey(0, $account1Groups);
        $this->assertArrayHasKey(0, $account2Groups);
        $this->assertSame('Group 1', $account1Groups[0]->getGroupName());
        $this->assertSame('Group 2', $account2Groups[0]->getGroupName());
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

        $this->assertNull($this->getRepository()->findByAccountAndGroupId($account, 'any-group'));
        $this->assertEmpty($this->getRepository()->findActiveGroupsByAccount($account));
        $this->assertEmpty($this->getRepository()->findByAccount($account));
        $this->assertEmpty($this->getRepository()->searchByName($account, 'any-name'));
        $this->assertSame(0, $this->getRepository()->countActiveByAccount($account));
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

        $group = new WeChatGroup();
        $group->setAccount($account);
        $group->setGroupId('new-group');
        $group->setGroupName('New Group');
        $group->setInGroup(true);
        $group->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $this->getRepository()->save($group, true);

        $foundGroup = $this->getRepository()->findOneBy(['groupId' => 'new-group']);
        $this->assertInstanceOf(WeChatGroup::class, $foundGroup);
        $this->assertSame('new-group', $foundGroup->getGroupId());
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

        $group = new WeChatGroup();
        $group->setAccount($account);
        $group->setGroupId('to-delete');
        $group->setGroupName('To Delete');
        $group->setInGroup(true);
        $group->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($group);
        self::getEntityManager()->flush();

        $this->getRepository()->remove($group, true);

        $foundGroup = $this->getRepository()->findOneBy(['groupId' => 'to-delete']);
        $this->assertNull($foundGroup);
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

        $group1 = new WeChatGroup();
        $group1->setAccount($account1);
        $group1->setGroupId('group-1');
        $group1->setGroupName('Group 1');
        $group1->setInGroup(true);
        $group1->setValid(true);

        $group2 = new WeChatGroup();
        $group2->setAccount($account2);
        $group2->setGroupId('group-2');
        $group2->setGroupName('Group 2');
        $group2->setInGroup(true);
        $group2->setValid(true);

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->persist($group1);
        self::getEntityManager()->persist($group2);
        self::getEntityManager()->flush();

        $groupsForAccount1 = $this->getRepository()->findBy(['account' => $account1]);
        $groupsForAccount2 = $this->getRepository()->findBy(['account' => $account2]);

        $this->assertCount(1, $groupsForAccount1);
        $this->assertCount(1, $groupsForAccount2);
        $this->assertArrayHasKey(0, $groupsForAccount1);
        $this->assertArrayHasKey(0, $groupsForAccount2);
        $this->assertInstanceOf(WeChatGroup::class, $groupsForAccount1[0]);
        $this->assertInstanceOf(WeChatGroup::class, $groupsForAccount2[0]);
        $this->assertSame($account1, $groupsForAccount1[0]->getAccount());
        $this->assertSame($account2, $groupsForAccount2[0]->getAccount());
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
            $group = new WeChatGroup();
            $group->setAccount($account1);
            $group->setGroupId('group-' . $i);
            $group->setGroupName('Group ' . $i);
            $group->setInGroup(true);
            $group->setValid(true);
            self::getEntityManager()->persist($group);
        }

        for ($i = 1; $i <= 2; ++$i) {
            $group = new WeChatGroup();
            $group->setAccount($account2);
            $group->setGroupId('group-acc2-' . $i);
            $group->setGroupName('Group Acc2 ' . $i);
            $group->setInGroup(true);
            $group->setValid(true);
            self::getEntityManager()->persist($group);
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
        // 先获取当前数据库中 remarkName 为 null 的群组数量（来自 DataFixtures）
        $initialCountWithoutRemark = $this->getRepository()->count(['remarkName' => null]);

        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $groupWithRemark = new WeChatGroup();
        $groupWithRemark->setAccount($account);
        $groupWithRemark->setGroupId('group-with-remark');
        $groupWithRemark->setGroupName('Group With Remark');
        $groupWithRemark->setRemarkName('My Group');
        $groupWithRemark->setInGroup(true);
        $groupWithRemark->setValid(true);

        $groupWithoutRemark = new WeChatGroup();
        $groupWithoutRemark->setAccount($account);
        $groupWithoutRemark->setGroupId('group-without-remark');
        $groupWithoutRemark->setGroupName('Group Without Remark');
        $groupWithoutRemark->setRemarkName(null);
        $groupWithoutRemark->setInGroup(true);
        $groupWithoutRemark->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($groupWithRemark);
        self::getEntityManager()->persist($groupWithoutRemark);
        self::getEntityManager()->flush();

        $groupsWithoutRemark = $this->getRepository()->findBy(['remarkName' => null]);

        // 验证数量应该是初始数量 + 1（我们新增的那个）
        $this->assertCount($initialCountWithoutRemark + 1, $groupsWithoutRemark);

        // 验证我们创建的群组在结果中
        $foundTestGroup = false;
        foreach ($groupsWithoutRemark as $group) {
            $this->assertInstanceOf(WeChatGroup::class, $group);
            if ('group-without-remark' === $group->getGroupId()) {
                $foundTestGroup = true;
                $this->assertNull($group->getRemarkName());
                break;
            }
        }
        $this->assertTrue($foundTestGroup, '应该能找到我们创建的测试群组');
    }

    #[TestDox('统计可空字段为NULL的记录数量')]
    public function testCountByNullableFields(): void
    {
        // 先获取当前数据库中 remarkName 为 null 的群组数量（来自 DataFixtures）
        $initialCountWithoutRemark = $this->getRepository()->count(['remarkName' => null]);

        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        for ($i = 1; $i <= 2; ++$i) {
            $group = new WeChatGroup();
            $group->setAccount($account);
            $group->setGroupId('with-remark-' . $i);
            $group->setGroupName('With Remark ' . $i);
            $group->setRemarkName('Remark ' . $i);
            $group->setInGroup(true);
            $group->setValid(true);
            self::getEntityManager()->persist($group);
        }

        for ($i = 1; $i <= 3; ++$i) {
            $group = new WeChatGroup();
            $group->setAccount($account);
            $group->setGroupId('without-remark-' . $i);
            $group->setGroupName('Without Remark ' . $i);
            $group->setRemarkName(null);
            $group->setInGroup(true);
            $group->setValid(true);
            self::getEntityManager()->persist($group);
        }

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $countWithoutRemark = $this->getRepository()->count(['remarkName' => null]);

        // 验证数量应该是初始数量 + 3（我们新增的3个 remarkName 为 null 的群组）
        $this->assertSame($initialCountWithoutRemark + 3, $countWithoutRemark);
    }

    // ================== 完整的可空字段测试 ==================

    #[TestDox('查询所有可空字段为NULL的记录')]
    public function testFindByAllNullableFields(): void
    {
        // 获取所有字段的初始数据（来自 DataFixtures）
        $initialCountWithoutGroupName = $this->getRepository()->count(['groupName' => null]);
        $initialCountWithoutRemarkName = $this->getRepository()->count(['remarkName' => null]);
        $initialCountWithoutAvatar = $this->getRepository()->count(['avatar' => null]);
        $initialCountWithoutOwnerId = $this->getRepository()->count(['ownerId' => null]);
        $initialCountWithoutOwnerName = $this->getRepository()->count(['ownerName' => null]);
        $initialCountWithoutAnnouncement = $this->getRepository()->count(['announcement' => null]);
        $initialCountWithoutDescription = $this->getRepository()->count(['description' => null]);
        $initialCountWithoutQrCodeUrl = $this->getRepository()->count(['qrCodeUrl' => null]);
        $initialCountWithoutJoinTime = $this->getRepository()->count(['joinTime' => null]);
        $initialCountWithoutLastActiveTime = $this->getRepository()->count(['lastActiveTime' => null]);
        $initialCountWithoutRemark = $this->getRepository()->count(['remark' => null]);

        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建有群名称的群组
        $groupWithName = new WeChatGroup();
        $groupWithName->setAccount($account);
        $groupWithName->setGroupId('with-name');
        $groupWithName->setGroupName('Test Group Name');
        $groupWithName->setInGroup(true);
        $groupWithName->setValid(true);

        // 创建没有群名称的群组
        $groupWithoutName = new WeChatGroup();
        $groupWithoutName->setAccount($account);
        $groupWithoutName->setGroupId('without-name');
        $groupWithoutName->setGroupName(null);
        $groupWithoutName->setInGroup(true);
        $groupWithoutName->setValid(true);

        // 创建有备注名的群组
        $groupWithRemarkName = new WeChatGroup();
        $groupWithRemarkName->setAccount($account);
        $groupWithRemarkName->setGroupId('with-remark-name');
        $groupWithRemarkName->setRemarkName('Test Remark');
        $groupWithRemarkName->setInGroup(true);
        $groupWithRemarkName->setValid(true);

        // 创建有头像的群组
        $groupWithAvatar = new WeChatGroup();
        $groupWithAvatar->setAccount($account);
        $groupWithAvatar->setGroupId('with-avatar');
        $groupWithAvatar->setAvatar('https://example.com/group-avatar.jpg');
        $groupWithAvatar->setInGroup(true);
        $groupWithAvatar->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($groupWithName);
        self::getEntityManager()->persist($groupWithoutName);
        self::getEntityManager()->persist($groupWithRemarkName);
        self::getEntityManager()->persist($groupWithAvatar);
        self::getEntityManager()->flush();

        // 测试查询群名称为NULL的群组
        $groupsWithoutName = $this->getRepository()->findBy(['groupName' => null]);
        // without-name, with-remark-name, with-avatar (3个) + DataFixtures中的数据
        $this->assertCount($initialCountWithoutGroupName + 3, $groupsWithoutName);

        // 测试查询备注名为NULL的群组
        $groupsWithoutRemarkName = $this->getRepository()->findBy(['remarkName' => null]);
        // with-name, without-name, with-avatar (3个) + DataFixtures中的数据
        $this->assertCount($initialCountWithoutRemarkName + 3, $groupsWithoutRemarkName);

        // 测试查询头像为NULL的群组
        $groupsWithoutAvatar = $this->getRepository()->findBy(['avatar' => null]);
        // with-name, without-name, with-remark-name (3个) + DataFixtures中的数据
        $this->assertCount($initialCountWithoutAvatar + 3, $groupsWithoutAvatar);

        // 测试查询群主ID为NULL的群组
        $groupsWithoutOwnerId = $this->getRepository()->findBy(['ownerId' => null]);
        // 我们创建的4个测试群组都没有设置ownerId + DataFixtures中的数据
        $this->assertCount($initialCountWithoutOwnerId + 4, $groupsWithoutOwnerId);

        // 测试查询群主名称为NULL的群组
        $groupsWithoutOwnerName = $this->getRepository()->findBy(['ownerName' => null]);
        // 我们创建的4个测试群组都没有设置ownerName + DataFixtures中的数据
        $this->assertCount($initialCountWithoutOwnerName + 4, $groupsWithoutOwnerName);

        // 测试查询群公告为NULL的群组
        $groupsWithoutAnnouncement = $this->getRepository()->findBy(['announcement' => null]);
        // 我们创建的4个测试群组都没有设置announcement + DataFixtures中的数据
        $this->assertCount($initialCountWithoutAnnouncement + 4, $groupsWithoutAnnouncement);

        // 测试查询群描述为NULL的群组
        $groupsWithoutDescription = $this->getRepository()->findBy(['description' => null]);
        // 我们创建的4个测试群组都没有设置description + DataFixtures中的数据
        $this->assertCount($initialCountWithoutDescription + 4, $groupsWithoutDescription);

        // 测试查询群二维码URL为NULL的群组
        $groupsWithoutQrCodeUrl = $this->getRepository()->findBy(['qrCodeUrl' => null]);
        // 我们创建的4个测试群组都没有设置qrCodeUrl + DataFixtures中的数据
        $this->assertCount($initialCountWithoutQrCodeUrl + 4, $groupsWithoutQrCodeUrl);

        // 测试查询加入群时间为NULL的群组
        $groupsWithoutJoinTime = $this->getRepository()->findBy(['joinTime' => null]);
        // 我们创建的4个测试群组都没有设置joinTime + DataFixtures中的数据
        $this->assertCount($initialCountWithoutJoinTime + 4, $groupsWithoutJoinTime);

        // 测试查询最后活跃时间为NULL的群组
        $groupsWithoutLastActiveTime = $this->getRepository()->findBy(['lastActiveTime' => null]);
        // 我们创建的4个测试群组都没有设置lastActiveTime + DataFixtures中的数据
        $this->assertCount($initialCountWithoutLastActiveTime + 4, $groupsWithoutLastActiveTime);

        // 测试查询备注为NULL的群组
        $groupsWithoutRemark = $this->getRepository()->findBy(['remark' => null]);
        // 我们创建的4个测试群组都没有设置remark + DataFixtures中的数据
        $this->assertCount($initialCountWithoutRemark + 4, $groupsWithoutRemark);
    }

    #[TestDox('统计所有可空字段为NULL的记录数量')]
    public function testCountByAllNullableFields(): void
    {
        // 获取所有字段的初始数据（来自 DataFixtures）
        $initialCountWithoutGroupName = $this->getRepository()->count(['groupName' => null]);
        $initialCountWithoutRemarkName = $this->getRepository()->count(['remarkName' => null]);
        $initialCountWithoutAvatar = $this->getRepository()->count(['avatar' => null]);
        $initialCountWithoutOwnerId = $this->getRepository()->count(['ownerId' => null]);
        $initialCountWithoutOwnerName = $this->getRepository()->count(['ownerName' => null]);
        $initialCountWithoutAnnouncement = $this->getRepository()->count(['announcement' => null]);
        $initialCountWithoutDescription = $this->getRepository()->count(['description' => null]);
        $initialCountWithoutQrCodeUrl = $this->getRepository()->count(['qrCodeUrl' => null]);
        $initialCountWithoutJoinTime = $this->getRepository()->count(['joinTime' => null]);
        $initialCountWithoutLastActiveTime = $this->getRepository()->count(['lastActiveTime' => null]);
        $initialCountWithoutRemark = $this->getRepository()->count(['remark' => null]);

        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建具有各种可空字段值的群组
        $groupFull = new WeChatGroup();
        $groupFull->setAccount($account);
        $groupFull->setGroupId('full-group');
        $groupFull->setGroupName('Full Group');
        $groupFull->setRemarkName('Full Remark');
        $groupFull->setAvatar('https://example.com/group-avatar.jpg');
        $groupFull->setOwnerId('owner123');
        $groupFull->setOwnerName('Group Owner');
        $groupFull->setAnnouncement('Group announcement');
        $groupFull->setDescription('Group description');
        $groupFull->setQrCodeUrl('https://example.com/group-qr.jpg');
        $groupFull->setJoinTime(new \DateTimeImmutable());
        $groupFull->setLastActiveTime(new \DateTimeImmutable());
        $groupFull->setRemark('test-remark');
        $groupFull->setInGroup(true);
        $groupFull->setValid(true);

        // 创建空字段群组
        $groupEmpty = new WeChatGroup();
        $groupEmpty->setAccount($account);
        $groupEmpty->setGroupId('empty-group');
        $groupEmpty->setInGroup(true);
        $groupEmpty->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($groupFull);
        self::getEntityManager()->persist($groupEmpty);
        self::getEntityManager()->flush();

        // 统计各个可空字段为NULL的记录数量（初始数量 + 新增的空字段群组）
        $this->assertSame($initialCountWithoutGroupName + 1, $this->getRepository()->count(['groupName' => null]));
        $this->assertSame($initialCountWithoutRemarkName + 1, $this->getRepository()->count(['remarkName' => null]));
        $this->assertSame($initialCountWithoutAvatar + 1, $this->getRepository()->count(['avatar' => null]));
        $this->assertSame($initialCountWithoutOwnerId + 1, $this->getRepository()->count(['ownerId' => null]));
        $this->assertSame($initialCountWithoutOwnerName + 1, $this->getRepository()->count(['ownerName' => null]));
        $this->assertSame($initialCountWithoutAnnouncement + 1, $this->getRepository()->count(['announcement' => null]));
        $this->assertSame($initialCountWithoutDescription + 1, $this->getRepository()->count(['description' => null]));
        $this->assertSame($initialCountWithoutQrCodeUrl + 1, $this->getRepository()->count(['qrCodeUrl' => null]));
        $this->assertSame($initialCountWithoutJoinTime + 1, $this->getRepository()->count(['joinTime' => null]));
        $this->assertSame($initialCountWithoutLastActiveTime + 1, $this->getRepository()->count(['lastActiveTime' => null]));
        $this->assertSame($initialCountWithoutRemark + 1, $this->getRepository()->count(['remark' => null]));
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

        $group1 = new WeChatGroup();
        $group1->setAccount($account);
        $group1->setGroupId('group-1');
        $group1->setGroupName('Group B');
        $group1->setInGroup(true);
        $group1->setValid(true);

        $group2 = new WeChatGroup();
        $group2->setAccount($account);
        $group2->setGroupId('group-2');
        $group2->setGroupName('Group A');
        $group2->setInGroup(true);
        $group2->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($group1);
        self::getEntityManager()->persist($group2);
        self::getEntityManager()->flush();

        // 按群名称升序查询，应返回第一个匹配的记录（限制为我们创建的账号）
        $group = $this->getRepository()->findOneBy(
            ['inGroup' => true, 'account' => $account],
            ['groupName' => 'ASC']
        );

        $this->assertInstanceOf(WeChatGroup::class, $group);
        $this->assertSame('Group A', $group->getGroupName());

        // 按群名称降序查询，应返回第一个匹配的记录（限制为我们创建的账号）
        $groupDesc = $this->getRepository()->findOneBy(
            ['inGroup' => true, 'account' => $account],
            ['groupName' => 'DESC']
        );

        $this->assertInstanceOf(WeChatGroup::class, $groupDesc);
        $this->assertSame('Group B', $groupDesc->getGroupName());
    }
}
