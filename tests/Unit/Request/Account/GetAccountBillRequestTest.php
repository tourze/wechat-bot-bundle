<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Unit\Request\Account;

use HttpClientBundle\Request\ApiRequest;
use PHPUnit\Framework\TestCase;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Account\GetAccountBillRequest;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

class GetAccountBillRequestTest extends TestCase
{
    private WeChatApiAccount $apiAccount;
    private GetAccountBillRequest $request;

    protected function setUp(): void
    {
        $this->apiAccount = $this->createMock(WeChatApiAccount::class);
        $this->apiAccount->method('getAccessToken')->willReturn('test_token');

        $this->request = new GetAccountBillRequest(
            $this->apiAccount,
            '2023-01-01',
            '2023-01-31',
            1,
            20
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

    public function testGetStartDate(): void
    {
        $this->assertEquals('2023-01-01', $this->request->getStartDate());
    }

    public function testGetEndDate(): void
    {
        $this->assertEquals('2023-01-31', $this->request->getEndDate());
    }

    public function testGetPage(): void
    {
        $this->assertEquals(1, $this->request->getPage());
    }

    public function testGetLimit(): void
    {
        $this->assertEquals(20, $this->request->getLimit());
    }

    public function testGetRequestMethod(): void
    {
        $this->assertEquals('GET', $this->request->getRequestMethod());
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('open/user/getBill', $this->request->getRequestPath());
    }

    public function testGetRequestOptions(): void
    {
        $options = $this->request->getRequestOptions();
        
        $this->assertIsArray($options);
        $this->assertArrayHasKey('headers', $options);
        $this->assertArrayHasKey('query', $options);
        
        $this->assertEquals('test_token', $options['headers']['Authorization']);
        $this->assertEquals(1, $options['query']['page']);
        $this->assertEquals(20, $options['query']['limit']);
        $this->assertEquals('2023-01-01', $options['query']['startDate']);
        $this->assertEquals('2023-01-31', $options['query']['endDate']);
    }

    public function testGetRequestOptionsWithNullDates(): void
    {
        $requestWithNullDates = new GetAccountBillRequest($this->apiAccount);
        $options = $requestWithNullDates->getRequestOptions();
        
        $this->assertIsArray($options);
        $this->assertArrayHasKey('query', $options);
        $this->assertArrayNotHasKey('startDate', $options['query']);
        $this->assertArrayNotHasKey('endDate', $options['query']);
    }
}