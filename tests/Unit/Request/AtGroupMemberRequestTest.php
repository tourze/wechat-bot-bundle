<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Unit\Request;

use HttpClientBundle\Request\ApiRequest;
use PHPUnit\Framework\TestCase;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\AtGroupMemberRequest;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

class AtGroupMemberRequestTest extends TestCase
{
    private WeChatApiAccount $apiAccount;
    private AtGroupMemberRequest $request;

    protected function setUp(): void
    {
        $this->apiAccount = $this->createMock(WeChatApiAccount::class);
        $this->apiAccount->method('getAccessToken')->willReturn('test_token');

        $this->request = new AtGroupMemberRequest(
            $this->apiAccount,
            'device123',
            'wxid_test',
            '@someone 测试消息',
            'wxid_someone'
        );
    }

    public function testRequestIsInstanceOfApiRequest(): void
    {
        $this->assertInstanceOf(ApiRequest::class, $this->request);
    }

    public function testRequestImplementsWeChatRequestInterface(): void
    {
        $this->assertInstanceOf(WeChatRequestInterface::class, $this->request);
    }

    public function testGetApiAccount(): void
    {
        $this->assertSame($this->apiAccount, $this->request->getApiAccount());
    }

    public function testGetDeviceId(): void
    {
        $this->assertEquals('device123', $this->request->getDeviceId());
    }

    public function testGetWxId(): void
    {
        $this->assertEquals('wxid_test', $this->request->getWxId());
    }

    public function testGetContent(): void
    {
        $this->assertEquals('@someone 测试消息', $this->request->getContent());
    }

    public function testGetAt(): void
    {
        $this->assertEquals('wxid_someone', $this->request->getAt());
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('open/sendText', $this->request->getRequestPath());
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
        $this->assertArrayHasKey('json', $options);

        $this->assertEquals('test_token', $options['headers']['Authorization']);
        $this->assertEquals('application/json', $options['headers']['Content-Type']);

        $this->assertEquals('device123', $options['json']['deviceId']);
        $this->assertEquals('wxid_test', $options['json']['wxId']);
        $this->assertEquals('@someone 测试消息', $options['json']['content']);
        $this->assertEquals('wxid_someone', $options['json']['at']);
    }
}