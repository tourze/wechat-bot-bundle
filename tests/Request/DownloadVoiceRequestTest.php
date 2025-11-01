<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\DownloadVoiceRequest;

/**
 * @internal
 */
#[CoversClass(DownloadVoiceRequest::class)]
final class DownloadVoiceRequestTest extends RequestTestCase
{
    private WeChatApiAccount $apiAccount;

    private DownloadVoiceRequest $request;

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
        $this->request = new DownloadVoiceRequest(
            $this->apiAccount,
            'device123',
            123456,
            5000,
            789012,
            'wxid_sender'
        );
    }

    public function testGetApiAccount(): void
    {
        $this->assertSame($this->apiAccount, $this->request->getApiAccount());
    }

    // Note: The DownloadVoiceRequest class doesn't expose getter methods for its properties
    // They are only used internally in getRequestOptions()

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
        $this->assertEquals('application/json', $options['headers']['Content-Type']);

        $this->assertArrayHasKey('json', $options);
        $this->assertEquals('device123', $options['json']['deviceId']);
        $this->assertEquals(123456, $options['json']['msgId']);
        $this->assertEquals(5000, $options['json']['length']);
        $this->assertEquals(789012, $options['json']['bufId']);
        $this->assertEquals('wxid_sender', $options['json']['fromUser']);

        $this->assertArrayHasKey('timeout', $options);
        $this->assertEquals(60, $options['timeout']);
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('open/getMsgVoice', $this->request->getRequestPath());
    }
}
