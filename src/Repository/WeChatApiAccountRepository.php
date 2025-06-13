<?php

namespace Tourze\WechatBotBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\WechatBotBundle\Entity\WeChatApiAccount;

/**
 * @extends ServiceEntityRepository<WeChatApiAccount>
 * @method WeChatApiAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method WeChatApiAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method WeChatApiAccount[]    findAll()
 * @method WeChatApiAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
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
            ->setParameter('now', new \DateTime())
            ->orderBy('a.lastLoginTime', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取API账号统计信息
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
            ->setParameter('error', 'error');

        $result = $qb->getQuery()->getSingleResult();

        return [
            'total' => (int) $result['totalCount'],
            'valid' => (int) $result['validCount'],
            'connected' => (int) $result['connectedCount'],
            'disconnected' => (int) $result['disconnectedCount'],
            'error' => (int) $result['errorCount'],
            'totalApiCalls' => (int) $result['totalApiCalls'],
        ];
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
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
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
        $threshold = new \DateTime();
        $threshold->add(new \DateInterval("PT{$minutesBeforeExpiry}M"));

        $qb = $this->createQueryBuilder('a');
        $qb->andWhere('a.valid = :valid')
            ->andWhere('a.accessToken IS NOT NULL')
            ->andWhere('a.tokenExpiresTime IS NOT NULL')
            ->andWhere('a.tokenExpiresTime < :threshold')
            ->andWhere('a.tokenExpiresTime > :now')
            ->setParameter('valid', true)
            ->setParameter('threshold', $threshold)
            ->setParameter('now', new \DateTime())
            ->orderBy('a.tokenExpiresTime', 'ASC');

        return $qb->getQuery()->getResult();
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
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
