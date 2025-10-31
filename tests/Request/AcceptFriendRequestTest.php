<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Friend\AcceptFriendRequest;

/**
 * @internal
 */
#[CoversClass(AcceptFriendRequest::class)]
final class AcceptFriendRequestTest extends RequestTestCase
{
    private WeChatApiAccount $apiAccount;

    private AcceptFriendRequest $request;

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
        $this->request = new AcceptFriendRequest(
            $this->apiAccount,
            'device123',
            'wxid_test',
            true,
            'welcome message'
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
        $this->assertEquals('wxid_test', $this->request->getWxId());
    }

    public function testIsAccept(): void
    {
        $this->assertTrue($this->request->isAccept());
    }

    public function testGetMessage(): void
    {
        $this->assertEquals('welcome message', $this->request->getMessage());
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('open/acceptFriend', $this->request->getRequestPath());
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

        $this->assertIsArray($options['headers']);
        $this->assertIsArray($options['json']);
        $this->assertArrayHasKey('Authorization', $options['headers']);
        $this->assertArrayHasKey('Content-Type', $options['headers']);

        $this->assertEquals('test_token', $options['headers']['Authorization']);
        $this->assertEquals('application/json', $options['headers']['Content-Type']);

        $this->assertIsArray($options['json']);
        $this->assertArrayHasKey('deviceId', $options['json']);
        $this->assertArrayHasKey('wxId', $options['json']);
        $this->assertArrayHasKey('accept', $options['json']);
        $this->assertArrayHasKey('message', $options['json']);

        $this->assertEquals('device123', $options['json']['deviceId']);
        $this->assertEquals('wxid_test', $options['json']['wxId']);
        $this->assertTrue($options['json']['accept']);
        $this->assertEquals('welcome message', $options['json']['message']);
    }
}
