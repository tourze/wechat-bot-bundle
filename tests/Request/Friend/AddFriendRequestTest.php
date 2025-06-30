<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request\Friend;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Friend\AddFriendRequest;

/**
 * 添加好友请求测试
 */
class AddFriendRequestTest extends TestCase
{
    private WeChatApiAccount|MockObject $apiAccount;

    protected function setUp(): void
    {
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
            'wx_test_user',
            '添加好友验证消息'
        );

        $options = $request->getRequestOptions();

        $this->assertArrayHasKey('headers', $options);
        $this->assertArrayHasKey('json', $options);
        $this->assertEquals('test_token', $options['headers']['Authorization']);
        $this->assertEquals('application/json', $options['headers']['Content-Type']);
    }
}
