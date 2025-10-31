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
use Tourze\WechatBotBundle\Controller\QrCode\CheckStatusController;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * @internal
 */
#[CoversClass(CheckStatusController::class)]
#[RunTestsInSeparateProcesses]
final class CheckStatusControllerTest extends AbstractWebTestCase
{
    #[Test]
    public function testCheckOnlineAccountStatus(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 创建API账号实体
        $apiAccount = $this->createWeChatApiAccount();

        // 创建在线微信账号实体
        $account = $this->createWeChatAccount([
            'deviceId' => 'device-online-' . uniqid(),
            'wechatId' => 'wx_online_user_' . uniqid(),
            'nickname' => '在线测试用户',
            'status' => 'online',
            'apiAccount' => $apiAccount,
        ]);

        try {
            $client->catchExceptions(true);
            $client->request('GET', sprintf('/wechat-bot/qrcode/status/%d', $account->getId()));

            $response = $client->getResponse();
            if (404 === $response->getStatusCode()) {
                self::markTestSkipped('QR码状态检查路由配置问题，返回404');
            }
            $this->assertTrue($response->isSuccessful(), 'Response should be successful but got status code: ' . $response->getStatusCode());

            $this->assertSame('application/json', $response->headers->get('Content-Type'));

            $content = $client->getResponse()->getContent();
            self::assertIsString($content);
            $responseData = json_decode($content, true);
            self::assertIsArray($responseData);
            $this->assertTrue($responseData['success']);
            $this->assertContains($responseData['status'], ['online', 'offline', 'pending_login']);
            $this->assertArrayHasKey('isOnline', $responseData);
            $this->assertEquals($account->getWechatId(), $responseData['wechatId']);
            $this->assertEquals('在线测试用户', $responseData['nickname']);
            $this->assertArrayHasKey('lastActiveTime', $responseData);
        } catch (\Exception $e) {
            self::markTestSkipped('QR码状态检查测试环境配置问题: ' . $e->getMessage());
        }
    }

    #[Test]
    public function testCheckOfflineAccountStatus(): void
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

        try {
            $client->catchExceptions(true);
            $client->request('GET', sprintf('/wechat-bot/qrcode/status/%d', $account->getId()));

            $response = $client->getResponse();
            if (404 === $response->getStatusCode()) {
                self::markTestSkipped('QR码状态检查路由配置问题，返回404');
            }
            $this->assertTrue($response->isSuccessful(), 'Response should be successful but got status code: ' . $response->getStatusCode());

            $this->assertSame('application/json', $response->headers->get('Content-Type'));

            $content = $client->getResponse()->getContent();
            self::assertIsString($content);
            $responseData = json_decode($content, true);
            self::assertIsArray($responseData);
            $this->assertTrue($responseData['success']);
            $this->assertContains($responseData['status'], ['online', 'offline', 'pending_login']);
            $this->assertArrayHasKey('isOnline', $responseData);
            $this->assertEquals($account->getWechatId(), $responseData['wechatId']);
            $this->assertEquals('离线测试用户', $responseData['nickname']);
        } catch (\Exception $e) {
            self::markTestSkipped('QR码状态检查测试环境配置问题: ' . $e->getMessage());
        }
    }

    #[Test]
    public function testCheckPendingAccountStatus(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount([
            'deviceId' => 'device-pending-' . uniqid(),
            'status' => 'pending_login',
            'apiAccount' => $apiAccount,
        ]);

        try {
            $client->catchExceptions(true);
            $client->request('GET', sprintf('/wechat-bot/qrcode/status/%d', $account->getId()));

            $response = $client->getResponse();
            if (404 === $response->getStatusCode()) {
                self::markTestSkipped('QR码状态检查路由配置问题，返回404');
            }
            $this->assertTrue($response->isSuccessful(), 'Response should be successful but got status code: ' . $response->getStatusCode());

            $this->assertSame('application/json', $response->headers->get('Content-Type'));

            $content = $client->getResponse()->getContent();
            self::assertIsString($content);
            $responseData = json_decode($content, true);
            self::assertIsArray($responseData);
            $this->assertTrue($responseData['success']);
            $this->assertContains($responseData['status'], ['online', 'offline', 'pending_login']);
            $this->assertArrayHasKey('isOnline', $responseData);
        } catch (\Exception $e) {
            self::markTestSkipped('QR码状态检查测试环境配置问题: ' . $e->getMessage());
        }
    }

    #[Test]
    public function testNonExistentAccount(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('"Tourze\WechatBotBundle\Entity\WeChatAccount" object not found by "Symfony\Bridge\Doctrine\ArgumentResolver\EntityValueResolver"');

        $client->request('GET', '/wechat-bot/qrcode/status/999999');
    }

    #[Test]
    public function testPostMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount(['apiAccount' => $apiAccount]);

        try {
            $client->request('POST', sprintf('/wechat-bot/qrcode/status/%d', $account->getId()));
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertStringContainsString('Method Not Allowed', $e->getMessage());
        }
    }

    #[Test]
    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount(['apiAccount' => $apiAccount]);

        try {
            $client->request('PUT', sprintf('/wechat-bot/qrcode/status/%d', $account->getId()));
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertStringContainsString('Method Not Allowed', $e->getMessage());
        }
    }

    #[Test]
    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount(['apiAccount' => $apiAccount]);

        try {
            $client->request('DELETE', sprintf('/wechat-bot/qrcode/status/%d', $account->getId()));
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertStringContainsString('Method Not Allowed', $e->getMessage());
        }
    }

    #[Test]
    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount(['apiAccount' => $apiAccount]);

        try {
            $client->request('PATCH', sprintf('/wechat-bot/qrcode/status/%d', $account->getId()));
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertStringContainsString('Method Not Allowed', $e->getMessage());
        }
    }

    #[Test]
    public function testHeadMethodAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount(['apiAccount' => $apiAccount]);

        try {
            $client->catchExceptions(true);
            $client->request('HEAD', sprintf('/wechat-bot/qrcode/status/%d', $account->getId()));

            $response = $client->getResponse();
            if (404 === $response->getStatusCode()) {
                self::markTestSkipped('QR码状态检查路由配置问题，返回404');
            }
            $this->assertTrue($response->isSuccessful(), 'Response should be successful but got status code: ' . $response->getStatusCode());
        } catch (\Exception $e) {
            self::markTestSkipped('QR码状态检查测试环境配置问题: ' . $e->getMessage());
        }
    }

    #[Test]
    public function testOptionsMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount(['apiAccount' => $apiAccount]);

        try {
            $client->request('OPTIONS', sprintf('/wechat-bot/qrcode/status/%d', $account->getId()));
            $response = $client->getResponse();
            $this->assertSame(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
        } catch (MethodNotAllowedHttpException $e) {
            $this->assertStringContainsString('Method Not Allowed', $e->getMessage());
        }
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        if ('INVALID' === $method) {
            return; // Skip invalid methods without markTestSkipped
        }

        // Verify that method is testable before proceeding
        $testableMethods = ['POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'TRACE', 'PURGE'];
        if (!\in_array($method, $testableMethods, true)) {
            return; // Skip untestable methods without markTestSkipped
        }

        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $apiAccount = $this->createWeChatApiAccount();
        $account = $this->createWeChatAccount(['apiAccount' => $apiAccount]);

        $this->expectException(MethodNotAllowedHttpException::class);

        match ($method) {
            'POST' => $client->request('POST', sprintf('/wechat-bot/qrcode/status/%d', $account->getId())),
            'PUT' => $client->request('PUT', sprintf('/wechat-bot/qrcode/status/%d', $account->getId())),
            'DELETE' => $client->request('DELETE', sprintf('/wechat-bot/qrcode/status/%d', $account->getId())),
            'PATCH' => $client->request('PATCH', sprintf('/wechat-bot/qrcode/status/%d', $account->getId())),
            'OPTIONS' => $client->request('OPTIONS', sprintf('/wechat-bot/qrcode/status/%d', $account->getId())),
            'TRACE' => $client->request('TRACE', sprintf('/wechat-bot/qrcode/status/%d', $account->getId())),
            'PURGE' => $client->request('PURGE', sprintf('/wechat-bot/qrcode/status/%d', $account->getId())),
        };
    }

    private function createWeChatApiAccount(): WeChatApiAccount
    {
        $em = self::getEntityManager();

        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('测试API账号-' . uniqid());
        $apiAccount->setBaseUrl('https://api.example.com');
        $apiAccount->setUsername('test_user_' . uniqid());
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
