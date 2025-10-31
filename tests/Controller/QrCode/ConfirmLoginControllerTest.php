<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Controller\QrCode;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\WechatBotBundle\Client\WeChatApiClient;
use Tourze\WechatBotBundle\Controller\QrCode\ConfirmLoginController;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * @internal
 */
#[CoversClass(ConfirmLoginController::class)]
#[RunTestsInSeparateProcesses]
final class ConfirmLoginControllerTest extends AbstractWebTestCase
{
    private MockObject&WeChatApiClient $mockApiClient;

    protected function onSetUp(): void
    {
        parent::onSetUp();
        $this->mockApiClient = $this->createMock(WeChatApiClient::class);
    }

    private function setupMockApiClient(): void
    {
        self::getContainer()->set(WeChatApiClient::class, $this->mockApiClient);
    }

    #[Test]
    public function testConfirmLoginForOnlineAccount(): void
    {
        $client = self::createClientWithDatabase();
        $this->setupMockApiClient();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount([
            'deviceId' => 'device-online-' . uniqid(),
            'wechatId' => 'wx_online_user_' . uniqid(),
            'nickname' => '在线测试用户',
            'status' => 'online',
            'apiAccount' => $apiAccount,
        ]);

        // Mock API客户端返回成功登录响应
        $this->mockApiClient->method('request')->willReturn([
            'data' => [
                'login' => true,
                'wxId' => $account->getWechatId(),
                'nickname' => $account->getNickname(),
                'avatar' => 'https://example.com/avatar.jpg',
            ],
        ]);

        $client->request('POST', sprintf('/wechat-bot/qrcode/confirm/%d', $account->getId()));

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('登录确认成功', $responseData['message']);
        $this->assertArrayHasKey('account', $responseData);
        self::assertIsArray($responseData['account']);
        $this->assertEquals($account->getWechatId(), $responseData['account']['wechatId']);
        $this->assertEquals('在线测试用户', $responseData['account']['nickname']);
    }

    #[Test]
    public function testConfirmLoginForPendingAccount(): void
    {
        $client = self::createClientWithDatabase();
        $this->setupMockApiClient();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount([
            'deviceId' => 'device-pending-' . uniqid(),
            'status' => 'pending_login',
            'apiAccount' => $apiAccount,
        ]);

        // Mock API客户端返回等待登录响应
        $this->mockApiClient->method('request')->willReturn([
            'data' => [
                'login' => false,
            ],
        ]);

        $client->request('POST', sprintf('/wechat-bot/qrcode/confirm/%d', $account->getId()));

        $response = $client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('等待扫码登录', $responseData['message']);
    }

    #[Test]
    public function testConfirmLoginForOfflineAccount(): void
    {
        $client = self::createClientWithDatabase();
        $this->setupMockApiClient();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount([
            'deviceId' => 'device-offline-' . uniqid(),
            'wechatId' => 'wx_offline_user_' . uniqid(),
            'nickname' => '离线测试用户',
            'status' => 'offline',
            'apiAccount' => $apiAccount,
        ]);

        // Mock API客户端返回离线响应
        $this->mockApiClient->method('request')->willReturn([
            'data' => [
                'login' => false,
            ],
        ]);

        $client->request('POST', sprintf('/wechat-bot/qrcode/confirm/%d', $account->getId()));

        $response = $client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('等待扫码登录', $responseData['message']);
    }

    #[Test]
    public function testNonExistentAccount(): void
    {
        $client = self::createClientWithDatabase();
        $this->setupMockApiClient();
        $this->loginAsAdmin($client);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('"Tourze\WechatBotBundle\Entity\WeChatAccount" object not found by "Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver"');

        $client->request('POST', '/wechat-bot/qrcode/confirm/999999');
    }

    private function createWeChatApiAccount(): WeChatApiAccount
    {
        $em = self::getEntityManager();

        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('测试API账号-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test_user');
        $apiAccount->setPassword('test_password');
        $apiAccount->setValid(true);
        $apiAccount->setRemark('测试环境API账号');

        $em->persist($apiAccount);
        $em->flush();

        return $apiAccount;
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        if ('INVALID' === $method) {
            return; // Skip invalid methods without markTestSkipped
        }

        // Verify that method is testable before proceeding
        $testableMethods = ['GET', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'TRACE', 'PURGE'];
        if (!\in_array($method, $testableMethods, true)) {
            return; // Skip untestable methods without markTestSkipped
        }

        $client = self::createClientWithDatabase();
        $this->setupMockApiClient();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount(['apiAccount' => $apiAccount]);

        $this->expectException(MethodNotAllowedHttpException::class);

        match ($method) {
            'GET' => $client->request('GET', sprintf('/wechat-bot/qrcode/confirm/%d', $account->getId())),
            'PUT' => $client->request('PUT', sprintf('/wechat-bot/qrcode/confirm/%d', $account->getId())),
            'DELETE' => $client->request('DELETE', sprintf('/wechat-bot/qrcode/confirm/%d', $account->getId())),
            'PATCH' => $client->request('PATCH', sprintf('/wechat-bot/qrcode/confirm/%d', $account->getId())),
            'OPTIONS' => $client->request('OPTIONS', sprintf('/wechat-bot/qrcode/confirm/%d', $account->getId())),
            'TRACE' => $client->request('TRACE', sprintf('/wechat-bot/qrcode/confirm/%d', $account->getId())),
            'PURGE' => $client->request('PURGE', sprintf('/wechat-bot/qrcode/confirm/%d', $account->getId())),
        };
    }

    /**
     * @param array<string, mixed> $data
     */
    private function createWeChatAccount(array $data = []): WeChatAccount
    {
        $em = self::getEntityManager();

        $account = new WeChatAccount();

        /** @var string $deviceId */
        $deviceId = $data['deviceId'] ?? 'test-device-' . uniqid();
        $account->setDeviceId($deviceId);

        /** @var string $status */
        $status = $data['status'] ?? 'pending_login';
        $account->setStatus($status);

        $account->setValid(true);

        if (array_key_exists('wechatId', $data)) {
            /** @var string|null $wechatId */
            $wechatId = $data['wechatId'];
            $account->setWechatId($wechatId);
        }
        if (array_key_exists('nickname', $data)) {
            /** @var string|null $nickname */
            $nickname = $data['nickname'];
            $account->setNickname($nickname);
        }
        if (array_key_exists('avatar', $data)) {
            /** @var string|null $avatar */
            $avatar = $data['avatar'];
            $account->setAvatar($avatar);
        }
        if (array_key_exists('apiAccount', $data)) {
            /** @var WeChatApiAccount|null $apiAccount */
            $apiAccount = $data['apiAccount'];
            $account->setApiAccount($apiAccount);
        }

        $em->persist($account);
        $em->flush();

        return $account;
    }
}
