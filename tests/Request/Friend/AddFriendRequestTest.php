<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request\Friend;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Friend\AddFriendRequest;

/**
 * 添加好友请求测试
 *
 * @internal
 */
#[CoversClass(AddFriendRequest::class)]
final class AddFriendRequestTest extends RequestTestCase
{
    private WeChatApiAccount&MockObject $apiAccount;

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
    }

    /**
     * 测试请求构造
     */
    public function testConstruct(): void
    {
        $request = new AddFriendRequest(
            $this->apiAccount,
            'device123',
            'wx_test_user',
            '添加好友验证消息'
        );

        $this->assertInstanceOf(AddFriendRequest::class, $request);
        $this->assertEquals('POST', $request->getRequestMethod());
        $this->assertEquals($this->apiAccount, $request->getApiAccount());
    }

    /**
     * 测试请求选项
     */
    public function testGetRequestOptions(): void
    {
        $request = new AddFriendRequest(
            $this->apiAccount,
            'device123',
            'wx_test_user',
            '添加好友验证消息'
        );

        $options = $request->getRequestOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('headers', $options);
        $this->assertArrayHasKey('json', $options);
        $this->assertIsArray($options['headers']);
        $this->assertIsArray($options['json']);

        $this->assertEquals('test_token', $options['headers']['Authorization']);
        $this->assertEquals('application/json', $options['headers']['Content-Type']);
    }
}
