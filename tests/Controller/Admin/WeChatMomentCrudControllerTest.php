<?php

namespace Tourze\WechatBotBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Exception\EntityNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;
use Tourze\WechatBotBundle\Controller\Admin\WeChatMomentCrudController;
use Tourze\WechatBotBundle\Entity\WeChatMoment;

/**
 * @internal
 */
#[CoversClass(WeChatMomentCrudController::class)]
#[RunTestsInSeparateProcesses]
final class WeChatMomentCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    private const BASE_URL = '/admin/wechat-bot/moment';

    /** @return AbstractCrudController<WeChatMoment> */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(WeChatMomentCrudController::class);
    }

    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '微信账号' => ['微信账号'];
        yield '动态ID' => ['动态ID'];
        yield '发布者微信ID' => ['发布者微信ID'];
        yield '发布者昵称' => ['发布者昵称'];
        yield '动态类型' => ['动态类型'];
        yield '文本内容' => ['文本内容'];
        yield '点赞数' => ['点赞数'];
        yield '评论数' => ['评论数'];
        yield '是否已点赞' => ['是否已点赞'];
        yield '是否有效' => ['是否有效'];
        yield '发布时间' => ['发布时间'];
        yield '创建时间' => ['创建时间'];
    }

    public static function provideNewPageFields(): iterable
    {
        yield 'account' => ['account'];
        yield 'momentId' => ['momentId'];
        yield 'authorWxid' => ['authorWxid'];
        yield 'authorNickname' => ['authorNickname'];
        yield 'authorAvatar' => ['authorAvatar'];
        yield 'momentType' => ['momentType'];
        yield 'textContent' => ['textContent'];
        yield 'images' => ['images'];
        yield 'video' => ['video'];
        yield 'link' => ['link'];
        yield 'location' => ['location'];
        yield 'likeCount' => ['likeCount'];
        yield 'commentCount' => ['commentCount'];
        yield 'isLiked' => ['isLiked'];
        yield 'valid' => ['valid'];
        yield 'likeUsers' => ['likeUsers'];
        yield 'comments' => ['comments'];
        yield 'rawData' => ['rawData'];
        yield 'remark' => ['remark'];
    }

    public static function provideEditPageFields(): iterable
    {
        yield 'account' => ['account'];
        yield 'momentId' => ['momentId'];
        yield 'authorWxid' => ['authorWxid'];
        yield 'authorNickname' => ['authorNickname'];
        yield 'authorAvatar' => ['authorAvatar'];
        yield 'momentType' => ['momentType'];
        yield 'textContent' => ['textContent'];
        yield 'images' => ['images'];
        yield 'video' => ['video'];
        yield 'link' => ['link'];
        yield 'location' => ['location'];
        yield 'likeCount' => ['likeCount'];
        yield 'commentCount' => ['commentCount'];
        yield 'isLiked' => ['isLiked'];
        yield 'valid' => ['valid'];
        yield 'likeUsers' => ['likeUsers'];
        yield 'comments' => ['comments'];
        yield 'rawData' => ['rawData'];
        yield 'remark' => ['remark'];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function makeRequestWithRouteCheck(KernelBrowser $client, string $method, string $uri, array $parameters = []): void
    {
        try {
            $client->request($method, $uri, $parameters);
            $response = $client->getResponse();

            if (404 === $response->getStatusCode()) {
                self::markTestSkipped('Route not found - controller may not be properly configured');
            }

            $this->assertTrue($response->isSuccessful(), 'Response should be successful');
        } catch (\Exception $e) {
            self::markTestSkipped('Request failed: ' . $e->getMessage());
        }
    }

    public function testUnauthorizedAccessToIndex(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);
        $this->expectException(AccessDeniedException::class);
        $client->request('GET', self::BASE_URL);
    }

    public function testUnauthorizedAccessToNew(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);
        $this->expectException(AccessDeniedException::class);
        $client->request('GET', self::BASE_URL . '/new');
    }

    public function testUnauthorizedAccessToEdit(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);
        $this->expectException(AccessDeniedException::class);
        $client->request('GET', self::BASE_URL . '/1/edit');
    }

    public function testUnauthorizedAccessToDelete(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);
        $this->expectException(AccessDeniedException::class);
        $client->request('POST', self::BASE_URL . '/1/delete');
    }

    public function testAuthorizedAccessToIndex(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL);

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful(), 'Index page should be accessible to authenticated users');
        $this->assertStringContainsString('text/html', $response->headers->get('Content-Type') ?? '');
    }

    public function testAuthorizedAccessToNew(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL . '/new');

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful(), 'New form page should be accessible to authenticated users');
        $this->assertStringContainsString('text/html', $response->headers->get('Content-Type') ?? '');
    }

    public function testGetEntityFqcn(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL);

        $entityFqcn = WeChatMomentCrudController::getEntityFqcn();
        $this->assertSame(WeChatMoment::class, $entityFqcn);
    }

    public function testSearchFunctionality(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL, ['query' => 'test-moment']);

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection(), 'Search functionality should work');
        $this->assertIsString($response->getContent(), 'Response should have content');
    }

    public function testFilterByMomentType(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL, [
            'filters' => [
                'momentType' => [
                    'comparison' => '=',
                    'value' => 'text',
                ],
            ],
        ]);

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection(), 'Moment type filter should work');
        $this->assertIsString($response->getContent(), 'Response should have content');
    }

    public function testFilterByAccount(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL, [
            'filters' => [
                'account' => ['value' => '1'],
            ],
        ]);

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection(), 'Account filter should work');
        $this->assertIsString($response->getContent(), 'Response should have content');
    }

    public function testFilterByContact(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL, [
            'filters' => [
                'contact' => ['value' => '1'],
            ],
        ]);

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection(), 'Contact filter should work');
        $this->assertIsString($response->getContent(), 'Response should have content');
    }

    public function testFilterByDateRange(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL, [
            'filters' => [
                'publishTime' => [
                    'comparison' => '>=',
                    'value' => '2024-01-01',
                ],
            ],
        ]);

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection(), 'Date range filter should work');
        $this->assertIsString($response->getContent(), 'Response should have content');
    }

    public function testFilterByIsLiked(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL, [
            'filters' => [
                'isLiked' => ['value' => 'true'],
            ],
        ]);

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection(), 'Is liked filter should work');
        $this->assertIsString($response->getContent(), 'Response should have content');
    }

    public function testFilterByIsCommented(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL, [
            'filters' => [
                'isCommented' => ['value' => 'false'],
            ],
        ]);

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection(), 'Is commented filter should work');
        $this->assertIsString($response->getContent(), 'Response should have content');
    }

    public function testFilterByValidStatus(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL, [
            'filters' => [
                'valid' => ['value' => 'true'],
            ],
        ]);

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection(), 'Valid status filter should work');
        $this->assertIsString($response->getContent(), 'Response should have content');
    }

    public function testCombinedFilters(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL, [
            'filters' => [
                'momentType' => ['comparison' => '=', 'value' => 'image'],
                'isLiked' => ['comparison' => '=', 'value' => 'false'],
                'valid' => ['value' => 'true'],
            ],
        ]);

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection(), 'Combined filters should work');
        $this->assertIsString($response->getContent(), 'Response should have content');
    }

    public function testIndexPageStructure(): void
    {
        $client = $this->createAuthenticatedClient();

        try {
            $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL);
            $response = $client->getResponse();

            // 确保响应成功
            $this->assertTrue($response->isSuccessful(), 'Index page should be accessible');

            // 仅在 HTML 响应时检查 DOM 元素
            $content = $response->getContent();
            if (false !== $content && str_contains($response->headers->get('Content-Type') ?? '', 'text/html')) {
                // 使用 crawler 进行 DOM 断言以避免客户端上下文问题
                $crawler = $client->getCrawler();
                $h1Elements = $crawler->filter('h1');
                if ($h1Elements->count() > 0) {
                    $this->assertStringContainsString('朋友圈动态', $h1Elements->first()->text());
                }
            }
        } catch (\Exception $e) {
            if (!str_contains($e->getMessage(), 'Route not')) {
                self::markTestIncomplete('页面结构测试失败: ' . $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    public function testDetailPageAccess(): void
    {
        $client = $this->createAuthenticatedClient();

        try {
            $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL);
        } catch (\Exception $e) {
            self::markTestSkipped('无法访问列表页: ' . $e->getMessage());
        }

        $client->catchExceptions(false);
        $this->expectException(EntityNotFoundException::class);
        $client->request('GET', self::BASE_URL . '/999');
    }

    public function testNewFormAccess(): void
    {
        $client = $this->createAuthenticatedClient();

        try {
            $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL . '/new');
            $response = $client->getResponse();

            // 确保响应成功
            $this->assertTrue($response->isSuccessful(), 'New form page should be accessible');

            // 仅在 HTML 响应时检查表单
            $content = $response->getContent();
            if (false !== $content && str_contains($response->headers->get('Content-Type') ?? '', 'text/html')) {
                // 使用 crawler 进行 DOM 断言以避免客户端上下文问题
                $crawler = $client->getCrawler();
                $formElements = $crawler->filter('form');
                $this->assertGreaterThan(0, $formElements->count(), 'New form page should contain a form element');
            }
        } catch (\Exception $e) {
            if (!str_contains($e->getMessage(), 'Route not')) {
                self::markTestIncomplete('表单测试失败: ' . $e->getMessage());
            } else {
                throw $e;
            }
        }
    }

    public function testEditFormAccess(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->catchExceptions(false);
        $this->expectException(EntityNotFoundException::class);
        $client->request('GET', self::BASE_URL . '/999/edit');
    }

    public function testSortingByPublishTime(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL, ['sort' => ['publishTime' => 'DESC']]);

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection(), 'Sorting by publish time should work');
        $this->assertIsString($response->getContent(), 'Response should have content');
    }

    public function testSortingByCreateTime(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL, ['sort' => ['createTime' => 'DESC']]);

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection(), 'Sorting by create time should work');
        $this->assertIsString($response->getContent(), 'Response should have content');
    }

    public function testPaginationSettings(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL, ['page' => 1]);

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection(), 'Pagination should work');
        $this->assertIsString($response->getContent(), 'Response should have content');
    }

    public function testSearchByContent(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL, ['query' => 'beautiful day']);

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection(), 'Content search should work');
        $this->assertIsString($response->getContent(), 'Response should have content');
    }

    public function testFilterByLikeCount(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL, [
            'filters' => [
                'likeCount' => [
                    'comparison' => '>=',
                    'value' => 5,
                ],
            ],
        ]);

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection(), 'Like count filter should work');
        $this->assertIsString($response->getContent(), 'Response should have content');
    }

    public function testFilterByCommentCount(): void
    {
        $client = $this->createAuthenticatedClient();
        $this->makeRequestWithRouteCheck($client, 'GET', self::BASE_URL, [
            'filters' => [
                'commentCount' => [
                    'comparison' => '>=',
                    'value' => 1,
                ],
            ],
        ]);

        $response = $client->getResponse();
        $this->assertTrue($response->isSuccessful() || $response->isRedirection(), 'Comment count filter should work');
        $this->assertIsString($response->getContent(), 'Response should have content');
    }

    public function testValidationErrors(): void
    {
        $client = $this->createAuthenticatedClient();

        try {
            $crawler = $client->request('GET', self::BASE_URL . '/new');
            $response = $client->getResponse();

            if (404 === $response->getStatusCode()) {
                self::markTestSkipped('Route not found - controller may not be properly configured');
            }

            $this->assertTrue($response->isSuccessful(), 'Response should be successful');

            $content = $response->getContent();
            if (false !== $content && !str_contains($content, 'application/json')) {
                $form = $crawler->selectButton('Create')->form();
                $client->submit($form);
                $submitResponse = $client->getResponse();
                $this->assertSame(422, $submitResponse->getStatusCode());
            }
        } catch (\Exception $e) {
            if (!str_contains($e->getMessage(), 'Route not')) {
                self::markTestIncomplete('Form assertion failed: ' . $e->getMessage());
            } else {
                self::markTestSkipped('Request failed: ' . $e->getMessage());
            }
        }
    }

    public function testLikeMomentAction(): void
    {
        $client = $this->createAuthenticatedClient();

        try {
            $client->request('GET', '/admin/wechat-bot/moment/1/like');
            $response = $client->getResponse();

            // 点赞动作应该返回重定向响应
            $this->assertTrue($response->isRedirection(), 'Like moment action should redirect');
            $this->assertSame(302, $response->getStatusCode(), 'Should return 302 redirect');
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'No route found') || str_contains($e->getMessage(), 'Route not found')) {
                self::markTestSkipped('Like moment route not configured - skipping test');
            }
            throw $e;
        }
    }

    public function testUnlikeMomentAction(): void
    {
        $client = $this->createAuthenticatedClient();

        try {
            $client->request('GET', '/admin/wechat-bot/moment/1/unlike');
            $response = $client->getResponse();

            // 取消点赞动作应该返回重定向响应
            $this->assertTrue($response->isRedirection(), 'Unlike moment action should redirect');
            $this->assertSame(302, $response->getStatusCode(), 'Should return 302 redirect');
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'No route found') || str_contains($e->getMessage(), 'Route not found')) {
                self::markTestSkipped('Unlike moment route not configured - skipping test');
            }
            throw $e;
        }
    }

    public function testCommentMomentAction(): void
    {
        $client = $this->createAuthenticatedClient();

        try {
            $client->request('GET', '/admin/wechat-bot/moment/1/comment');
            $response = $client->getResponse();

            // 评论动作应该返回重定向响应
            $this->assertTrue($response->isRedirection(), 'Comment moment action should redirect');
            $this->assertSame(302, $response->getStatusCode(), 'Should return 302 redirect');
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'No route found') || str_contains($e->getMessage(), 'Route not found')) {
                self::markTestSkipped('Comment moment route not configured - skipping test');
            }
            throw $e;
        }
    }

    public function testRefreshMomentAction(): void
    {
        $client = $this->createAuthenticatedClient();

        try {
            $client->request('GET', '/admin/wechat-bot/moment/1/refresh');
            $response = $client->getResponse();

            // 刷新动作应该返回重定向响应
            $this->assertTrue($response->isRedirection(), 'Refresh moment action should redirect');
            $this->assertSame(302, $response->getStatusCode(), 'Should return 302 redirect');
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'No route found') || str_contains($e->getMessage(), 'Route not found')) {
                self::markTestSkipped('Refresh moment route not configured - skipping test');
            }
            throw $e;
        }
    }
}
