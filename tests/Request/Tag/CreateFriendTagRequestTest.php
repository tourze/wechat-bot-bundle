<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Request\Tag;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Tag\CreateFriendTagRequest;

/**
 * @internal
 */
#[CoversClass(CreateFriendTagRequest::class)]
final class CreateFriendTagRequestTest extends RequestTestCase
{
    private WeChatApiAccount $apiAccount;

    private CreateFriendTagRequest $request;

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
        $this->request = new CreateFriendTagRequest($this->apiAccount, 'test_device', 'test_tag');
    }

    public function testGetApiAccount(): void
    {
        $this->assertSame($this->apiAccount, $this->request->getApiAccount());
    }

    public function testGetRequestMethod(): void
    {
        $method = $this->request->getRequestMethod();
        $this->assertNotEmpty($method);
    }

    public function testGetRequestPath(): void
    {
        $path = $this->request->getRequestPath();
        $this->assertNotEmpty($path);
    }

    public function testGetRequestOptions(): void
    {
        $options = $this->request->getRequestOptions();

        if (null !== $options) {
            if (isset($options['headers']) && is_array($options['headers'])) {
                $this->assertIsArray($options['headers']);
        $this->assertIsArray($options['json']);

                $this->assertEquals('test_token', $options['headers']['Authorization']);
            }
        }
    }
}
