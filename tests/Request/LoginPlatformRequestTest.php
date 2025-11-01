<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\LoginPlatformRequest;

/**
 * 登录API平台请求测试
 *
 * @internal
 */
#[CoversClass(LoginPlatformRequest::class)]
final class LoginPlatformRequestTest extends RequestTestCase
{
    private WeChatApiAccount&MockObject $apiAccount;

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
        $this->assertIsArray($options);
        $this->assertArrayHasKey('body', $options);
        $body = $options['body'];
        $this->assertIsString($body);
        $this->assertStringContainsString('username=test_account', $body);
        $this->assertStringContainsString('password=test_password', $body);
    }

    /**
     * 测试获取Content-Type
     */
    public function testGetContentType(): void
    {
        $request = new LoginPlatformRequest($this->apiAccount);
        $options = $request->getRequestOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('headers', $options);
        $this->assertIsArray($options['headers']);
        $this->assertIsArray($options['json']);
        $this->assertArrayHasKey('Content-Type', $options['headers']);
        $this->assertEquals('application/x-www-form-urlencoded', $options['headers']['Content-Type']);
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

    protected function onSetUp(): void
    {
        /*
         * 使用具体类创建 Mock 对象的原因：
         * 1) WeChatApiAccount 是实体类，没有对应的接口
         * 2) 为了测试请求类的构造和数据封装，需要 mock 依赖的实体对象
         * 3) 在单元测试中使用 mock 实体对象是测试最佳实践
         */
        $this->apiAccount = $this->createMock(WeChatApiAccount::class);
        $this->apiAccount->method('getUsername')->willReturn('test_account');
        $this->apiAccount->method('getPassword')->willReturn('test_password');
    }
}
