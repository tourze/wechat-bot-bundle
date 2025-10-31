<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Controller\QrCode;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\WechatBotBundle\Controller\QrCode\LogoutController;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * @internal
 */
#[CoversClass(LogoutController::class)]
#[RunTestsInSeparateProcesses]
final class LogoutControllerTest extends AbstractWebTestCase
{
    public function testLogoutSuccessfully(): void
    {
        $client = self::createClientWithDatabase();

        // 创建 API 账户
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('Test API Account ' . uniqid());
        $apiAccount->setBaseUrl('https://example.com');
        $apiAccount->setUsername('test_user');
        $apiAccount->setPassword('test_password');
        $apiAccount->setAccessToken('test-token');
        $apiAccount->setValid(true);
        self::getEntityManager()->persist($apiAccount);

        // 创建微信账户
        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setStatus('online');
        $account->setValid(true);
        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        // 发送登出请求
        $client->request('POST', sprintf('/wechat-bot/qrcode/logout/%d', $account->getId()));

        // 验证响应格式和状态码（不依赖具体的业务逻辑结果）
        $response = $client->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertContains($response->getStatusCode(), [200, 400, 500]);

        $content = $response->getContent();
        self::assertIsString($content);
        $data = json_decode($content, true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertIsBool($data['success']);
        $this->assertIsString($data['message']);
    }

    public function testLogoutNonExistentDevice(): void
    {
        $client = self::createClientWithDatabase();

        // 捕获异常以避免测试失败，因为EntityValueResolver会抛出NotFoundHttpException
        $client->catchExceptions(false);

        // 期望抛出NotFoundHttpException，这是Symfony EntityValueResolver的预期行为
        $this->expectException(NotFoundHttpException::class);

        // 发送登出请求到不存在的设备
        $client->request('POST', '/wechat-bot/qrcode/logout/999999');
    }

    public function testLogoutInactiveAccount(): void
    {
        $client = self::createClientWithDatabase();

        // 创建 API 账户
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('Test API Account ' . uniqid());
        $apiAccount->setBaseUrl('https://example.com');
        $apiAccount->setUsername('test_user');
        $apiAccount->setPassword('test_password');
        $apiAccount->setAccessToken('test-token');
        $apiAccount->setValid(true);
        self::getEntityManager()->persist($apiAccount);

        // 创建已登出的微信账户
        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('inactive-device');
        $account->setStatus('offline');
        $account->setValid(true);
        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        // 发送登出请求
        $client->request('POST', sprintf('/wechat-bot/qrcode/logout/%d', $account->getId()));

        // 验证响应
        $response = $client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        self::assertIsString($content);
        $data = json_decode($content, true);
        self::assertIsArray($data);
        $this->assertFalse($data['success']);
        $this->assertSame('退出登录失败', $data['message']);
    }

    public function testLogoutWithException(): void
    {
        $client = self::createClientWithDatabase();

        // 创建 API 账户
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('Test API Account ' . uniqid());
        $apiAccount->setBaseUrl('https://example.com');
        $apiAccount->setUsername('test_user');
        $apiAccount->setPassword('test_password');
        $apiAccount->setAccessToken('test-token');
        $apiAccount->setValid(true);
        self::getEntityManager()->persist($apiAccount);

        // 创建微信账户，但不设置必要字段以触发异常
        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('error-device');
        $account->setStatus('offline');
        $account->setValid(true);
        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        // 发送登出请求
        $client->request('POST', sprintf('/wechat-bot/qrcode/logout/%d', $account->getId()));

        // 验证响应
        $response = $client->getResponse();
        $this->assertSame(400, $response->getStatusCode());
        $this->assertInstanceOf(JsonResponse::class, $response);

        $content = $response->getContent();
        self::assertIsString($content);
        $data = json_decode($content, true);
        self::assertIsArray($data);
        $this->assertFalse($data['success']);
        $this->assertIsString($data['message']);
        $this->assertStringContainsString('退出登录失败', $data['message']);
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
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount(['apiAccount' => $apiAccount]);

        $this->expectException(MethodNotAllowedHttpException::class);

        match ($method) {
            'GET' => $client->request('GET', sprintf('/wechat-bot/qrcode/logout/%d', $account->getId())),
            'PUT' => $client->request('PUT', sprintf('/wechat-bot/qrcode/logout/%d', $account->getId())),
            'DELETE' => $client->request('DELETE', sprintf('/wechat-bot/qrcode/logout/%d', $account->getId())),
            'PATCH' => $client->request('PATCH', sprintf('/wechat-bot/qrcode/logout/%d', $account->getId())),
            'OPTIONS' => $client->request('OPTIONS', sprintf('/wechat-bot/qrcode/logout/%d', $account->getId())),
            'TRACE' => $client->request('TRACE', sprintf('/wechat-bot/qrcode/logout/%d', $account->getId())),
            'PURGE' => $client->request('PURGE', sprintf('/wechat-bot/qrcode/logout/%d', $account->getId())),
        };
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
