<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request\Group;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Group\AddGroupMemberAsFriendRequest;

/**
 * @internal
 */
#[CoversClass(AddGroupMemberAsFriendRequest::class)]
final class AddGroupMemberAsFriendRequestTest extends RequestTestCase
{
    private WeChatApiAccount $apiAccount;

    private AddGroupMemberAsFriendRequest $request;

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
        $this->request = new AddGroupMemberAsFriendRequest(
            $this->apiAccount,
            'device123',
            'group456',
            'memberWxId789',
            '我是群友，想加你为好友'
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

    public function testGetMessage(): void
    {
        $this->assertEquals('我是群友，想加你为好友', $this->request->getMessage());
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
        $this->assertEquals('group456', $options['json']['groupId']);
        $this->assertEquals('memberWxId789', $options['json']['memberWxId']);
        $this->assertEquals('我是群友，想加你为好友', $options['json']['message']);
    }
}
