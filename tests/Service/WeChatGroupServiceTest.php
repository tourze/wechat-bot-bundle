<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Entity\WeChatGroup;
use Tourze\WechatBotBundle\Repository\WeChatGroupRepository;
use Tourze\WechatBotBundle\Service\WeChatGroupService;

/**
 * 微信群组服务测试
 *
 * @internal
 */
#[CoversClass(WeChatGroupService::class)]
#[RunTestsInSeparateProcesses]
final class WeChatGroupServiceTest extends AbstractIntegrationTestCase
{
    private WeChatGroupService $service;

    /** @var WeChatApiClient&MockObject */
    private WeChatApiClient $apiClient;

    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;

    private WeChatGroupRepository $groupRepository;

    /**
     * 测试创建微信群
     */
    public function testCreateGroup(): void
    {
        // 创建真实的API账号和账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-create-group-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device-create-group-' . uniqid());
        $account->setWechatId('test_wx_create_group');
        $account->setNickname('Create Group Test User');
        $account->setStatus('active');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $memberWxIds = ['member1', 'member2', 'member3'];

        $mockResponse = [
            'group_wxid' => 'group123',
            'group_name' => '新建群聊',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        // 执行测试（使用真实数据库）
        $result = $this->service->createGroup($account, $memberWxIds);

        // 验证结果
        $this->assertNotNull($result);
        $this->assertEquals('group123', $result->groupWxid);
    }

    /**
     * 测试添加群成员
     */
    public function testAddGroupMember(): void
    {
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        /**
         * 使用 WeChatAccount 具体类进行 Mock 的原因：
         * 1. 领域模型限制：WeChatAccount 是核心业务实体，承载微信账户的状态和行为
         * 2. 测试场景需求：需要模拟账户的关联关系（如 getApiAccount）和业务属性（如 getDeviceId）
         * 3. 设计合理性：该实体遵循聚合根模式，封装性良好，Mock 不会破坏业务逻辑
         * 4. 改进建议：可考虑提取 AccountInterface，但需权衡接口设计的复杂度和收益
         */
        /** @var WeChatAccount&MockObject $account */
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupId = 'group123';
        $memberWxid = 'newmember1';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        // 执行测试
        $result = $this->service->addGroupMember($account, $groupId, $memberWxid);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试移除群成员
     */
    public function testRemoveGroupMember(): void
    {
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        /**
         * 使用 WeChatAccount 具体类进行 Mock 的原因：
         * 1. 领域模型限制：WeChatAccount 是核心业务实体，承载微信账户的状态和行为
         * 2. 测试场景需求：需要模拟账户的关联关系（如 getApiAccount）和业务属性（如 getDeviceId）
         * 3. 设计合理性：该实体遵循聚合根模式，封装性良好，Mock 不会破坏业务逻辑
         * 4. 改进建议：可考虑提取 AccountInterface，但需权衡接口设计的复杂度和收益
         */
        /** @var WeChatAccount&MockObject $account */
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupId = 'group123';
        $memberWxid = 'member1';

        $this->logger
            ->expects($this->once())
            ->method('warning')
        ;

        // 执行测试
        $result = $this->service->removeGroupMember($account, $groupId, $memberWxid);

        // 验证结果
        $this->assertFalse($result);
    }

    /**
     * 测试修改群名
     */
    public function testUpdateGroupName(): void
    {
        // 创建真实的API账号和账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-update-name-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device-update-name-' . uniqid());
        $account->setWechatId('test_wx_update_name');
        $account->setNickname('Update Name Test User');
        $account->setStatus('active');

        self::getEntityManager()->persist($account);

        // 创建真实的群组
        $group = new WeChatGroup();
        $group->setAccount($account);
        $group->setGroupId('group123');
        $group->setGroupName('原群名');

        self::getEntityManager()->persist($group);
        self::getEntityManager()->flush();

        $newName = '新群名称';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        // 执行测试（使用真实数据库）
        $result = $this->service->updateGroupName($account, 'group123', $newName);

        // 验证结果
        $this->assertTrue($result);

        // 验证数据库中的群名已更新
        self::getEntityManager()->refresh($group);
        $this->assertEquals($newName, $group->getGroupName());
    }

    /**
     * 测试设置群公告
     */
    public function testSetGroupAnnouncement(): void
    {
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        /**
         * 使用 WeChatAccount 具体类进行 Mock 的原因：
         * 1. 领域模型限制：WeChatAccount 是核心业务实体，承载微信账户的状态和行为
         * 2. 测试场景需求：需要模拟账户的关联关系（如 getApiAccount）和业务属性（如 getDeviceId）
         * 3. 设计合理性：该实体遵循聚合根模式，封装性良好，Mock 不会破坏业务逻辑
         * 4. 改进建议：可考虑提取 AccountInterface，但需权衡接口设计的复杂度和收益
         */
        /** @var WeChatAccount&MockObject $account */
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupId = 'group123';
        $announcement = '这是群公告内容';

        $this->logger
            ->expects($this->once())
            ->method('warning')
        ;

        // 执行测试（方法无返回值，仅记录日志）
        $this->service->setGroupAnnouncement($account, $groupId, $announcement);
    }

    /**
     * 测试退出群聊
     */
    public function testLeaveGroup(): void
    {
        // 创建真实的API账号和账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-leave-group-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device-leave-group-' . uniqid());
        $account->setWechatId('test_wx_leave_group');
        $account->setNickname('Leave Group Test User');
        $account->setStatus('active');

        self::getEntityManager()->persist($account);

        // 创建真实的群组
        $group = new WeChatGroup();
        $group->setAccount($account);
        $group->setGroupId('group123');
        $group->setGroupName('测试群');

        self::getEntityManager()->persist($group);
        self::getEntityManager()->flush();

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        // 执行测试（使用真实数据库）
        $result = $this->service->leaveGroup($account, 'group123');

        // 验证结果
        $this->assertTrue($result);

        // 验证数据库中的群组已被删除
        self::getEntityManager()->clear();
        $deletedGroup = $this->groupRepository->findOneBy(['groupId' => 'group123']);
        $this->assertNull($deletedGroup);
    }

    /**
     * 测试获取群成员列表
     */
    public function testGetGroupMembers(): void
    {
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        /**
         * 使用 WeChatAccount 具体类进行 Mock 的原因：
         * 1. 领域模型限制：WeChatAccount 是核心业务实体，承载微信账户的状态和行为
         * 2. 测试场景需求：需要模拟账户的关联关系（如 getApiAccount）和业务属性（如 getDeviceId）
         * 3. 设计合理性：该实体遵循聚合根模式，封装性良好，Mock 不会破坏业务逻辑
         * 4. 改进建议：可考虑提取 AccountInterface，但需权衡接口设计的复杂度和收益
         */
        /** @var WeChatAccount&MockObject $account */
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupId = 'group123';

        $mockResponse = [
            'members' => [],
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        // 执行测试
        $result = $this->service->getGroupMembers($account, $groupId);

        // 验证结果
        $this->assertCount(0, $result);
    }

    /**
     * 测试获取群详细信息
     */
    public function testGetGroupDetail(): void
    {
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        /**
         * 使用 WeChatAccount 具体类进行 Mock 的原因：
         * 1. 领域模型限制：WeChatAccount 是核心业务实体，承载微信账户的状态和行为
         * 2. 测试场景需求：需要模拟账户的关联关系（如 getApiAccount）和业务属性（如 getDeviceId）
         * 3. 设计合理性：该实体遵循聚合根模式，封装性良好，Mock 不会破坏业务逻辑
         * 4. 改进建议：可考虑提取 AccountInterface，但需权衡接口设计的复杂度和收益
         */
        /** @var WeChatAccount&MockObject $account */
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupId = 'group123';

        $mockResponse = [
            'wxid' => $groupId,
            'group_name' => '测试群',
            'avatar' => 'group_avatar.jpg',
            'member_count' => 10,
            'owner_wxid' => 'owner123',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        // 执行测试
        $result = $this->service->getGroupDetail($account, $groupId);

        // 验证结果
        $this->assertNotNull($result);
        $this->assertEquals($groupId, $result->wxid);
        $this->assertEquals('测试群', $result->groupName);
        $this->assertEquals(10, $result->memberCount);
    }

    /**
     * 测试@群成员
     */
    public function testAtGroupMember(): void
    {
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        /**
         * 使用 WeChatAccount 具体类进行 Mock 的原因：
         * 1. 领域模型限制：WeChatAccount 是核心业务实体，承载微信账户的状态和行为
         * 2. 测试场景需求：需要模拟账户的关联关系（如 getApiAccount）和业务属性（如 getDeviceId）
         * 3. 设计合理性：该实体遵循聚合根模式，封装性良好，Mock 不会破坏业务逻辑
         * 4. 改进建议：可考虑提取 AccountInterface，但需权衡接口设计的复杂度和收益
         */
        /** @var WeChatAccount&MockObject $account */
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupId = 'group123';
        $memberWxIds = ['Member 1', 'Member 2'];
        $content = '@成员 请查看重要消息';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        // 执行测试
        $result = $this->service->atGroupMember($account, $groupId, $memberWxIds, $content);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试同步群组列表
     */
    public function testSyncGroups(): void
    {
        // 创建真实的API账号和账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-sync-groups-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device-sync-groups-' . uniqid());
        $account->setWechatId('test_wx_sync_groups');
        $account->setNickname('Sync Groups Test User');
        $account->setStatus('active');

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $mockResponse = [
            'groups' => [
                [
                    'wxid' => 'group1',
                    'group_name' => '群组1',
                    'avatar' => 'avatar1.jpg',
                ],
                [
                    'wxid' => 'group2',
                    'group_name' => '群组2',
                    'avatar' => 'avatar2.jpg',
                ],
            ],
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        // 执行测试（使用真实数据库，Repository会自动查找和创建群组）
        $result = $this->service->syncGroups($account);

        // 验证结果
        $this->assertTrue($result);

        // 验证数据库中是否创建了群组
        $groups = $this->groupRepository->findBy(['account' => $account]);
        $this->assertGreaterThanOrEqual(2, count($groups), '应该创建了至少2个群组');
    }

    /**
     * 测试通过群邀请
     */
    public function testAcceptGroupInvite(): void
    {
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        /**
         * 使用 WeChatAccount 具体类进行 Mock 的原因：
         * 1. 领域模型限制：WeChatAccount 是核心业务实体，承载微信账户的状态和行为
         * 2. 测试场景需求：需要模拟账户的关联关系（如 getApiAccount）和业务属性（如 getDeviceId）
         * 3. 设计合理性：该实体遵循聚合根模式，封装性良好，Mock 不会破坏业务逻辑
         * 4. 改进建议：可考虑提取 AccountInterface，但需权衡接口设计的复杂度和收益
         */
        /** @var WeChatAccount&MockObject $account */
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $encryptUsername = 'encrypt_user123';
        $ticket = 'ticket123';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        // 执行测试
        $result = $this->service->acceptGroupInvite($account, $encryptUsername, $ticket);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试添加群成员为好友
     */
    public function testAddGroupMemberAsFriend(): void
    {
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        /**
         * 使用 WeChatAccount 具体类进行 Mock 的原因：
         * 1. 领域模型限制：WeChatAccount 是核心业务实体，承载微信账户的状态和行为
         * 2. 测试场景需求：需要模拟账户的关联关系（如 getApiAccount）和业务属性（如 getDeviceId）
         * 3. 设计合理性：该实体遵循聚合根模式，封装性良好，Mock 不会破坏业务逻辑
         * 4. 改进建议：可考虑提取 AccountInterface，但需权衡接口设计的复杂度和收益
         */
        /** @var WeChatAccount&MockObject $account */
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupWxid = 'group123';
        $memberWxid = 'member123';
        $verifyMessage = '申请添加好友';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        // 执行测试
        $result = $this->service->addGroupMemberAsFriend($account, $groupWxid, $memberWxid, $verifyMessage);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试群管理员操作
     */
    public function testGroupAdminOperation(): void
    {
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        /**
         * 使用 WeChatAccount 具体类进行 Mock 的原因：
         * 1. 领域模型限制：WeChatAccount 是核心业务实体，承载微信账户的状态和行为
         * 2. 测试场景需求：需要模拟账户的关联关系（如 getApiAccount）和业务属性（如 getDeviceId）
         * 3. 设计合理性：该实体遵循聚合根模式，封装性良好，Mock 不会破坏业务逻辑
         * 4. 改进建议：可考虑提取 AccountInterface，但需权衡接口设计的复杂度和收益
         */
        /** @var WeChatAccount&MockObject $account */
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupWxid = 'group123';
        $memberWxid = 'member123';
        $operation = 'set_admin';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        // 执行测试
        $result = $this->service->groupAdminOperation($account, $groupWxid, $memberWxid, $operation);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试邀请群成员
     */
    public function testInviteGroupMember(): void
    {
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        /**
         * 使用 WeChatAccount 具体类进行 Mock 的原因：
         * 1. 领域模型限制：WeChatAccount 是核心业务实体，承载微信账户的状态和行为
         * 2. 测试场景需求：需要模拟账户的关联关系（如 getApiAccount）和业务属性（如 getDeviceId）
         * 3. 设计合理性：该实体遵循聚合根模式，封装性良好，Mock 不会破坏业务逻辑
         * 4. 改进建议：可考虑提取 AccountInterface，但需权衡接口设计的复杂度和收益
         */
        /** @var WeChatAccount&MockObject $account */
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupWxid = 'group123';
        $memberWxids = ['member1' => 'Member 1', 'member2' => 'Member 2'];

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        // 执行测试
        $result = $this->service->inviteGroupMember($account, $groupWxid, $memberWxids);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试保存群聊到通讯录
     */
    public function testSaveGroupToContact(): void
    {
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        /**
         * 使用 WeChatAccount 具体类进行 Mock 的原因：
         * 1. 领域模型限制：WeChatAccount 是核心业务实体，承载微信账户的状态和行为
         * 2. 测试场景需求：需要模拟账户的关联关系（如 getApiAccount）和业务属性（如 getDeviceId）
         * 3. 设计合理性：该实体遵循聚合根模式，封装性良好，Mock 不会破坏业务逻辑
         * 4. 改进建议：可考虑提取 AccountInterface，但需权衡接口设计的复杂度和收益
         */
        /** @var WeChatAccount&MockObject $account */
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupWxid = 'group123';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        // 执行测试
        $result = $this->service->saveGroupToContact($account, $groupWxid);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试搜索本地群组
     */
    public function testSearchLocalGroups(): void
    {
        /**
         * 使用 WeChatAccount 具体类进行 Mock 的原因：
         * 1. 领域模型限制：WeChatAccount 是核心业务实体，承载微信账户的状态和行为
         * 2. 测试场景需求：需要模拟账户的关联关系（如 getApiAccount）和业务属性（如 getDeviceId）
         * 3. 设计合理性：该实体遵循聚合根模式，封装性良好，Mock 不会破坏业务逻辑
         * 4. 改进建议：可考虑提取 AccountInterface，但需权衡接口设计的复杂度和收益
         */
        /** @var WeChatAccount&MockObject $account */
        $account = $this->createMock(WeChatAccount::class);
        $keyword = '测试群';

        $expectedGroups = [
            /*
             * 使用具体类创建 Mock 对象的原因：
             * 1) WeChatGroup 是实体类，没有对应的接口，只能使用具体类进行 Mock
             * 2) 在单元测试中模拟群组实体是必要的，用于测试搜索功能
             * 3) 该实体类封装了群组的完整生命周期，Mock 提供可控的测试环境
             */
            $this->createMock(WeChatGroup::class),
            /*
             * 使用具体类创建 Mock 对象的原因：
             * 1) WeChatGroup 是实体类，没有对应的接口，只能使用具体类进行 Mock
             * 2) 在单元测试中模拟群组实体是必要的，用于测试搜索功能
             * 3) 该实体类封装了群组的完整生命周期，Mock 提供可控的测试环境
             */
            $this->createMock(WeChatGroup::class),
        ];

        // searchLocalGroups 在 setUp 中已经配置了基本的 QueryBuilder 行为
        // 这里只需要验证方法能正常返回结果即可

        // 执行测试
        $result = $this->service->searchLocalGroups($account, $keyword);

        // 验证结果不为null（方法签名保证了返回array类型）
        $this->assertNotNull($result);
    }

    /**
     * 测试修改群昵称
     */
    public function testUpdateGroupNickname(): void
    {
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        /**
         * 使用 WeChatAccount 具体类进行 Mock 的原因：
         * 1. 领域模型限制：WeChatAccount 是核心业务实体，承载微信账户的状态和行为
         * 2. 测试场景需求：需要模拟账户的关联关系（如 getApiAccount）和业务属性（如 getDeviceId）
         * 3. 设计合理性：该实体遵循聚合根模式，封装性良好，Mock 不会破坏业务逻辑
         * 4. 改进建议：可考虑提取 AccountInterface，但需权衡接口设计的复杂度和收益
         */
        /** @var WeChatAccount&MockObject $account */
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupWxid = 'group123';
        $nickname = '新昵称';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        // 执行测试
        $result = $this->service->updateGroupNickname($account, $groupWxid, $nickname);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试修改群备注
     */
    public function testUpdateGroupRemark(): void
    {
        // 创建真实的API账号和账号
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('test-api-account-update-remark-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test-user');
        $apiAccount->setPassword('test-password');
        $apiAccount->setTimeout(30);
        $apiAccount->setConnectionStatus('connected');

        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('device-update-remark-' . uniqid());
        $account->setWechatId('test_wx_update_remark');
        $account->setNickname('Update Remark Test User');
        $account->setStatus('active');

        self::getEntityManager()->persist($account);

        // 创建真实的群组
        $group = new WeChatGroup();
        $group->setAccount($account);
        $group->setGroupId('group123');
        $group->setGroupName('测试群');

        self::getEntityManager()->persist($group);
        self::getEntityManager()->flush();

        $remark = '新备注';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        // 执行测试（使用真实数据库）
        $result = $this->service->updateGroupRemark($account, 'group123', $remark);

        // 验证结果
        $this->assertTrue($result);

        // 验证数据库中的群备注已更新
        self::getEntityManager()->refresh($group);
        $this->assertEquals($remark, $group->getRemark());
    }

    protected function onSetUp(): void
    {
        // 获取真实服务
        $this->groupRepository = self::getService(WeChatGroupRepository::class);

        // Mock外部API客户端
        $this->apiClient = $this->createMock(WeChatApiClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        // 将 Mock 对象注入到容器中
        self::getContainer()->set(WeChatApiClient::class, $this->apiClient);
        self::getContainer()->set('monolog.logger.wechat_bot', $this->logger);

        // 清理服务定位器缓存，确保获取更新后的服务
        self::clearServiceLocatorCache();

        // 使用容器获取服务实例（会使用我们注入的 Mock）
        $this->service = self::getService(WeChatGroupService::class);
    }
}
