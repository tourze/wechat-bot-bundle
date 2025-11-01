<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request\Download;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Download\DownloadFileRequest;

/**
 * @internal
 */
#[CoversClass(DownloadFileRequest::class)]
final class DownloadFileRequestTest extends RequestTestCase
{
    private WeChatApiAccount $apiAccount;

    private DownloadFileRequest $request;

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
        $this->request = new DownloadFileRequest(
            $this->apiAccount,
            'device123',
            'file_123456',
            'wxid_sender123',
            'msg_789'
        );
    }

    public function testGetApiAccount(): void
    {
        $this->assertSame($this->apiAccount, $this->request->getApiAccount());
    }

    public function testGetDeviceId(): void
    {
        $this->assertEquals('device123', $this->request->getDeviceId());
    }

    public function testGetFileId(): void
    {
        $this->assertEquals('file_123456', $this->request->getFileId());
    }

    public function testGetFromUser(): void
    {
        $this->assertEquals('wxid_sender123', $this->request->getFromUser());
    }

    public function testGetMsgId(): void
    {
        $this->assertEquals('msg_789', $this->request->getMsgId());
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
        $this->assertEquals('application/x-www-form-urlencoded', $options['headers']['Content-Type']);

        $this->assertArrayHasKey('body', $options);
        $body = $options['body'];
        $this->assertIsString($body);
        $this->assertStringContainsString('deviceId=device123', $body);
        $this->assertStringContainsString('fileId=file_123456', $body);
        $this->assertStringContainsString('fromUser=wxid_sender123', $body);
        $this->assertStringContainsString('msgId=msg_789', $body);
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('open/downloadFile', $this->request->getRequestPath());
    }
}
