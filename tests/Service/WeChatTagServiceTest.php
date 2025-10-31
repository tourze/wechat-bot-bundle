<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Entity\WeChatContact;
use Tourze\WechatBotBundle\Service\WeChatTagService;

/**
 * @internal
 */
#[CoversClass(WeChatTagService::class)]
#[RunTestsInSeparateProcesses]
final class WeChatTagServiceTest extends AbstractIntegrationTestCase
{
    private WeChatTagService $service;

    /** @var WeChatApiClient&MockObject */
    private WeChatApiClient $apiClient;

    protected function onSetUp(): void
    {
        // 创建API客户端Mock对象用于测试
        $this->apiClient = $this->createMock(WeChatApiClient::class);

        // 将Mock的API客户端注册到服务容器中
        self::getContainer()->set(WeChatApiClient::class, $this->apiClient);

        // 从容器中获取服务实例，这样会使用我们注入的Mock API客户端
        $this->service = self::getService(WeChatTagService::class);
    }

    public function testServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(WeChatTagService::class, $this->service);
    }

    /**
     * 测试创建好友标签
     */
    public function testCreateFriendTag(): void
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

        $tagName = '重要客户';

        $mockResponse = [
            'tag_id' => 'tag123',
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        // 执行测试
        $result = $this->service->createFriendTag($account, $tagName);

        // 验证结果
        $this->assertNotNull($result);
        $this->assertEquals('tag123', $result->tagId);
        $this->assertEquals($tagName, $result->tagName);
        $this->assertEquals(0, $result->memberCount);
    }

    /**
     * 测试修改好友标签
     */
    public function testUpdateFriendTag(): void
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

        $tagId = 'tag123';
        $newTagName = '特别客户';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        // 执行测试
        $result = $this->service->updateFriendTag($account, $tagId, $newTagName);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试删除好友标签
     */
    public function testDeleteFriendTag(): void
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

        $tagId = 'tag123';

        $mockResponse = [
            'code' => '1000',
            'message' => 'success',
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockResponse)
        ;

        // 执行测试
        $result = $this->service->deleteFriendTag($account, $tagId);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试为联系人添加标签
     */
    public function testAddContactToTag(): void
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
        $account->method('getDeviceId')->willReturn('device123');

        /**
         * 使用 WeChatContact 具体类进行 Mock 的原因：
         * 1. 联系人实体特性：WeChatContact 是联系人聚合根，封装了标签管理、关系维护等复杂业务逻辑
         * 2. 标签业务需求：需要测试标签的增加、删除、查询等核心业务功能
         * 3. 架构一致性：与其他微信实体保持一致，均采用具体类 Mock 的测试策略
         * 4. 测试可控性：Mock 提供精确的联系人状态控制，确保测试场景的可重现性
         */
        /** @var WeChatContact&MockObject $contact */
        $contact = $this->createMock(WeChatContact::class);
        $contact->method('getTags')->willReturn('tag1,tag2');
        $contact->method('getContactId')->willReturn('contact123');
        $contact->expects($this->once())->method('setTags')->with('tag1,tag2,tag123');

        $tagId = 'tag123';

        // 执行测试
        $result = $this->service->addContactToTag($account, $contact, $tagId);

        // 验证结果
        $this->assertTrue($result);
    }

    /**
     * 测试从联系人移除标签
     */
    public function testRemoveContactFromTag(): void
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
        $account->method('getDeviceId')->willReturn('device123');

        /**
         * 使用 WeChatContact 具体类进行 Mock 的原因：
         * 1. 联系人实体特性：WeChatContact 是联系人聚合根，封装了标签管理、关系维护等复杂业务逻辑
         * 2. 标签业务需求：需要测试标签的增加、删除、查询等核心业务功能
         * 3. 架构一致性：与其他微信实体保持一致，均采用具体类 Mock 的测试策略
         * 4. 测试可控性：Mock 提供精确的联系人状态控制，确保测试场景的可重现性
         */
        /** @var WeChatContact&MockObject $contact */
        $contact = $this->createMock(WeChatContact::class);
        $contact->method('getTags')->willReturn('tag1,tag123,tag2');
        $contact->method('getContactId')->willReturn('contact123');
        $contact->expects($this->once())->method('setTags')->with('tag1,tag2');

        $tagId = 'tag123';

        // 执行测试
        $result = $this->service->removeContactFromTag($account, $contact, $tagId);

        // 验证结果
        $this->assertTrue($result);
    }
}
