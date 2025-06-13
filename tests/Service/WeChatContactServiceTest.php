<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\DTO\ContactInfoResult;
use Tourze\WechatBotBundle\DTO\ContactSearchResult;
use Tourze\WechatBotBundle\DTO\WeChatContactSearchResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Repository\WeChatContactRepository;
use Tourze\WechatBotBundle\Service\WeChatContactService;

/**
 * 微信联系人服务测试
 */
class WeChatContactServiceTest extends TestCase
{
    private WeChatContactService $service;
    private EntityManagerInterface&MockObject $entityManager;
    private WeChatApiClient&MockObject $apiClient;
    private WeChatContactRepository&MockObject $contactRepository;
    private LoggerInterface&MockObject $logger;

    /**
     * 测试搜索联系人成功
     */
    public function testSearchContactSuccess(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $searchKeyword = 'test@example.com';

        $mockResponse = [
            'wxid' => 'contact123',
            'nickname' => '测试用户',
            'avatar' => 'https://wx.qlogo.cn/avatar.jpg',
            'sex' => 1,
            'signature' => '个人签名',
            'phone' => '13800138000',
            'city' => '深圳',
            'province' => '广东',
            'country' => '中国'
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        // 执行测试
        $result = $this->service->searchContact($account, $searchKeyword);

        // 验证结果
        $this->assertInstanceOf(ContactSearchResult::class, $result);
        $this->assertEquals('contact123', $result->wxid);
        $this->assertEquals('测试用户', $result->nickname);
        $this->assertEquals('male', $result->sex);
    }

    /**
     * 测试搜索联系人失败
     */
    public function testSearchContactNotFound(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new \Exception('User not found'));

        $this->logger
            ->expects($this->once())
            ->method('error');

        // 执行测试
        $result = $this->service->searchContact($account, 'notfound@example.com');

        // 验证结果
        $this->assertNull($result);
    }

    /**
     * 测试添加好友
     */
    public function testAddFriend(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $wxId = 'friend123';
        $v1 = 'v1_value';
        $v2 = 'v2_value';
        $message = '你好，我想加你为好友';

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
        $result = $this->service->addFriend($account, $wxId, $v1, $v2, $message);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试删除好友
     */
    public function testDeleteFriend(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $wxId = 'friend123';

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
        $result = $this->service->deleteFriend($account, $wxId);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试修改好友备注
     */
    public function testUpdateFriendRemark(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $wxId = 'friend123';
        $remark = '新备注名';

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
        $result = $this->service->updateFriendRemark($account, $wxId, $remark);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试获取联系人详情
     */
    public function testGetContactInfo(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $wxId = 'contact123';

        $mockResponse = [
            'wxid' => $wxId,
            'nickname' => '联系人昵称',
            'avatar' => 'https://wx.qlogo.cn/avatar.jpg',
            'sex' => 1,
            'province' => '广东',
            'city' => '深圳'
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        // 执行测试
        $result = $this->service->getContactInfo($account, $wxId);

        // 验证结果
        $this->assertInstanceOf(ContactInfoResult::class, $result);
        $this->assertEquals($wxId, $result->wxid);
        $this->assertEquals('联系人昵称', $result->nickname);
    }

    /**
     * 测试同意好友添加
     */
    public function testAcceptFriendRequest(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $encryptUsername = 'encrypt_username';
        $ticket = 'ticket_value';
        $type = 3;

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
        $result = $this->service->acceptFriend($account, $encryptUsername, $ticket, $type);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试同步联系人列表
     */
    public function testSyncContacts(): void
    {
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account = $this->createMock(WeChatAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockResponse = [
            'friends' => [
                [
                    'wxid' => 'contact1',
                    'nickname' => '联系人1',
                    'avatar' => 'avatar1.jpg',
                    'sex' => 1
                ],
                [
                    'wxid' => 'contact2',
                    'nickname' => '联系人2',
                    'avatar' => 'avatar2.jpg',
                    'sex' => 2
                ]
            ]
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse);

        // 模拟Repository查找不存在的联系人
        $contactRepository = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $contactRepository->method('findOneBy')->willReturn(null);

        $this->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($contactRepository);

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->logger
            ->expects($this->once())
            ->method('info');

        // 执行测试
        $result = $this->service->syncContacts($account);

        // 验证结果
        $this->assertTrue($result);
    }

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->apiClient = $this->createMock(WeChatApiClient::class);
        $this->contactRepository = $this->createMock(WeChatContactRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new WeChatContactService(
            $this->entityManager,
            $this->apiClient,
            $this->logger
        );
    }
}
