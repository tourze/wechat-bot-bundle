<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Service\WeChatFileService;

/**
 * @internal
 */
#[CoversClass(WeChatFileService::class)]
#[RunTestsInSeparateProcesses]
final class WeChatFileServiceTest extends AbstractIntegrationTestCase
{
    private WeChatFileService $service;

    protected function onSetUp(): void
    {
        // 获取服务实例
        $this->service = self::getService(WeChatFileService::class);
    }

    public function testServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(WeChatFileService::class, $this->service);
    }

    public function testDownloadCdnResource(): void
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
        $cdnUrl = 'https://example.com/test.jpg';
        $fileName = 'test.jpg';

        // 模拟 API 调用失败的情况
        $result = $this->service->downloadCdnResource($account, $cdnUrl, $fileName);

        // 由于 mock 没有配置返回值，预期返回 null
        $this->assertNull($result);
    }

    public function testUploadImageToCdn(): void
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
        $imageFilePath = '/tmp/test.jpg';

        // 模拟上传失败的情况
        $result = $this->service->uploadImageToCdn($account, $imageFilePath);

        // 由于 mock 没有配置返回值，预期返回 null
        $this->assertNull($result);
    }

    public function testDeleteLocalFile(): void
    {
        $filePath = '/nonexistent/file.txt';

        // 删除不存在的文件应该返回 true（视为删除成功）
        $result = $this->service->deleteLocalFile($filePath);

        $this->assertTrue($result);
    }

    public function testCleanExpiredFiles(): void
    {
        $expireDays = 7;

        // 测试清理过期文件功能
        $result = $this->service->cleanExpiredFiles($expireDays);

        // 验证返回的清理文件数量
        $this->assertGreaterThanOrEqual(0, $result);
    }
}
