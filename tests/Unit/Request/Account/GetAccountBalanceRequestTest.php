<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Unit\Request\Account;

use HttpClientBundle\Request\ApiRequest;
use PHPUnit\Framework\TestCase;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Account\GetAccountBalanceRequest;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

class GetAccountBalanceRequestTest extends TestCase
{
    private WeChatApiAccount $apiAccount;
    private GetAccountBalanceRequest $request;

    protected function setUp(): void
    {
        $this->apiAccount = $this->createMock(WeChatApiAccount::class);
        $this->apiAccount->method('getAccessToken')->willReturn('test_token');

        $this->request = new GetAccountBalanceRequest($this->apiAccount);
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

    public function testGetRequestMethod(): void
    {
        $this->assertEquals('GET', $this->request->getRequestMethod());
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('open/user/getBalance', $this->request->getRequestPath());
    }

    public function testGetRequestOptions(): void
    {
        $options = $this->request->getRequestOptions();
        
        $this->assertIsArray($options);
        $this->assertArrayHasKey('headers', $options);
        $this->assertEquals('test_token', $options['headers']['Authorization']);
    }
}