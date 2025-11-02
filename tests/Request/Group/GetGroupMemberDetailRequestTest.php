<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request\Group;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Group\GetGroupMemberDetailRequest;

/**
 * @internal
 */
#[CoversClass(GetGroupMemberDetailRequest::class)]
final class GetGroupMemberDetailRequestTest extends RequestTestCase
{
    private WeChatApiAccount $apiAccount;

    private GetGroupMemberDetailRequest $request;

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
        $this->request = new GetGroupMemberDetailRequest(
            $this->apiAccount,
            'device123',
            'group456',
            'memberWxId789'
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

    public function testGetGroupId(): void
    {
        $this->assertEquals('group456', $this->request->getGroupId());
    }

    public function testGetMemberWxId(): void
    {
        $this->assertEquals('memberWxId789', $this->request->getMemberWxId());
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('open/getGroupMemberDetail', $this->request->getRequestPath());
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
        $this->assertArrayHasKey('query', $options);
        $this->assertIsArray($options['query']);

        // Test headers
        $this->assertIsArray($options['headers']);
        // GET 请求使用 query 参数，并不包含 json 负载
        $this->assertArrayNotHasKey('json', $options);

        $this->assertEquals('test_token', $options['headers']['Authorization']);

        // Test query parameters
        $this->assertEquals('device123', $options['query']['deviceId']);
        $this->assertEquals('group456', $options['query']['groupId']);
        $this->assertEquals('memberWxId789', $options['query']['memberWxId']);
    }
}
