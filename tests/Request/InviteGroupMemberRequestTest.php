<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request;

use PHPUnit\Framework\Attributes\CoversClass;
use HttpClientBundle\Tests\Request\RequestTestCase;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Group\InviteGroupMemberRequest;

/**
 * @internal
 */
#[CoversClass(InviteGroupMemberRequest::class)]
final class InviteGroupMemberRequestTest extends RequestTestCase
{
    private WeChatApiAccount $apiAccount;

    private InviteGroupMemberRequest $request;

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
        $this->request = new InviteGroupMemberRequest(
            $this->apiAccount,
            'device123',
            'chatroom123@chatroom',
            'user1,user2,user3'
        );
    }

    public function testGetApiAccount(): void
    {
        $this->assertSame($this->apiAccount, $this->request->getApiAccount());
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
        $this->assertIsArray($options['headers']);
        $this->assertIsArray($options['json']);

        $this->assertEquals('test_token', $options['headers']['Authorization']);
        $this->assertEquals('application/json', $options['headers']['Content-Type']);

        $this->assertArrayHasKey('json', $options);
        $this->assertEquals('device123', $options['json']['deviceId']);
        $this->assertEquals('chatroom123@chatroom', $options['json']['chatRoomId']);
        $this->assertEquals('user1,user2,user3', $options['json']['userList']);
    }

    public function testGetDeviceId(): void
    {
        $this->assertEquals('device123', $this->request->getDeviceId());
    }

    public function testGetChatRoomId(): void
    {
        $this->assertEquals('chatroom123@chatroom', $this->request->getChatRoomId());
    }

    public function testGetUserList(): void
    {
        $this->assertEquals('user1,user2,user3', $this->request->getUserList());
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('open/inviteChatRoomMember', $this->request->getRequestPath());
    }
}
