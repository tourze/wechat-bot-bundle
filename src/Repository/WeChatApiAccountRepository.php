<?php

namespace Tourze\WechatBotBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * @extends ServiceEntityRepository<WeChatApiAccount>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: WeChatApiAccount::class)]
class WeChatApiAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeChatApiAccount::class);
    }

    /**
     * 根据账号名称查找API账号
     */
    public function findByName(string $name): ?WeChatApiAccount
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * 查找所有有效的API账号
     *
     * @return WeChatApiAccount[]
     */
    public function findValidAccounts(): array
    {
        return $this->findBy(['valid' => true], ['name' => 'ASC']);
    }

    /**
     * 查找所有已连接的API账号
     *
     * @return WeChatApiAccount[]
     */
    public function findConnectedAccounts(): array
    {
        return $this->findBy(
            ['connectionStatus' => 'connected', 'valid' => true],
            ['lastLoginTime' => 'DESC']
        );
    }

    /**
     * 查找所有断开连接的API账号
     *
     * @return WeChatApiAccount[]
     */
    public function findDisconnectedAccounts(): array
    {
        return $this->findBy(
            ['connectionStatus' => 'disconnected', 'valid' => true],
            ['lastLoginTime' => 'DESC']
        );
    }

    /**
     * 查找所有出错的API账号
     *
     * @return WeChatApiAccount[]
     */
    public function findErrorAccounts(): array
    {
        return $this->findBy(
            ['connectionStatus' => 'error', 'valid' => true],
            ['lastLoginTime' => 'DESC']
        );
    }

    /**
     * 根据基础URL查找API账号
     */
    public function findByBaseUrl(string $baseUrl): ?WeChatApiAccount
    {
        return $this->findOneBy(['baseUrl' => rtrim($baseUrl, '/')]);
    }

    /**
     * 查找有有效Token的API账号
     *
     * @return WeChatApiAccount[]
     */
    public function findAccountsWithValidToken(): array
    {
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.valid = :valid')
            ->andWhere('a.accessToken IS NOT NULL')
            ->andWhere('a.accessToken != :emptyToken')
            ->andWhere('(a.tokenExpiresTime IS NULL OR a.tokenExpiresTime > :now)')
            ->setParameter('valid', true)
            ->setParameter('emptyToken', '')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.lastLoginTime', 'DESC')
        ;

        $result = $qb->getQuery()->getResult();

        if (!is_array($result)) {
            return [];
        }

        /** @var WeChatApiAccount[] */
        return array_filter($result, fn ($item) => $item instanceof WeChatApiAccount);
    }

    /**
     * 获取API账号统计信息
     *
     * @return array<string, mixed>
     */
    public function getAccountStatistics(): array
    {
        $qb = $this->createQueryBuilder('a');
        $qb->select('
                COUNT(a.id) as totalCount,
                SUM(CASE WHEN a.valid = true THEN 1 ELSE 0 END) as validCount,
                SUM(CASE WHEN a.connectionStatus = :connected THEN 1 ELSE 0 END) as connectedCount,
                SUM(CASE WHEN a.connectionStatus = :disconnected THEN 1 ELSE 0 END) as disconnectedCount,
                SUM(CASE WHEN a.connectionStatus = :error THEN 1 ELSE 0 END) as errorCount,
                SUM(a.apiCallCount) as totalApiCalls
            ')
            ->setParameter('connected', 'connected')
            ->setParameter('disconnected', 'disconnected')
            ->setParameter('error', 'error')
        ;

        $result = $qb->getQuery()->getSingleResult();

        if (!is_array($result)) {
            return $this->getDefaultStatistics();
        }

        // 确保是 array<string, mixed> 类型
        /** @var array<string, mixed> $validatedResult */
        $validatedResult = array_filter($result, static fn ($key) => is_string($key), ARRAY_FILTER_USE_KEY);

        return $this->buildStatisticsArray($validatedResult);
    }

    /**
     * @return array<string, int>
     */
    private function getDefaultStatistics(): array
    {
        return [
            'total' => 0,
            'valid' => 0,
            'connected' => 0,
            'disconnected' => 0,
            'error' => 0,
            'totalApiCalls' => 0,
        ];
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, int>
     */
    private function buildStatisticsArray(array $result): array
    {
        return [
            'total' => $this->extractNumericValue($result, 'totalCount'),
            'valid' => $this->extractNumericValue($result, 'validCount'),
            'connected' => $this->extractNumericValue($result, 'connectedCount'),
            'disconnected' => $this->extractNumericValue($result, 'disconnectedCount'),
            'error' => $this->extractNumericValue($result, 'errorCount'),
            'totalApiCalls' => $this->extractNumericValue($result, 'totalApiCalls'),
        ];
    }

    /**
     * @param array<string, mixed> $result
     */
    private function extractNumericValue(array $result, string $key): int
    {
        return isset($result[$key]) && is_numeric($result[$key]) ? (int) $result[$key] : 0;
    }

    /**
     * 查找最近活跃的API账号
     *
     * @return WeChatApiAccount[]
     */
    public function findRecentlyActiveAccounts(int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.valid = :valid')
            ->andWhere('a.lastApiCallTime IS NOT NULL')
            ->setParameter('valid', true)
            ->orderBy('a.lastApiCallTime', 'DESC')
            ->setMaxResults($limit)
        ;

        $result = $qb->getQuery()->getResult();

        // 确保返回正确的类型
        /** @var WeChatApiAccount[] */
        return is_array($result) ? $result : [];
    }

    /**
     * 根据用户名查找API账号
     */
    public function findByUsername(string $username): ?WeChatApiAccount
    {
        return $this->findOneBy(['username' => $username, 'valid' => true]);
    }

    /**
     * 查找需要刷新Token的账号（Token即将过期）
     *
     * @return WeChatApiAccount[]
     */
    public function findAccountsNeedingTokenRefresh(int $minutesBeforeExpiry = 30): array
    {
        $threshold = (new \DateTimeImmutable())->add(new \DateInterval("PT{$minutesBeforeExpiry}M"));

        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.valid = :valid')
            ->andWhere('a.accessToken IS NOT NULL')
            ->andWhere('a.tokenExpiresTime IS NOT NULL')
            ->andWhere('a.tokenExpiresTime < :threshold')
            ->andWhere('a.tokenExpiresTime > :now')
            ->setParameter('valid', true)
            ->setParameter('threshold', $threshold)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.tokenExpiresTime', 'ASC')
        ;

        $result = $qb->getQuery()->getResult();

        // 确保返回正确的类型
        /** @var WeChatApiAccount[] */
        return is_array($result) ? $result : [];
    }

    /**
     * 获取默认的API账号（第一个有效且已连接的账号）
     */
    public function getDefaultAccount(): ?WeChatApiAccount
    {
        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.valid = :valid')
            ->andWhere('a.connectionStatus = :connected')
            ->setParameter('valid', true)
            ->setParameter('connected', 'connected')
            ->orderBy('a.lastLoginTime', 'DESC')
            ->setMaxResults(1)
        ;

        $result = $qb->getQuery()->getOneOrNullResult();

        // 确保返回正确的类型或 null
        return $result instanceof WeChatApiAccount ? $result : null;
    }

    public function save(WeChatApiAccount $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WeChatApiAccount $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
