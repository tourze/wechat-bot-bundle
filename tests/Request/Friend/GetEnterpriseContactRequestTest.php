<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request\Friend;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Friend\GetEnterpriseContactRequest;

/**
 * @internal
 */
#[CoversClass(GetEnterpriseContactRequest::class)]
final class GetEnterpriseContactRequestTest extends RequestTestCase
{
    private WeChatApiAccount $apiAccount;

    private GetEnterpriseContactRequest $request;

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
        $this->request = new GetEnterpriseContactRequest(
            $this->apiAccount,
            'device123',
            'wxid_enterprise123'
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

    public function testGetWxId(): void
    {
        $this->assertEquals('wxid_enterprise123', $this->request->getWxId());
    }

    public function testGetRequestMethod(): void
    {
        $this->assertEquals('GET', $this->request->getRequestMethod());
    }

    public function testGetRequestOptions(): void
    {
        $options = $this->request->getRequestOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('headers', $options);
        $this->assertIsArray($options['headers']);
        $this->assertIsArray($options['json']);

        $this->assertEquals('test_token', $options['headers']['Authorization']);

        $this->assertArrayHasKey('query', $options);
        $this->assertIsArray($options['query']);
        $this->assertEquals('device123', $options['query']['deviceId']);
        $this->assertEquals('wxid_enterprise123', $options['query']['wxId']);
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('open/getEnterpriseContact', $this->request->getRequestPath());
    }
}
