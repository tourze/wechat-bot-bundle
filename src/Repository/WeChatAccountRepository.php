<?php

namespace Tourze\WechatBotBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\WechatBotBundle\Entity\WeChatAccount;

/**
 * @extends ServiceEntityRepository<WeChatAccount>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: WeChatAccount::class)]
class WeChatAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeChatAccount::class);
    }

    public function findByDeviceId(string $deviceId): ?WeChatAccount
    {
        $account = $this->findOneBy(['deviceId' => $deviceId]);

        return $account instanceof WeChatAccount ? $account : null;
    }

    public function findByWechatId(string $wechatId): ?WeChatAccount
    {
        $account = $this->findOneBy(['wechatId' => $wechatId]);

        return $account instanceof WeChatAccount ? $account : null;
    }

    /**
     * @return WeChatAccount[]
     */
    public function findByOnline(): array
    {
        return $this->findBy(['status' => 'online', 'valid' => true]);
    }

    /**
     * @return WeChatAccount[]
     */
    public function findByPendingLogin(): array
    {
        return $this->findBy(['status' => 'pending_login', 'valid' => true]);
    }

    /**
     * @return WeChatAccount[]
     */
    public function findByOffline(): array
    {
        return $this->findBy(['status' => 'offline', 'valid' => true]);
    }

    /**
     * @return WeChatAccount[]
     */
    public function findValid(): array
    {
        return $this->findBy(['valid' => true]);
    }

    /**
     * @return array<string, int>
     */
    public function countByStatus(): array
    {
        $result = $this->createQueryBuilder('a')
            ->select('a.status', 'COUNT(a.id) as count')
            ->where('a.valid = :valid')
            ->setParameter('valid', true)
            ->groupBy('a.status')
            ->getQuery()
            ->getResult()
        ;

        $counts = [];
        if (is_array($result)) {
            foreach ($result as $row) {
                if (is_array($row)
                    && isset($row['status'], $row['count'])
                    && is_string($row['status'])
                    && is_numeric($row['count'])
                ) {
                    $counts[$row['status']] = (int) $row['count'];
                }
            }
        }

        return $counts;
    }

    /**
     * 查找所有活跃账号（非删除状态）
     *
     * @return WeChatAccount[]
     */
    public function findActiveAccounts(): array
    {
        $result = $this->createQueryBuilder('a')
            ->where('a.valid = :valid')
            ->setParameter('valid', true)
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        if (!is_array($result)) {
            return [];
        }

        return array_filter($result, fn ($item) => $item instanceof WeChatAccount);
    }

    /**
     * 查找所有在线账号
     *
     * @return WeChatAccount[]
     */
    public function findOnlineAccounts(): array
    {
        return $this->findBy(['status' => 'online', 'valid' => true]);
    }

    /**
     * 查找所有有效账号
     *
     * @return WeChatAccount[]
     */
    public function findAllValidAccounts(): array
    {
        return $this->findBy(['valid' => true]);
    }

    public function save(WeChatAccount $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WeChatAccount $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
