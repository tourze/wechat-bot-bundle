<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request\Account;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Account\ResetPasswordRequest;

/**
 * 重置账号密码请求测试
 */
class ResetPasswordRequestTest extends TestCase
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

        $this->assertArrayHasKey('headers', $options);
        $this->assertArrayHasKey('json', $options);
        $this->assertEquals('test_token', $options['headers']['Authorization']);
        $this->assertEquals('application/json', $options['headers']['Content-Type']);
        $this->assertEquals('old_password', $options['json']['oldPassword']);
        $this->assertEquals('new_password', $options['json']['newPassword']);
        $this->assertEquals('new_password', $options['json']['confirmPassword']);
    }
}
