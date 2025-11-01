<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request\Group;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Group\AtGroupMemberRequest;

/**
 * @internal
 */
#[CoversClass(AtGroupMemberRequest::class)]
final class AtGroupMemberRequestTest extends RequestTestCase
{
    private WeChatApiAccount $apiAccount;

    private AtGroupMemberRequest $request;

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
        $this->request = new AtGroupMemberRequest(
            $this->apiAccount,
            'device123',
            'chatRoom456',
            'Hello @member',
            'member789'
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

    public function testGetChatRoomId(): void
    {
        $this->assertEquals('chatRoom456', $this->request->getChatRoomId());
    }

    public function testGetContent(): void
    {
        $this->assertEquals('Hello @member', $this->request->getContent());
    }

    public function testGetAtList(): void
    {
        $this->assertEquals('member789', $this->request->getAtList());
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('open/sendAtText', $this->request->getRequestPath());
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

        // Test headers
        $this->assertIsArray($options['headers']);
        $this->assertIsArray($options['json']);

        $this->assertEquals('test_token', $options['headers']['Authorization']);
        $this->assertEquals('application/json', $options['headers']['Content-Type']);

        // Test JSON payload
        $this->assertEquals('device123', $options['json']['deviceId']);
        $this->assertEquals('chatRoom456', $options['json']['wxId']);
        $this->assertEquals('Hello @member', $options['json']['content']);
        $this->assertEquals('member789', $options['json']['atList']);
    }
}
