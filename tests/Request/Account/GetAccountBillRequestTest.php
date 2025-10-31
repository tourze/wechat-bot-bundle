<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request\Account;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Account\GetAccountBillRequest;

/**
 * @internal
 */
#[CoversClass(GetAccountBillRequest::class)]
final class GetAccountBillRequestTest extends RequestTestCase
{
    private WeChatApiAccount $apiAccount;

    private GetAccountBillRequest $request;

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
        $this->request = new GetAccountBillRequest(
            $this->apiAccount,
            '2023-01-01',
            '2023-01-31',
            1,
            20
        );
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
        $this->assertIsArray($options['query']);

        $this->assertIsArray($options['headers']);
        $this->assertIsArray($options['json']);
        $this->assertArrayHasKey('Authorization', $options['headers']);

        $this->assertEquals('test_token', $options['headers']['Authorization']);

        $this->assertIsArray($options['query']);
        $this->assertArrayHasKey('page', $options['query']);
        $this->assertArrayHasKey('limit', $options['query']);
        $this->assertArrayHasKey('startDate', $options['query']);
        $this->assertArrayHasKey('endDate', $options['query']);

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
        $this->assertIsArray($options['query']);

        $this->assertIsArray($options['query']);
        $this->assertArrayNotHasKey('startDate', $options['query']);
        $this->assertArrayNotHasKey('endDate', $options['query']);
    }
}
