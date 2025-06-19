<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\LoginPlatformRequest;

/**
 * 登录API平台请求测试
 */
class LoginPlatformRequestTest extends TestCase
{
    private WeChatApiAccount|MockObject $apiAccount;

    /**
     * 测试请求构造
     */
    public function testConstruct(): void
    {
        $request = new LoginPlatformRequest($this->apiAccount);

        $this->assertInstanceOf(LoginPlatformRequest::class, $request);
        $this->assertEquals('POST', $request->getRequestMethod());
        $this->assertEquals('auth/login', $request->getRequestPath());
    }

    /**
     * 测试获取请求体
     */
    public function testGetBody(): void
    {
        $request = new LoginPlatformRequest($this->apiAccount);
        $options = $request->getRequestOptions();
        $this->assertArrayHasKey('form_params', $options);
        $this->assertEquals('test_account', $options['form_params']['username']);
        $this->assertEquals('test_password', $options['form_params']['password']);
    }

    /**
     * 测试获取Content-Type
     */
    public function testGetContentType(): void
    {
        $request = new LoginPlatformRequest($this->apiAccount);
        $options = $request->getRequestOptions();

        // form_params 使用 application/x-www-form-urlencoded content type
        $this->assertArrayHasKey('form_params', $options);
    }

    /**
     * 测试toString方法
     */
    public function testToString(): void
    {
        $request = new LoginPlatformRequest($this->apiAccount);
        $result = $request->__toString();

        $this->assertStringContainsString('LoginPlatformRequest', $result);
        $this->assertStringContainsString('test_account', $result);
    }

    protected function setUp(): void
    {
        $this->apiAccount = $this->createMock(WeChatApiAccount::class);
        $this->apiAccount->method('getUsername')->willReturn('test_account');
        $this->apiAccount->method('getPassword')->willReturn('test_password');
    }
}
