<?php

declare(strict_types=1);

namespace Tourze\WechatBotBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\TestDox;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;
use Tourze\WechatBotBundle\Entity\WeChatMoment;
use Tourze\WechatBotBundle\Repository\WeChatMomentRepository;

/**
 * 微信朋友圈仓储测试
 *
 * 测试微信朋友圈数据访问层的各种查询方法：
 * - 基础查询方法
 * - 按账号过滤查询
 * - 按作者过滤查询
 * - 按类型过滤查询
 * - 时间范围查询
 * - 搜索查询
 * - 统计查询
 *
 * @template-extends AbstractRepositoryTestCase<WeChatMoment>
 * @internal
 */
#[RunTestsInSeparateProcesses]
#[CoversClass(WeChatMomentRepository::class)]
final class WeChatMomentRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 此测试类不需要特殊的初始化逻辑
    }

    protected function createNewEntity(): object
    {
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('Test API Account ' . uniqid());
        $apiAccount->setBaseUrl('http://localhost:8080');
        $apiAccount->setUsername('test_user_' . uniqid());
        $apiAccount->setPassword('test_password');
        $apiAccount->setValid(true);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device-' . uniqid());
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $entity = new WeChatMoment();
        $entity->setAccount($account);
        $entity->setMomentId('test-moment-' . uniqid());
        $entity->setAuthorWxid('author-' . uniqid());
        $entity->setAuthorNickname('Test Author');
        $entity->setMomentType('text');
        $entity->setTextContent('Test moment content');
        $entity->setPublishTime(new \DateTimeImmutable());
        $entity->setValid(true);

        return $entity;
    }

    protected function getRepository(): WeChatMomentRepository
    {
        return self::getService(WeChatMomentRepository::class);
    }

    private function createApiAccount(): WeChatApiAccount
    {
        $apiAccount = new WeChatApiAccount();
        $apiAccount->setName('Test API Account ' . uniqid());
        $apiAccount->setBaseUrl('http://localhost:8080');
        $apiAccount->setUsername('test_user_' . uniqid());
        $apiAccount->setPassword('test_password');
        $apiAccount->setValid(true);

        return $apiAccount;
    }

    #[TestDox('按账号查找朋友圈动态')]
    public function testFindByAccount(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $moment1 = new WeChatMoment();
        $moment1->setAccount($account);
        $moment1->setMomentId('moment-1');
        $moment1->setAuthorWxid('author-1');
        $moment1->setAuthorNickname('Author 1');
        $moment1->setTextContent('Moment 1 Content');
        $moment1->setMomentType('text');
        $moment1->setPublishTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $moment1->setValid(true);

        $moment2 = new WeChatMoment();
        $moment2->setAccount($account);
        $moment2->setMomentId('moment-2');
        $moment2->setAuthorWxid('author-2');
        $moment2->setAuthorNickname('Author 2');
        $moment2->setTextContent('Moment 2 Content');
        $moment2->setMomentType('text');
        $moment2->setPublishTime(new \DateTimeImmutable('2023-01-01 11:00:00'));
        $moment2->setValid(true);

        $invalidMoment = new WeChatMoment();
        $invalidMoment->setAccount($account);
        $invalidMoment->setMomentId('invalid-moment');
        $invalidMoment->setAuthorWxid('author-3');
        $invalidMoment->setAuthorNickname('Author 3');
        $invalidMoment->setTextContent('Invalid Moment Content');
        $invalidMoment->setMomentType('text');
        $invalidMoment->setPublishTime(new \DateTimeImmutable('2023-01-01 12:00:00'));
        $invalidMoment->setValid(false);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($moment1);
        self::getEntityManager()->persist($moment2);
        self::getEntityManager()->persist($invalidMoment);
        self::getEntityManager()->flush();

        $moments = $this->getRepository()->findByAccount($account, 10, 0);

        $this->assertCount(2, $moments);
        // 验证按publishTime DESC排序
        $this->assertArrayHasKey(0, $moments);
        $this->assertArrayHasKey(1, $moments);
        $this->assertInstanceOf(WeChatMoment::class, $moments[0]);
        $this->assertInstanceOf(WeChatMoment::class, $moments[1]);
        $this->assertSame('moment-2', $moments[0]->getMomentId());
        $this->assertSame('moment-1', $moments[1]->getMomentId());
    }

    #[TestDox('按账号查找朋友圈动态时应用分页')]
    public function testFindByAccountWithPagination(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建3个朋友圈动态
        for ($i = 0; $i < 3; ++$i) {
            $moment = new WeChatMoment();
            $moment->setAccount($account);
            $moment->setMomentId('moment-' . $i);
            $moment->setAuthorWxid('author-' . $i);
            $moment->setAuthorNickname('Author ' . $i);
            $moment->setTextContent('Moment ' . $i . ' Content');
            $moment->setMomentType('text');
            $moment->setPublishTime(new \DateTimeImmutable('2023-01-01 ' . (10 + $i) . ':00:00'));
            $moment->setValid(true);
            self::getEntityManager()->persist($moment);
        }

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        // 测试分页：limit=2, offset=0
        $firstPage = $this->getRepository()->findByAccount($account, 2, 0);
        $this->assertCount(2, $firstPage);
        $this->assertArrayHasKey(0, $firstPage);
        $this->assertArrayHasKey(1, $firstPage);
        $this->assertInstanceOf(WeChatMoment::class, $firstPage[0]);
        $this->assertInstanceOf(WeChatMoment::class, $firstPage[1]);
        $this->assertSame('moment-2', $firstPage[0]->getMomentId());
        $this->assertSame('moment-1', $firstPage[1]->getMomentId());

        // 测试分页：limit=2, offset=2
        $secondPage = $this->getRepository()->findByAccount($account, 2, 2);
        $this->assertCount(1, $secondPage);
        $this->assertArrayHasKey(0, $secondPage);
        $this->assertInstanceOf(WeChatMoment::class, $secondPage[0]);
        $this->assertSame('moment-0', $secondPage[0]->getMomentId());
    }

    #[TestDox('按作者微信ID查找朋友圈动态')]
    public function testFindByAuthorWxid(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $moment1 = new WeChatMoment();
        $moment1->setAccount($account);
        $moment1->setMomentId('moment-1');
        $moment1->setAuthorWxid('target-author');
        $moment1->setAuthorNickname('Target Author');
        $moment1->setTextContent('Moment 1 Content');
        $moment1->setMomentType('text');
        $moment1->setPublishTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $moment1->setValid(true);

        $moment2 = new WeChatMoment();
        $moment2->setAccount($account);
        $moment2->setMomentId('moment-2');
        $moment2->setAuthorWxid('target-author');
        $moment2->setAuthorNickname('Target Author');
        $moment2->setTextContent('Moment 2 Content');
        $moment2->setMomentType('image');
        $moment2->setPublishTime(new \DateTimeImmutable('2023-01-01 11:00:00'));
        $moment2->setValid(true);

        $otherMoment = new WeChatMoment();
        $otherMoment->setAccount($account);
        $otherMoment->setMomentId('other-moment');
        $otherMoment->setAuthorWxid('other-author');
        $otherMoment->setAuthorNickname('Other Author');
        $otherMoment->setTextContent('Other Moment Content');
        $otherMoment->setMomentType('text');
        $otherMoment->setPublishTime(new \DateTimeImmutable('2023-01-01 12:00:00'));
        $otherMoment->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($moment1);
        self::getEntityManager()->persist($moment2);
        self::getEntityManager()->persist($otherMoment);
        self::getEntityManager()->flush();

        $moments = $this->getRepository()->findByAuthorWxid('target-author', 10, 0);

        $this->assertCount(2, $moments);
        // 验证按publishTime DESC排序
        $this->assertArrayHasKey(0, $moments);
        $this->assertArrayHasKey(1, $moments);
        $this->assertInstanceOf(WeChatMoment::class, $moments[0]);
        $this->assertInstanceOf(WeChatMoment::class, $moments[1]);
        $this->assertSame('moment-2', $moments[0]->getMomentId());
        $this->assertSame('moment-1', $moments[1]->getMomentId());
        $this->assertSame('target-author', $moments[0]->getAuthorWxid());
        $this->assertSame('target-author', $moments[1]->getAuthorWxid());
    }

    #[TestDox('按动态类型查找朋友圈动态')]
    public function testFindByMomentType(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $textMoment = new WeChatMoment();
        $textMoment->setAccount($account);
        $textMoment->setMomentId('text-moment');
        $textMoment->setAuthorWxid('author-1');
        $textMoment->setAuthorNickname('Author 1');
        $textMoment->setTextContent('Text Moment Content');
        $textMoment->setMomentType('text');
        $textMoment->setPublishTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $textMoment->setValid(true);

        $imageMoment = new WeChatMoment();
        $imageMoment->setAccount($account);
        $imageMoment->setMomentId('image-moment');
        $imageMoment->setAuthorWxid('author-2');
        $imageMoment->setAuthorNickname('Author 2');
        $imageMoment->setTextContent('Image Moment Content');
        $imageMoment->setMomentType('image');
        $imageMoment->setPublishTime(new \DateTimeImmutable('2023-01-01 11:00:00'));
        $imageMoment->setValid(true);

        $linkMoment = new WeChatMoment();
        $linkMoment->setAccount($account);
        $linkMoment->setMomentId('link-moment');
        $linkMoment->setAuthorWxid('author-3');
        $linkMoment->setAuthorNickname('Author 3');
        $linkMoment->setTextContent('Link Moment Content');
        $linkMoment->setMomentType('link');
        $linkMoment->setPublishTime(new \DateTimeImmutable('2023-01-01 12:00:00'));
        $linkMoment->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($textMoment);
        self::getEntityManager()->persist($imageMoment);
        self::getEntityManager()->persist($linkMoment);
        self::getEntityManager()->flush();

        $imageMoments = $this->getRepository()->findByMomentType('image', 10, 0);

        // 验证包含我们创建的图片朋友圈（可能还有Fixtures中的其他图片朋友圈）
        $this->assertGreaterThanOrEqual(1, count($imageMoments));

        // 查找我们创建的特定朋友圈
        $testImageMoment = null;
        foreach ($imageMoments as $moment) {
            $this->assertInstanceOf(WeChatMoment::class, $moment);
            if ('image-moment' === $moment->getMomentId()) {
                $testImageMoment = $moment;
                break;
            }
        }

        $this->assertNotNull($testImageMoment, '应该能找到测试创建的图片朋友圈');
        $this->assertInstanceOf(WeChatMoment::class, $testImageMoment);
        $this->assertSame('image-moment', $testImageMoment->getMomentId());
        $this->assertSame('image', $testImageMoment->getMomentType());
    }

    #[TestDox('根据朋友圈ID查找动态')]
    public function testFindByMomentId(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $moment = new WeChatMoment();
        $moment->setAccount($account);
        $moment->setMomentId('test-moment-123');
        $moment->setAuthorWxid('author-1');
        $moment->setAuthorNickname('Author 1');
        $moment->setTextContent('Test Moment Content');
        $moment->setMomentType('text');
        $moment->setPublishTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $moment->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($moment);
        self::getEntityManager()->flush();

        $foundMoment = $this->getRepository()->findByMomentId('test-moment-123');

        $this->assertInstanceOf(WeChatMoment::class, $foundMoment);
        $this->assertSame('test-moment-123', $foundMoment->getMomentId());
        $this->assertSame('Test Moment Content', $foundMoment->getTextContent());
    }

    #[TestDox('根据朋友圈ID查找不存在的动态返回null')]
    public function testFindByMomentIdNotFound(): void
    {
        $foundMoment = $this->getRepository()->findByMomentId('non-existent-moment');

        $this->assertNull($foundMoment);
    }

    #[TestDox('统计朋友圈动态数量')]
    public function testCountMoments(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        // 记录添加新数据前的计数
        $initialTotalCount = $this->getRepository()->countMoments();
        $initialAccountCount = $this->getRepository()->countMoments($account);

        // 创建有效动态
        for ($i = 0; $i < 3; ++$i) {
            $moment = new WeChatMoment();
            $moment->setAccount($account);
            $moment->setMomentId('moment-' . $i);
            $moment->setAuthorWxid('author-' . $i);
            $moment->setAuthorNickname('Author ' . $i);
            $moment->setTextContent('Moment ' . $i . ' Content');
            $moment->setMomentType('text');
            $moment->setPublishTime(new \DateTimeImmutable());
            $moment->setValid(true);
            self::getEntityManager()->persist($moment);
        }

        // 创建无效动态
        $invalidMoment = new WeChatMoment();
        $invalidMoment->setAccount($account);
        $invalidMoment->setMomentId('invalid-moment');
        $invalidMoment->setAuthorWxid('author-invalid');
        $invalidMoment->setAuthorNickname('Invalid Author');
        $invalidMoment->setTextContent('Invalid Moment Content');
        $invalidMoment->setMomentType('text');
        $invalidMoment->setPublishTime(new \DateTimeImmutable());
        $invalidMoment->setValid(false);

        self::getEntityManager()->persist($invalidMoment);
        self::getEntityManager()->flush();

        $finalTotalCount = $this->getRepository()->countMoments();
        $finalAccountCount = $this->getRepository()->countMoments($account);

        // 验证总数增加了3个（只统计有效的朋友圈）
        $this->assertSame($initialTotalCount + 3, $finalTotalCount);
        // 验证当前测试账号的朋友圈数量为3个
        $this->assertSame($initialAccountCount + 3, $finalAccountCount);
    }

    #[TestDox('根据时间范围查找朋友圈动态')]
    public function testFindByTimeRange(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $moment1 = new WeChatMoment();
        $moment1->setAccount($account);
        $moment1->setMomentId('moment-1');
        $moment1->setAuthorWxid('author-1');
        $moment1->setAuthorNickname('Author 1');
        $moment1->setTextContent('Moment 1 Content');
        $moment1->setMomentType('text');
        $moment1->setPublishTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $moment1->setValid(true);

        $moment2 = new WeChatMoment();
        $moment2->setAccount($account);
        $moment2->setMomentId('moment-2');
        $moment2->setAuthorWxid('author-2');
        $moment2->setAuthorNickname('Author 2');
        $moment2->setTextContent('Moment 2 Content');
        $moment2->setMomentType('text');
        $moment2->setPublishTime(new \DateTimeImmutable('2023-01-01 12:00:00'));
        $moment2->setValid(true);

        $moment3 = new WeChatMoment();
        $moment3->setAccount($account);
        $moment3->setMomentId('moment-3');
        $moment3->setAuthorWxid('author-3');
        $moment3->setAuthorNickname('Author 3');
        $moment3->setTextContent('Moment 3 Content');
        $moment3->setMomentType('text');
        $moment3->setPublishTime(new \DateTimeImmutable('2023-01-02 10:00:00'));
        $moment3->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($moment1);
        self::getEntityManager()->persist($moment2);
        self::getEntityManager()->persist($moment3);
        self::getEntityManager()->flush();

        $startTime = new \DateTimeImmutable('2023-01-01 09:00:00');
        $endTime = new \DateTimeImmutable('2023-01-01 15:00:00');

        $moments = $this->getRepository()->findByTimeRange($startTime, $endTime);

        $this->assertCount(2, $moments);
        /** @var WeChatMoment[] $moments */
        $momentIds = array_map(static fn (WeChatMoment $moment): ?string => $moment->getMomentId(), $moments);
        $this->assertContains('moment-1', $momentIds);
        $this->assertContains('moment-2', $momentIds);
        $this->assertNotContains('moment-3', $momentIds);
    }

    #[TestDox('根据时间范围和账号查找朋友圈动态')]
    public function testFindByTimeRangeWithAccount(): void
    {
        $apiAccount1 = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount1);
        $apiAccount2 = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount2);

        $account1 = new WeChatAccount();
        $account1->setApiAccount($apiAccount1);
        $account1->setDeviceId('test-device-1');
        $account1->setNickname('Test Account 1');
        $account1->setStatus('online');
        $account1->setValid(true);

        $account2 = new WeChatAccount();
        $account2->setApiAccount($apiAccount2);
        $account2->setDeviceId('test-device-2');
        $account2->setNickname('Test Account 2');
        $account2->setStatus('online');
        $account2->setValid(true);

        $moment1 = new WeChatMoment();
        $moment1->setAccount($account1);
        $moment1->setMomentId('moment-1');
        $moment1->setAuthorWxid('author-1');
        $moment1->setAuthorNickname('Author 1');
        $moment1->setTextContent('Moment 1 Content');
        $moment1->setMomentType('text');
        $moment1->setPublishTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $moment1->setValid(true);

        $moment2 = new WeChatMoment();
        $moment2->setAccount($account2);
        $moment2->setMomentId('moment-2');
        $moment2->setAuthorWxid('author-2');
        $moment2->setAuthorNickname('Author 2');
        $moment2->setTextContent('Moment 2 Content');
        $moment2->setMomentType('text');
        $moment2->setPublishTime(new \DateTimeImmutable('2023-01-01 11:00:00'));
        $moment2->setValid(true);

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->persist($moment1);
        self::getEntityManager()->persist($moment2);
        self::getEntityManager()->flush();

        $startTime = new \DateTimeImmutable('2023-01-01 09:00:00');
        $endTime = new \DateTimeImmutable('2023-01-01 15:00:00');

        $account1Moments = $this->getRepository()->findByTimeRange($startTime, $endTime, $account1);

        $this->assertCount(1, $account1Moments);
        $this->assertArrayHasKey(0, $account1Moments);
        $this->assertInstanceOf(WeChatMoment::class, $account1Moments[0]);
        $this->assertSame('moment-1', $account1Moments[0]->getMomentId());
        $this->assertSame($account1, $account1Moments[0]->getAccount());
    }

    #[TestDox('查找最新的朋友圈动态')]
    public function testFindLatest(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建多个动态（使用未来时间确保它们是最新的）
        $baseTime = new \DateTimeImmutable('+1 hour');
        for ($i = 0; $i < 5; ++$i) {
            $moment = new WeChatMoment();
            $moment->setAccount($account);
            $moment->setMomentId('moment-' . $i);
            $moment->setAuthorWxid('author-' . $i);
            $moment->setAuthorNickname('Author ' . $i);
            $moment->setTextContent('Moment ' . $i . ' Content');
            $moment->setMomentType('text');
            $moment->setPublishTime($baseTime->modify('+' . $i . ' minutes'));
            $moment->setValid(true);
            self::getEntityManager()->persist($moment);
        }

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $latestMoments = $this->getRepository()->findLatest(3);

        $this->assertGreaterThanOrEqual(3, count($latestMoments));

        // 查找我们创建的最新朋友圈（在前3个中）
        $testMomentIds = ['moment-4', 'moment-3', 'moment-2'];
        $foundTestMoments = [];

        foreach ($latestMoments as $moment) {
            $this->assertInstanceOf(WeChatMoment::class, $moment);
            if (in_array($moment->getMomentId(), $testMomentIds, true)) {
                $foundTestMoments[] = $moment->getMomentId();
            }
        }

        // 验证我们能找到这些测试朋友圈（由于fixtures数据，可能不在最前面）
        $this->assertContains('moment-4', $foundTestMoments, '应该能在最新朋友圈中找到moment-4');
    }

    #[TestDox('按内容搜索朋友圈动态')]
    public function testSearchByContent(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $moment1 = new WeChatMoment();
        $moment1->setAccount($account);
        $moment1->setMomentId('moment-1');
        $moment1->setAuthorWxid('author-1');
        $moment1->setAuthorNickname('Author 1');
        $moment1->setTextContent('Hello World from Beijing');
        $moment1->setMomentType('text');
        $moment1->setPublishTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $moment1->setValid(true);

        $moment2 = new WeChatMoment();
        $moment2->setAccount($account);
        $moment2->setMomentId('moment-2');
        $moment2->setAuthorWxid('author-2');
        $moment2->setAuthorNickname('Author 2');
        $moment2->setTextContent('Good morning Beijing');
        $moment2->setMomentType('text');
        $moment2->setPublishTime(new \DateTimeImmutable('2023-01-01 11:00:00'));
        $moment2->setValid(true);

        $moment3 = new WeChatMoment();
        $moment3->setAccount($account);
        $moment3->setMomentId('moment-3');
        $moment3->setAuthorWxid('author-3');
        $moment3->setAuthorNickname('Author 3');
        $moment3->setTextContent('Hello Shanghai');
        $moment3->setMomentType('text');
        $moment3->setPublishTime(new \DateTimeImmutable('2023-01-01 12:00:00'));
        $moment3->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($moment1);
        self::getEntityManager()->persist($moment2);
        self::getEntityManager()->persist($moment3);
        self::getEntityManager()->flush();

        $searchResults = $this->getRepository()->searchByContent('Beijing', 10, 0);

        $this->assertCount(2, $searchResults);
        /** @var WeChatMoment[] $searchResults */
        $momentIds = array_map(static fn (WeChatMoment $moment): ?string => $moment->getMomentId(), $searchResults);
        $this->assertContains('moment-1', $momentIds);
        $this->assertContains('moment-2', $momentIds);
        $this->assertNotContains('moment-3', $momentIds);
    }

    #[TestDox('按内容搜索朋友圈动态时只返回有效动态')]
    public function testSearchByContentOnlyValidMoments(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $validMoment = new WeChatMoment();
        $validMoment->setAccount($account);
        $validMoment->setMomentId('valid-moment');
        $validMoment->setAuthorWxid('author-1');
        $validMoment->setAuthorNickname('Author 1');
        $validMoment->setTextContent('Valid Beijing moment');
        $validMoment->setMomentType('text');
        $validMoment->setPublishTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $validMoment->setValid(true);

        $invalidMoment = new WeChatMoment();
        $invalidMoment->setAccount($account);
        $invalidMoment->setMomentId('invalid-moment');
        $invalidMoment->setAuthorWxid('author-2');
        $invalidMoment->setAuthorNickname('Author 2');
        $invalidMoment->setTextContent('Invalid Beijing moment');
        $invalidMoment->setMomentType('text');
        $invalidMoment->setPublishTime(new \DateTimeImmutable('2023-01-01 11:00:00'));
        $invalidMoment->setValid(false);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($validMoment);
        self::getEntityManager()->persist($invalidMoment);
        self::getEntityManager()->flush();

        $searchResults = $this->getRepository()->searchByContent('Beijing', 10, 0);

        $this->assertCount(1, $searchResults);
        $this->assertArrayHasKey(0, $searchResults);
        $this->assertInstanceOf(WeChatMoment::class, $searchResults[0]);
        $this->assertSame('valid-moment', $searchResults[0]->getMomentId());
    }

    #[TestDox('空数据库时的查询方法')]
    public function testEmptyDatabase(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        // 测试新账号没有朋友圈数据（不包括fixtures数据）
        $this->assertEmpty($this->getRepository()->findByAccount($account));
        $this->assertEmpty($this->getRepository()->findByAuthorWxid('non-existent-author-123'));
        $this->assertNull($this->getRepository()->findByMomentId('non-existent-moment-123'));
        $this->assertSame(0, $this->getRepository()->countMoments($account));
        $this->assertEmpty($this->getRepository()->findByTimeRange(new \DateTimeImmutable('1990-01-01'), new \DateTimeImmutable('1990-01-02')));
        $this->assertEmpty($this->getRepository()->searchByContent('non-existent-keyword-123'));

        // 全局查询可能有fixtures数据，但至少不应该为负数
        $this->assertGreaterThanOrEqual(0, $this->getRepository()->countMoments());
        $this->assertIsArray($this->getRepository()->findByMomentType('text'));
        $this->assertIsArray($this->getRepository()->findLatest());
    }

    // ================== 基础 Doctrine 方法测试 ==================

    #[TestDox('save方法应持久化新实体')]
    public function testSave(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $moment = new WeChatMoment();
        $moment->setAccount($account);
        $moment->setMomentId('new-moment');
        $moment->setAuthorWxid('new-author');
        $moment->setAuthorNickname('New Author');
        $moment->setTextContent('New Moment');
        $moment->setMomentType('text');
        $moment->setPublishTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $moment->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $this->getRepository()->save($moment, true);

        $foundMoment = $this->getRepository()->findOneBy(['momentId' => 'new-moment']);
        $this->assertInstanceOf(WeChatMoment::class, $foundMoment);
        $this->assertSame('new-moment', $foundMoment->getMomentId());
    }

    #[TestDox('remove方法应删除实体')]
    public function testRemove(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $moment = new WeChatMoment();
        $moment->setAccount($account);
        $moment->setMomentId('to-delete');
        $moment->setAuthorWxid('delete-author');
        $moment->setAuthorNickname('Delete Author');
        $moment->setTextContent('Delete Moment');
        $moment->setMomentType('text');
        $moment->setPublishTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $moment->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($moment);
        self::getEntityManager()->flush();

        $this->getRepository()->remove($moment, true);

        $foundMoment = $this->getRepository()->findOneBy(['momentId' => 'to-delete']);
        $this->assertNull($foundMoment);
    }

    // ================== 健壮性测试 ==================

    // ================== 关联查询测试 ==================

    #[TestDox('查询包含关联实体')]
    public function testQueryWithAssociations(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account1 = new WeChatAccount();
        $account1->setApiAccount($apiAccount);
        $account1->setDeviceId('test-device-1');
        $account1->setNickname('Test Account 1');
        $account1->setStatus('online');
        $account1->setValid(true);

        $account2 = new WeChatAccount();
        $account2->setApiAccount($apiAccount);
        $account2->setDeviceId('test-device-2');
        $account2->setNickname('Test Account 2');
        $account2->setStatus('online');
        $account2->setValid(true);

        $moment1 = new WeChatMoment();
        $moment1->setAccount($account1);
        $moment1->setMomentId('moment-1');
        $moment1->setAuthorWxid('author-1');
        $moment1->setAuthorNickname('Author 1');
        $moment1->setTextContent('Moment 1');
        $moment1->setMomentType('text');
        $moment1->setPublishTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $moment1->setValid(true);

        $moment2 = new WeChatMoment();
        $moment2->setAccount($account2);
        $moment2->setMomentId('moment-2');
        $moment2->setAuthorWxid('author-2');
        $moment2->setAuthorNickname('Author 2');
        $moment2->setTextContent('Moment 2');
        $moment2->setMomentType('text');
        $moment2->setPublishTime(new \DateTimeImmutable('2023-01-01 11:00:00'));
        $moment2->setValid(true);

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->persist($moment1);
        self::getEntityManager()->persist($moment2);
        self::getEntityManager()->flush();

        // 测试按关联实体查询
        $momentsForAccount1 = $this->getRepository()->findBy(['account' => $account1]);
        $momentsForAccount2 = $this->getRepository()->findBy(['account' => $account2]);

        $this->assertCount(1, $momentsForAccount1);
        $this->assertCount(1, $momentsForAccount2);
        $this->assertArrayHasKey(0, $momentsForAccount1);
        $this->assertArrayHasKey(0, $momentsForAccount2);
        $this->assertInstanceOf(WeChatMoment::class, $momentsForAccount1[0]);
        $this->assertInstanceOf(WeChatMoment::class, $momentsForAccount2[0]);
        $this->assertSame($account1, $momentsForAccount1[0]->getAccount());
        $this->assertSame($account2, $momentsForAccount2[0]->getAccount());
    }

    #[TestDox('统计关联查询')]
    public function testCountWithAssociations(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account1 = new WeChatAccount();
        $account1->setApiAccount($apiAccount);
        $account1->setDeviceId('test-device-1');
        $account1->setNickname('Test Account 1');
        $account1->setStatus('online');
        $account1->setValid(true);

        $account2 = new WeChatAccount();
        $account2->setApiAccount($apiAccount);
        $account2->setDeviceId('test-device-2');
        $account2->setNickname('Test Account 2');
        $account2->setStatus('online');
        $account2->setValid(true);

        for ($i = 1; $i <= 3; ++$i) {
            $moment = new WeChatMoment();
            $moment->setAccount($account1);
            $moment->setMomentId('moment-acc1-' . $i);
            $moment->setAuthorWxid('author-' . $i);
            $moment->setAuthorNickname('Author ' . $i);
            $moment->setTextContent('Moment ' . $i);
            $moment->setMomentType('text');
            $moment->setPublishTime(new \DateTimeImmutable('2023-01-01 ' . (9 + $i) . ':00:00'));
            $moment->setValid(true);
            self::getEntityManager()->persist($moment);
        }

        for ($i = 1; $i <= 2; ++$i) {
            $moment = new WeChatMoment();
            $moment->setAccount($account2);
            $moment->setMomentId('moment-acc2-' . $i);
            $moment->setAuthorWxid('author-acc2-' . $i);
            $moment->setAuthorNickname('Author Acc2 ' . $i);
            $moment->setTextContent('Moment Acc2 ' . $i);
            $moment->setMomentType('text');
            $moment->setPublishTime(new \DateTimeImmutable('2023-01-01 ' . (14 + $i) . ':00:00'));
            $moment->setValid(true);
            self::getEntityManager()->persist($moment);
        }

        self::getEntityManager()->persist($account1);
        self::getEntityManager()->persist($account2);
        self::getEntityManager()->flush();

        $account1Count = $this->getRepository()->count(['account' => $account1]);
        $account2Count = $this->getRepository()->count(['account' => $account2]);

        $this->assertSame(3, $account1Count);
        $this->assertSame(2, $account2Count);
    }

    // ================== NULL 查询测试 ==================

    #[TestDox('查询可空字段为NULL的记录')]
    public function testFindByNullableFields(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $momentWithLocation = new WeChatMoment();
        $momentWithLocation->setAccount($account);
        $momentWithLocation->setMomentId('moment-with-location');
        $momentWithLocation->setAuthorWxid('author-1');
        $momentWithLocation->setAuthorNickname('Author 1');
        $momentWithLocation->setTextContent('Moment with location');
        $momentWithLocation->setLocation('Beijing, China');
        $momentWithLocation->setMomentType('text');
        $momentWithLocation->setPublishTime(new \DateTimeImmutable('2023-01-01 10:00:00'));
        $momentWithLocation->setValid(true);

        $momentWithoutLocation = new WeChatMoment();
        $momentWithoutLocation->setAccount($account);
        $momentWithoutLocation->setMomentId('moment-without-location');
        $momentWithoutLocation->setAuthorWxid('author-2');
        $momentWithoutLocation->setAuthorNickname('Author 2');
        $momentWithoutLocation->setTextContent('Moment without location');
        $momentWithoutLocation->setLocation(null);
        $momentWithoutLocation->setMomentType('text');
        $momentWithoutLocation->setPublishTime(new \DateTimeImmutable('2023-01-01 11:00:00'));
        $momentWithoutLocation->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($momentWithLocation);
        self::getEntityManager()->persist($momentWithoutLocation);
        self::getEntityManager()->flush();

        // 查询位置信息为NULL的动态（限制在当前测试创建的账号下）
        $momentsWithoutLocation = $this->getRepository()->findBy(['location' => null, 'account' => $account]);

        $this->assertCount(1, $momentsWithoutLocation);
        $this->assertArrayHasKey(0, $momentsWithoutLocation);
        $this->assertInstanceOf(WeChatMoment::class, $momentsWithoutLocation[0]);
        $this->assertSame('moment-without-location', $momentsWithoutLocation[0]->getMomentId());
        $this->assertNull($momentsWithoutLocation[0]->getLocation());
    }

    #[TestDox('统计可空字段为NULL的记录数量')]
    public function testCountByNullableFields(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建有媒体文件的动态
        for ($i = 1; $i <= 2; ++$i) {
            $moment = new WeChatMoment();
            $moment->setAccount($account);
            $moment->setMomentId('with-media-' . $i);
            $moment->setAuthorWxid('author-' . $i);
            $moment->setAuthorNickname('Author ' . $i);
            $moment->setTextContent('With Media ' . $i);
            $moment->setImages(['image' . $i . '.jpg']);
            $moment->setMomentType('image');
            $moment->setPublishTime(new \DateTimeImmutable('2023-01-01 ' . (9 + $i) . ':00:00'));
            $moment->setValid(true);
            self::getEntityManager()->persist($moment);
        }

        // 创建没有媒体文件的动态
        for ($i = 1; $i <= 3; ++$i) {
            $moment = new WeChatMoment();
            $moment->setAccount($account);
            $moment->setMomentId('without-media-' . $i);
            $moment->setAuthorWxid('author-no-media-' . $i);
            $moment->setAuthorNickname('Author No Media ' . $i);
            $moment->setTextContent('Without Media ' . $i);
            $moment->setImages(null);
            $moment->setMomentType('text');
            $moment->setPublishTime(new \DateTimeImmutable('2023-01-01 ' . (11 + $i) . ':00:00'));
            $moment->setValid(true);
            self::getEntityManager()->persist($moment);
        }

        self::getEntityManager()->persist($account);
        self::getEntityManager()->flush();

        $countWithoutMedia = $this->getRepository()->count(['images' => null, 'account' => $account]);

        $this->assertSame(3, $countWithoutMedia);
    }

    // ================== 完整的可空字段测试 ==================

    #[TestDox('查询所有可空字段为NULL的记录')]
    public function testFindByAllNullableFields(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建有作者昵称的动态
        $momentWithNickname = new WeChatMoment();
        $momentWithNickname->setAccount($account);
        $momentWithNickname->setMomentId('with-nickname');
        $momentWithNickname->setAuthorWxid('author1');
        $momentWithNickname->setAuthorNickname('Test Author');
        $momentWithNickname->setMomentType('text');
        $momentWithNickname->setPublishTime(new \DateTimeImmutable());
        $momentWithNickname->setValid(true);

        // 创建没有作者昵称的动态
        $momentWithoutNickname = new WeChatMoment();
        $momentWithoutNickname->setAccount($account);
        $momentWithoutNickname->setMomentId('without-nickname');
        $momentWithoutNickname->setAuthorWxid('author2');
        $momentWithoutNickname->setAuthorNickname(null);
        $momentWithoutNickname->setMomentType('text');
        $momentWithoutNickname->setPublishTime(new \DateTimeImmutable());
        $momentWithoutNickname->setValid(true);

        // 创建有作者头像的动态
        $momentWithAvatar = new WeChatMoment();
        $momentWithAvatar->setAccount($account);
        $momentWithAvatar->setMomentId('with-avatar');
        $momentWithAvatar->setAuthorWxid('author3');
        $momentWithAvatar->setAuthorAvatar('https://example.com/avatar.jpg');
        $momentWithAvatar->setMomentType('text');
        $momentWithAvatar->setPublishTime(new \DateTimeImmutable());
        $momentWithAvatar->setValid(true);

        // 创建有文本内容的动态
        $momentWithContent = new WeChatMoment();
        $momentWithContent->setAccount($account);
        $momentWithContent->setMomentId('with-content');
        $momentWithContent->setAuthorWxid('author4');
        $momentWithContent->setTextContent('Test content');
        $momentWithContent->setMomentType('text');
        $momentWithContent->setPublishTime(new \DateTimeImmutable());
        $momentWithContent->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($momentWithNickname);
        self::getEntityManager()->persist($momentWithoutNickname);
        self::getEntityManager()->persist($momentWithAvatar);
        self::getEntityManager()->persist($momentWithContent);
        self::getEntityManager()->flush();

        // 测试查询作者昵称为NULL的动态（限制在当前测试账号下）
        $momentsWithoutNickname = $this->getRepository()->findBy(['authorNickname' => null, 'account' => $account]);
        $this->assertCount(3, $momentsWithoutNickname); // without-nickname, with-avatar, with-content

        // 测试查询作者头像为NULL的动态（限制在当前测试账号下）
        $momentsWithoutAvatar = $this->getRepository()->findBy(['authorAvatar' => null, 'account' => $account]);
        $this->assertCount(3, $momentsWithoutAvatar); // with-nickname, without-nickname, with-content

        // 测试查询文本内容为NULL的动态（限制在当前测试账号下）
        $momentsWithoutContent = $this->getRepository()->findBy(['textContent' => null, 'account' => $account]);
        $this->assertCount(3, $momentsWithoutContent); // with-nickname, without-nickname, with-avatar

        // 测试查询图片为NULL的动态（限制在当前测试账号下）
        $momentsWithoutImages = $this->getRepository()->findBy(['images' => null, 'account' => $account]);
        $this->assertCount(4, $momentsWithoutImages);

        // 测试查询视频为NULL的动态（限制在当前测试账号下）
        $momentsWithoutVideo = $this->getRepository()->findBy(['video' => null, 'account' => $account]);
        $this->assertCount(4, $momentsWithoutVideo);

        // 测试查询链接为NULL的动态（限制在当前测试账号下）
        $momentsWithoutLink = $this->getRepository()->findBy(['link' => null, 'account' => $account]);
        $this->assertCount(4, $momentsWithoutLink);

        // 测试查询位置为NULL的动态（限制在当前测试账号下）
        $momentsWithoutLocation = $this->getRepository()->findBy(['location' => null, 'account' => $account]);
        $this->assertCount(4, $momentsWithoutLocation);

        // 测试查询点赞用户列表为NULL的动态（限制在当前测试账号下）
        $momentsWithoutLikeUsers = $this->getRepository()->findBy(['likeUsers' => null, 'account' => $account]);
        $this->assertCount(4, $momentsWithoutLikeUsers);

        // 测试查询评论列表为NULL的动态（限制在当前测试账号下）
        $momentsWithoutComments = $this->getRepository()->findBy(['comments' => null, 'account' => $account]);
        $this->assertCount(4, $momentsWithoutComments);

        // 测试查询原始数据为NULL的动态（限制在当前测试账号下）
        $momentsWithoutRawData = $this->getRepository()->findBy(['rawData' => null, 'account' => $account]);
        $this->assertCount(4, $momentsWithoutRawData);

        // 测试查询备注为NULL的动态（限制在当前测试账号下）
        $momentsWithoutRemark = $this->getRepository()->findBy(['remark' => null, 'account' => $account]);
        $this->assertCount(4, $momentsWithoutRemark);
    }

    #[TestDox('统计所有可空字段为NULL的记录数量')]
    public function testCountByAllNullableFields(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        // 创建具有各种可空字段值的动态
        $momentFull = new WeChatMoment();
        $momentFull->setAccount($account);
        $momentFull->setMomentId('full-moment');
        $momentFull->setAuthorWxid('author1');
        $momentFull->setAuthorNickname('Full Author');
        $momentFull->setAuthorAvatar('https://example.com/avatar.jpg');
        $momentFull->setTextContent('Full text content');
        $momentFull->setImages(['image1.jpg', 'image2.jpg']);
        $momentFull->setVideo(['url' => 'video.mp4', 'thumb' => 'thumb.jpg']);
        $momentFull->setLink(['title' => 'Link Title', 'url' => 'https://example.com']);
        $momentFull->setLocation('Beijing');
        $momentFull->setLikeUsers(['user1' => 'user1_data', 'user2' => 'user2_data']);
        $momentFull->setComments(['comment1' => 'comment1_data', 'comment2' => 'comment2_data']);
        $momentFull->setRawData('{"test": "data"}');
        $momentFull->setRemark('test-remark');
        $momentFull->setMomentType('image');
        $momentFull->setPublishTime(new \DateTimeImmutable());
        $momentFull->setValid(true);

        // 创建空字段动态
        $momentEmpty = new WeChatMoment();
        $momentEmpty->setAccount($account);
        $momentEmpty->setMomentId('empty-moment');
        $momentEmpty->setAuthorWxid('author2');
        $momentEmpty->setMomentType('text');
        $momentEmpty->setPublishTime(new \DateTimeImmutable());
        $momentEmpty->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($momentFull);
        self::getEntityManager()->persist($momentEmpty);
        self::getEntityManager()->flush();

        // 统计各个可空字段为NULL的记录数量（限制在当前测试账号下）
        $this->assertSame(1, $this->getRepository()->count(['authorNickname' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['authorAvatar' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['textContent' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['images' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['video' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['link' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['location' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['likeUsers' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['comments' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['rawData' => null, 'account' => $account]));
        $this->assertSame(1, $this->getRepository()->count(['remark' => null, 'account' => $account]));
    }

    // ================== findOneBy 排序测试 ==================

    #[TestDox('findOneBy应遵循排序参数')]
    public function testFindOneByShouldRespectOrderByClause(): void
    {
        $apiAccount = $this->createApiAccount();
        self::getEntityManager()->persist($apiAccount);

        $account = new WeChatAccount();
        $account->setApiAccount($apiAccount);
        $account->setDeviceId('test-device');
        $account->setNickname('Test Account');
        $account->setStatus('online');
        $account->setValid(true);

        $moment1 = new WeChatMoment();
        $moment1->setAccount($account);
        $moment1->setMomentId('moment-1');
        $moment1->setAuthorWxid('author1');
        $moment1->setAuthorNickname('Author B');
        $moment1->setTextContent('Moment B');
        $moment1->setMomentType('text');
        $moment1->setPublishTime(new \DateTimeImmutable('2023-01-01'));
        $moment1->setValid(true);

        $moment2 = new WeChatMoment();
        $moment2->setAccount($account);
        $moment2->setMomentId('moment-2');
        $moment2->setAuthorWxid('author2');
        $moment2->setAuthorNickname('Author A');
        $moment2->setTextContent('Moment A');
        $moment2->setMomentType('text');
        $moment2->setPublishTime(new \DateTimeImmutable('2023-01-02'));
        $moment2->setValid(true);

        self::getEntityManager()->persist($account);
        self::getEntityManager()->persist($moment1);
        self::getEntityManager()->persist($moment2);
        self::getEntityManager()->flush();

        // 按作者昵称升序查询，应返回第一个匹配的记录（限制在当前测试账号下）
        $moment = $this->getRepository()->findOneBy(
            ['momentType' => 'text', 'account' => $account],
            ['authorNickname' => 'ASC']
        );

        $this->assertInstanceOf(WeChatMoment::class, $moment);
        $this->assertSame('Author A', $moment->getAuthorNickname());

        // 按作者昵称降序查询，应返回第一个匹配的记录（限制在当前测试账号下）
        $momentDesc = $this->getRepository()->findOneBy(
            ['momentType' => 'text', 'account' => $account],
            ['authorNickname' => 'DESC']
        );

        $this->assertInstanceOf(WeChatMoment::class, $momentDesc);
        $this->assertSame('Author B', $momentDesc->getAuthorNickname());

        // 按发布时间升序查询（限制在当前测试账号下）
        $momentByTime = $this->getRepository()->findOneBy(
            ['momentType' => 'text', 'account' => $account],
            ['publishTime' => 'ASC']
        );

        $this->assertInstanceOf(WeChatMoment::class, $momentByTime);
        $this->assertSame('Moment B', $momentByTime->getTextContent()); // 2023-01-01的动态

        // 按发布时间降序查询（限制在当前测试账号下）
        $momentByTimeDesc = $this->getRepository()->findOneBy(
            ['momentType' => 'text', 'account' => $account],
            ['publishTime' => 'DESC']
        );

        $this->assertInstanceOf(WeChatMoment::class, $momentByTimeDesc);
        $this->assertSame('Moment A', $momentByTimeDesc->getTextContent()); // 2023-01-02的动态
    }
}
