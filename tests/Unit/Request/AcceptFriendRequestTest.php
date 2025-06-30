<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Unit\Request;

use HttpClientBundle\Request\ApiRequest;
use PHPUnit\Framework\TestCase;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\AcceptFriendRequest;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

class AcceptFriendRequestTest extends TestCase
{
    private WeChatApiAccount $apiAccount;
    private AcceptFriendRequest $request;

    protected function setUp(): void
    {
        $this->apiAccount = $this->createMock(WeChatApiAccount::class);
        $this->apiAccount->method('getAccessToken')->willReturn('test_token');

        $this->request = new AcceptFriendRequest(
            $this->apiAccount,
            'device123',
            'v1_value',
            'v2_value',
            8
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

    public function testGetV1(): void
    {
        $this->assertEquals('v1_value', $this->request->getV1());
    }

    public function testGetV2(): void
    {
        $this->assertEquals('v2_value', $this->request->getV2());
    }

    public function testGetType(): void
    {
        $this->assertEquals(8, $this->request->getType());
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('open/acceptUser', $this->request->getRequestPath());
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
        $this->assertEquals('v1_value', $options['json']['v1']);
        $this->assertEquals('v2_value', $options['json']['v2']);
        $this->assertEquals(8, $options['json']['type']);
    }
}