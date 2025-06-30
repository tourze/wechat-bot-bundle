<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Unit\Request;

use HttpClientBundle\Request\ApiRequest;
use PHPUnit\Framework\TestCase;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\CheckOnlineStatusRequest;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

class CheckOnlineStatusRequestTest extends TestCase
{
    private WeChatApiAccount $apiAccount;
    private CheckOnlineStatusRequest $request;

    protected function setUp(): void
    {
        $this->apiAccount = $this->createMock(WeChatApiAccount::class);
        $this->apiAccount->method('getAccessToken')->willReturn('test_token');

        $this->request = new CheckOnlineStatusRequest(
            $this->apiAccount,
            'device123'
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

    public function testGetRequestPath(): void
    {
        $this->assertEquals('open/isOnline', $this->request->getRequestPath());
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
    }
}