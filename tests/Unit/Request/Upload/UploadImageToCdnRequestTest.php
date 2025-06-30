<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Unit\Request\Upload;

use HttpClientBundle\Request\ApiRequest;
use PHPUnit\Framework\TestCase;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Request\Upload\UploadImageToCdnRequest;
use Tourze\WechatBotBundle\Request\WeChatRequestInterface;

class UploadImageToCdnRequestTest extends TestCase
{
    private WeChatApiAccount $apiAccount;
    private UploadImageToCdnRequest $request;

    protected function setUp(): void
    {
        $this->apiAccount = $this->createMock(WeChatApiAccount::class);
        $this->apiAccount->method('getAccessToken')->willReturn('test_token');

        $this->request = new UploadImageToCdnRequest($this->apiAccount, 'test_device', 'test_image_data');
    }

    public function testRequestIsInstanceOfApiRequest(): void
    {
        $this->assertInstanceOf(ApiRequest::class, $this->request);
    }

    public function testRequestImplementsWeChatRequestInterface(): void
    {
        $this->assertInstanceOf(WeChatRequestInterface::class, $this->request);
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
        
        if ($options !== null) {
            if (isset($options['headers'])) {
                $this->assertEquals('test_token', $options['headers']['Authorization']);
            }
        }
    }
}