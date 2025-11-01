<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\CreateGroupRequest;

/**
 * @internal
 */
#[CoversClass(CreateGroupRequest::class)]
final class CreateGroupRequestTest extends RequestTestCase
{
    private WeChatApiAccount $apiAccount;

    private CreateGroupRequest $request;

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
        $this->request = new CreateGroupRequest(
            $this->apiAccount,
            'device123',
            'Test Group',
            'wxid_user1,wxid_user2,wxid_user3'
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

    public function testGetTopic(): void
    {
        $this->assertEquals('Test Group', $this->request->getTopic());
    }

    public function testGetUserList(): void
    {
        $this->assertEquals('wxid_user1,wxid_user2,wxid_user3', $this->request->getUserList());
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
        $this->assertEquals('Test Group', $options['json']['topic']);
        $this->assertEquals('wxid_user1,wxid_user2,wxid_user3', $options['json']['userList']);
    }

    public function testGetRequestPath(): void
    {
        $this->assertEquals('open/createChatroom', $this->request->getRequestPath());
    }
}
