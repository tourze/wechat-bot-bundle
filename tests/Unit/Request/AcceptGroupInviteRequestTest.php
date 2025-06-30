<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Unit\Request;

use HttpClientBundle\Request\ApiRequest;
use PHPUnit\Framework\TestCase;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\AcceptGroupInviteRequest;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

class AcceptGroupInviteRequestTest extends TestCase
{
    private WeChatApiAccount $apiAccount;
    private AcceptGroupInviteRequest $request;

    protected function setUp(): void
    {
        $this->apiAccount = $this->createMock(WeChatApiAccount::class);
        $this->apiAccount->method('getAccessToken')->willReturn('test_token');

        $this->request = new AcceptGroupInviteRequest(
            $this->apiAccount,
            'device123',
            'encrypt_username',
            'ticket_value'
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

    public function testGetEncryptUsername(): void
    {
        $this->assertEquals('encrypt_username', $this->request->getEncryptUsername());
    }

    public function testGetTicket(): void
    {
        $this->assertEquals('ticket_value', $this->request->getTicket());
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('open/acceptChatRoomInvite', $this->request->getRequestPath());
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
        $this->assertEquals('encrypt_username', $options['json']['encryptUsername']);
        $this->assertEquals('ticket_value', $options['json']['ticket']);
    }
}