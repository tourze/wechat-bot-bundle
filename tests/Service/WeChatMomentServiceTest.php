<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\DTO\MomentInfo;
use Tourze\WechatBotBundle\DTO\MomentsResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Service\WeChatMomentService;

/**
 * @internal
 */
#[CoversClass(WeChatMomentService::class)]
#[RunTestsInSeparateProcesses]
final class WeChatMomentServiceTest extends AbstractIntegrationTestCase
{
    private WeChatMomentService $service;

    /** @var WeChatApiClient&MockObject */
    private WeChatApiClient $apiClient;

    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;

    protected function onSetUp(): void
    {
        // Mock外部API客户端
        $this->apiClient = $this->createMock(WeChatApiClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        // 使用容器获取服务实例
        $this->service = self::getService(WeChatMomentService::class);
    }

    public function testServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(WeChatMomentService::class, $this->service);
    }

    public function testGetMoments(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockData = [
            'moments' => [
                [
                    'moment_id' => 'moment123',
                    'wxid' => 'user123',
                    'nickname' => '测试用户',
                    'content' => '测试内容',
                    'type' => 1,
                    'create_time' => time(),
                    'images' => [],
                    'video_url' => '',
                    'link_title' => '',
                    'link_desc' => '',
                    'link_url' => '',
                    'like_count' => 0,
                    'comment_count' => 0,
                    'likes' => [],
                    'comments' => [],
                ],
            ],
            'next_max_id' => 'next123',
            'has_more' => false,
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockData)
        ;

        $result = $this->service->getMoments($account);

        $this->assertInstanceOf(MomentsResult::class, $result);
        $this->assertCount(1, $result->moments);
        $this->assertEquals('next123', $result->nextMaxId);
        $this->assertFalse($result->hasMore);
    }

    public function testGetFriendMoments(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockData = [
            'moments' => [],
            'next_max_id' => '',
            'has_more' => false,
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockData)
        ;

        $result = $this->service->getFriendMoments($account, 'friend123');

        $this->assertInstanceOf(MomentsResult::class, $result);
        $this->assertCount(0, $result->moments);
    }

    public function testGetMomentDetail(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockData = [
            'moment_id' => 'moment123',
            'wxid' => 'user123',
            'nickname' => '测试用户',
            'content' => '详细内容',
            'type' => 1,
            'create_time' => time(),
            'images' => [],
            'video_url' => '',
            'link_title' => '',
            'link_desc' => '',
            'link_url' => '',
            'like_count' => 5,
            'comment_count' => 2,
            'likes' => [],
            'comments' => [],
        ];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockData)
        ;

        $result = $this->service->getMomentDetail($account, 'moment123');

        $this->assertInstanceOf(MomentInfo::class, $result);
        $this->assertEquals('moment123', $result->momentId);
        $this->assertEquals('详细内容', $result->content);
        $this->assertEquals(5, $result->likeCount);
    }

    public function testLikeMoment(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn([])
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        $result = $this->service->likeMoment($account, 'moment123');

        $this->assertTrue($result);
    }

    public function testCommentMoment(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn([])
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        $result = $this->service->commentMoment($account, 'moment123', '很棒的内容');

        $this->assertTrue($result);
    }

    public function testPublishTextMoment(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockData = ['moment_id' => 'new_moment123'];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockData)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        $result = $this->service->publishTextMoment($account, '今天天气真好');

        $this->assertEquals('new_moment123', $result);
    }

    public function testPublishLinkMoment(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockData = ['moment_id' => 'link_moment123'];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockData)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        $result = $this->service->publishLinkMoment(
            $account,
            'https://example.com',
            '测试链接',
            '这是一个测试链接',
            '分享链接'
        );

        $this->assertEquals('link_moment123', $result);
    }

    public function testPublishVideoMoment(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockData = ['moment_id' => 'video_moment123'];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockData)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        $result = $this->service->publishVideoMoment($account, '/path/to/video.mp4', '分享视频');

        $this->assertEquals('video_moment123', $result);
    }

    public function testDownloadMomentVideo(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockData = ['file_path' => '/downloaded/video.mp4'];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockData)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        $result = $this->service->downloadMomentVideo($account, 'https://video.url/test.mp4');

        $this->assertEquals('/downloaded/video.mp4', $result);
    }

    public function testForwardMoment(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockData = ['moment_id' => 'forwarded_moment123'];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockData)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        $result = $this->service->forwardMoment($account, 'original_moment123', '转发一下');

        $this->assertEquals('forwarded_moment123', $result);
    }

    public function testDeleteMoment(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn([])
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        $result = $this->service->deleteMoment($account, 'moment123');

        $this->assertTrue($result);
    }

    public function testHideMoment(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn([])
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        $result = $this->service->hideMoment($account, 'moment123');

        $this->assertTrue($result);
    }

    public function testShowMoment(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn([])
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        $result = $this->service->showMoment($account, 'moment123');

        $this->assertTrue($result);
    }

    public function testPublishImageMomentFromFiles(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockUploadData = ['image_id' => 'image123'];
        $mockPublishData = ['moment_id' => 'image_moment123'];

        $this->apiClient
            ->expects($this->exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($mockUploadData, $mockPublishData)
        ;

        $result = $this->service->publishImageMomentFromFiles($account, ['/path/to/image.jpg'], '分享图片');

        $this->assertEquals('image_moment123', $result);
    }

    public function testUploadMomentImageFile(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockData = ['image_id' => 'uploaded_image123'];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockData)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        $result = $this->service->uploadMomentImageFile($account, '/path/to/image.jpg');

        $this->assertEquals('uploaded_image123', $result);
    }

    public function testPublishImageMoment(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockData = ['moment_id' => 'image_moment123'];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockData)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        $result = $this->service->publishImageMoment($account, ['Image 1', 'Image 2'], '多图分享');

        $this->assertEquals('image_moment123', $result);
    }

    public function testPublishImageMomentFromBase64(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockUploadData = ['image_id' => 'b64_image123'];
        $mockPublishData = ['moment_id' => 'b64_moment123'];

        $this->apiClient
            ->expects($this->exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($mockUploadData, $mockPublishData)
        ;

        $result = $this->service->publishImageMomentFromBase64($account, ['base64data'], 'Base64图片分享');

        $this->assertEquals('b64_moment123', $result);
    }

    public function testUploadMomentImage(): void
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
        /**
         * 使用 WeChatApiAccount 具体类进行 Mock 的原因：
         * 1. 架构限制：WeChatApiAccount 是 Doctrine 实体类，没有对应的接口抽象
         * 2. 测试需求：需要模拟实体的 getter/setter 方法和状态管理行为
         * 3. 合理性评估：该实体类职责单一，仅负责数据存储，Mock 不会引入复杂依赖
         * 4. 替代方案：未来可考虑引入 ApiAccountInterface 接口，但当前架构下这是最优选择
         */
        /** @var WeChatApiAccount&MockObject $apiAccount */
        $apiAccount = $this->createMock(WeChatApiAccount::class);
        $account->method('getApiAccount')->willReturn($apiAccount);
        $account->method('getDeviceId')->willReturn('device123');

        $mockData = ['image_id' => 'base64_image123'];

        $this->apiClient
            ->expects($this->once())
            ->method('request')
            ->willReturn($mockData)
        ;

        $this->logger
            ->expects($this->once())
            ->method('info')
        ;

        $result = $this->service->uploadMomentImage($account, 'base64imagedata');

        $this->assertEquals('base64_image123', $result);
    }
}
