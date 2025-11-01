<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request\Account;

use HttpClientBundle\Test\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Account\ResetPasswordRequest;

/**
 * 重置账号密码请求测试
 *
 * @internal
 */
#[CoversClass(ResetPasswordRequest::class)]
final class ResetPasswordRequestTest extends RequestTestCase
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
        $request = new ResetPasswordRequest(
            $this->apiAccount,
            'old_password',
            'new_password',
            'new_password'
        );

        $this->assertInstanceOf(ResetPasswordRequest::class, $request);
        $this->assertEquals('POST', $request->getRequestMethod());
        $this->assertEquals('open/user/resetPassword', $request->getRequestPath());
        $this->assertEquals($this->apiAccount, $request->getApiAccount());
        $this->assertEquals('old_password', $request->getOldPassword());
        $this->assertEquals('new_password', $request->getNewPassword());
        $this->assertEquals('new_password', $request->getConfirmPassword());
    }

    /**
     * 测试请求选项
     */
    public function testGetRequestOptions(): void
    {
        $request = new ResetPasswordRequest(
            $this->apiAccount,
            'old_password',
            'new_password',
            'new_password'
        );

        $options = $request->getRequestOptions();

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
        $this->assertArrayHasKey('oldPassword', $options['json']);
        $this->assertArrayHasKey('newPassword', $options['json']);
        $this->assertArrayHasKey('confirmPassword', $options['json']);

        $this->assertEquals('old_password', $options['json']['oldPassword']);
        $this->assertEquals('new_password', $options['json']['newPassword']);
        $this->assertEquals('new_password', $options['json']['confirmPassword']);
    }
}
