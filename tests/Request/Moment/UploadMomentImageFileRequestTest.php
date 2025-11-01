<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request\Moment;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Moment\UploadMomentImageFileRequest;

/**
 * @internal
 */
#[CoversClass(UploadMomentImageFileRequest::class)]
final class UploadMomentImageFileRequestTest extends RequestTestCase
{
    private WeChatApiAccount $apiAccount;

    private UploadMomentImageFileRequest $request;

    protected function onSetUp(): void
    {
        /*
         * 使用具体类创建 Mock 对象的原因：
         * 1) WeChatApiAccount 是实体类，没有对应的接口
         * 2) 为了测试请求类的构造和数据封装，需要 mock 依赖的实体对象
         * 3) 在单元测试中使用 mock 实体对象是测试最佳实践
         */
        $this->apiAccount = $this->createMock(WeChatApiAccount::class);
        $this->apiAccount->method('getAccessToken')->willReturn('test_token');
        // Create a temporary image file for testing
        $tempImageFile = tempnam(sys_get_temp_dir(), 'test_image_') . '.jpg';
        file_put_contents($tempImageFile, 'fake image content');
        $this->request = new UploadMomentImageFileRequest(
            $this->apiAccount,
            'device123',
            $tempImageFile
        );
    }

    public function testGetApiAccount(): void
    {
        $this->assertSame($this->apiAccount, $this->request->getApiAccount());
    }

    public function testGetRequestMethod(): void
    {
        $this->assertEquals('POST', $this->request->getRequestMethod());
    }

    public function testGetRequestOptions(): void
    {
        $options = $this->request->getRequestOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('headers', $options);
        $this->assertIsArray($options['headers']);
        $this->assertIsArray($options['json']);

        $this->assertEquals('test_token', $options['headers']['Authorization']);
    }
}
