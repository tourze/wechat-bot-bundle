<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Entity\WeChatGroup;
use Tourze\WechatBotBundle\Repository\WeChatGroupRepository;
use Tourze\WechatBotBundle\Service\WeChatGroupService;

/**
 * 微信群组服务测试
 */
class WeChatGroupServiceTest extends TestCase
{
    private WeChatGroupService $service;
    private EntityManagerInterface&MockObject $entityManager;
    private WeChatApiClient&MockObject $apiClient;
    private WeChatGroupRepository&MockObject $groupRepository;
    private LoggerInterface&MockObject $logger;

    /**
     * 测试创建微信群
     */
    public function testCreateGroup(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $memberWxIds = ['member1', 'member2', 'member3'];

        $mockResponse = [
            'group_wxid' => 'group123',
            'group_name' => '新建群聊'
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(WeChatGroup::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->logger
            ->expects($this->once())
            ->method('info');

        // 执行测试
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
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupId = 'group123';
        $memberWxid = 'newmember1';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success'
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $this->logger
            ->expects($this->once())
            ->method('info');

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
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupId = 'group123';
        $memberWxid = 'member1';

        $this->logger
            ->expects($this->once())
            ->method('warning');

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
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupId = 'group123';
        $newName = '新群名称';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success'
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        // 更新本地群信息
        $group = $this->createMock(WeChatGroup::class);

        $groupRepository = $this->createMock(WeChatGroupRepository::class);
        $groupRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn($group);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($groupRepository);

        $group
            ->expects($this->once())
            ->method('setGroupName')
            ->with($newName);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->logger
            ->expects($this->once())
            ->method('info');

        // 执行测试
        $result = $this->service->updateGroupName($account, $groupId, $newName);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试设置群公告
     */
    public function testSetGroupAnnouncement(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupId = 'group123';
        $announcement = '这是群公告内容';

        $this->logger
            ->expects($this->once())
            ->method('warning');

        // 执行测试
        $result = $this->service->setGroupAnnouncement($account, $groupId, $announcement);

        // 验证结果
        $this->assertFalse($result);
    }

    /**
     * 测试退出群聊
     */
    public function testLeaveGroup(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupId = 'group123';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success'
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        // 删除本地群组记录
        $group = $this->createMock(WeChatGroup::class);

        $groupRepository = $this->createMock(WeChatGroupRepository::class);
        $groupRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn($group);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($groupRepository);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($group);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->logger
            ->expects($this->once())
            ->method('info');

        // 执行测试
        $result = $this->service->leaveGroup($account, $groupId);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试获取群成员列表
     */
    public function testGetGroupMembers(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupId = 'group123';

        $mockResponse = [
            'members' => []
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

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
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupId = 'group123';

        $mockResponse = [
            'wxid' => $groupId,
            'group_name' => '测试群',
            'avatar' => 'group_avatar.jpg',
            'member_count' => 10,
            'owner_wxid' => 'owner123'
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

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
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $groupId = 'group123';
        $memberWxIds = ['member1', 'member2'];
        $content = '@成员 请查看重要消息';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success'
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $this->logger
            ->expects($this->once())
            ->method('info');

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
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockResponse = [
            'groups' => [
                [
                    'wxid' => 'group1',
                    'group_name' => '群组1',
                    'avatar' => 'avatar1.jpg'
                ],
                [
                    'wxid' => 'group2',
                    'group_name' => '群组2',
                    'avatar' => 'avatar2.jpg'
                ]
            ]
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        $groupRepository = $this->createMock(WeChatGroupRepository::class);
        $groupRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->willReturn(null);

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->willReturn($groupRepository);

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist')
            ->with($this->isInstanceOf(WeChatGroup::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->logger
            ->expects($this->once())
            ->method('info');

        // 执行测试
        $result = $this->service->syncGroups($account);

        // 验证结果
        $this->assertTrue($result);
    }

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->apiClient = $this->createMock(WeChatApiClient::class);
        $this->groupRepository = $this->createMock(WeChatGroupRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new WeChatGroupService(
            $this->entityManager,
            $this->apiClient,
            $this->logger
        );
    }
}
