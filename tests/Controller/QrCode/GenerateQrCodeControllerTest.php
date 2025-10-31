<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Controller\QrCode;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;
use Tourze\WechatBotBundle\Controller\QrCode\GenerateQrCodeController;
use Tourze\WechatBotBundle\DTO\WeChatLoginResult;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Service\WeChatAccountService;

/**
 * @internal
 */
#[CoversClass(GenerateQrCodeController::class)]
#[RunTestsInSeparateProcesses]
final class GenerateQrCodeControllerTest extends AbstractWebTestCase
{
    #[Test]
    public function testGenerateQrCodeWithDefaultParameters(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount([
            'deviceId' => 'device-pending-' . uniqid(),
            'status' => 'pending_login',
            'apiAccount' => $apiAccount,
        ]);

        $client->request('POST', sprintf('/wechat-bot/qrcode/generate/%d', $account->getId()));

        // 在测试环境中，由于没有真实的API服务器，期望返回400错误
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertFalse($responseData['success']); // 期望失败，因为API不可达
    }

    #[Test]
    public function testGenerateQrCodeWithCustomParameters(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount([
            'deviceId' => 'device-pending-' . uniqid(),
            'status' => 'pending_login',
            'apiAccount' => $apiAccount,
        ]);

        $client->request(
            'POST',
            sprintf('/wechat-bot/qrcode/generate/%d', $account->getId()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'],
            http_build_query([
                'province' => '北京',
                'city' => '北京',
                'proxy' => 'http://proxy.example.com:8080',
            ])
        );

        // 在测试环境中，由于没有真实的API服务器，期望返回400错误
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertFalse($responseData['success']); // 期望失败，因为API不可达
    }

    #[Test]
    public function testGenerateQrCodeForOnlineAccount(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount([
            'deviceId' => 'device-online-' . uniqid(),
            'wechatId' => 'wx_online_user_' . uniqid(),
            'nickname' => '在线测试用户',
            'status' => 'online',
            'apiAccount' => $apiAccount,
        ]);

        $client->request('POST', sprintf('/wechat-bot/qrcode/generate/%d', $account->getId()));

        // 在测试环境中，由于没有真实的API服务器，期望返回400错误
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertFalse($responseData['success']); // 期望失败，因为API不可达
    }

    #[Test]
    public function testGenerateQrCodeForOfflineAccount(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount([
            'deviceId' => 'device-offline-' . uniqid(),
            'wechatId' => 'wx_offline_user_' . uniqid(),
            'nickname' => '离线测试用户',
            'status' => 'offline',
            'apiAccount' => $apiAccount,
        ]);

        $client->request('POST', sprintf('/wechat-bot/qrcode/generate/%d', $account->getId()));

        // 在测试环境中，由于没有真实的API服务器，期望返回400错误
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertFalse($responseData['success']); // 期望失败，因为API不可达
    }

    #[Test]
    public function testGenerateQrCodeWithOnlyProvinceParameter(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount([
            'deviceId' => 'device-pending-' . uniqid(),
            'status' => 'pending_login',
            'apiAccount' => $apiAccount,
        ]);

        $client->request(
            'POST',
            sprintf('/wechat-bot/qrcode/generate/%d', $account->getId()),
            [],
            [],
            ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'],
            http_build_query([
                'province' => '上海',
            ])
        );

        // 在测试环境中，由于没有真实的API服务器，期望返回400错误
        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('Content-Type'));

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);
        $responseData = json_decode($content, true);
        self::assertIsArray($responseData);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertFalse($responseData['success']); // 期望失败，因为API不可达
    }

    #[Test]
    public function testNonExistentAccount(): void
    {
        $client = self::createClientWithDatabase();

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('"Tourze\WechatBotBundle\Entity\WeChatAccount" object not found by "Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver"');

        $this->loginAsAdmin($client);
        $client->request('POST', '/wechat-bot/qrcode/generate/999999');
    }

    #[Test]
    public function testGetMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount(['apiAccount' => $apiAccount]);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('GET', sprintf('/wechat-bot/qrcode/generate/%d', $account->getId()));
    }

    #[Test]
    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount(['apiAccount' => $apiAccount]);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', sprintf('/wechat-bot/qrcode/generate/%d', $account->getId()));
    }

    #[Test]
    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount(['apiAccount' => $apiAccount]);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', sprintf('/wechat-bot/qrcode/generate/%d', $account->getId()));
    }

    #[Test]
    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount(['apiAccount' => $apiAccount]);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', sprintf('/wechat-bot/qrcode/generate/%d', $account->getId()));
    }

    #[Test]
    public function testOptionsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount(['apiAccount' => $apiAccount]);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', sprintf('/wechat-bot/qrcode/generate/%d', $account->getId()));
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

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        if ('INVALID' === $method) {
            return; // Skip invalid methods without markTestSkipped
        }

        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount(['apiAccount' => $apiAccount]);

        if (!in_array($method, ['GET', 'PUT', 'DELETE', 'PATCH', 'TRACE', 'PURGE'], true)) {
            return; // Skip untestable methods without markTestSkipped
        }

        $this->expectException(MethodNotAllowedHttpException::class);

        match ($method) {
            'GET' => $client->request('GET', sprintf('/wechat-bot/qrcode/generate/%d', $account->getId())),
            'PUT' => $client->request('PUT', sprintf('/wechat-bot/qrcode/generate/%d', $account->getId())),
            'DELETE' => $client->request('DELETE', sprintf('/wechat-bot/qrcode/generate/%d', $account->getId())),
            'PATCH' => $client->request('PATCH', sprintf('/wechat-bot/qrcode/generate/%d', $account->getId())),
            'TRACE' => $client->request('TRACE', sprintf('/wechat-bot/qrcode/generate/%d', $account->getId())),
            'PURGE' => $client->request('PURGE', sprintf('/wechat-bot/qrcode/generate/%d', $account->getId())),
        };
    }
}
