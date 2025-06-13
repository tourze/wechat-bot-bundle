<?php

namespace Tourze\WechatBotBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\WechatBotBundle\Entity\WeChatAccount;

/**
 * @method WeChatAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method WeChatAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method WeChatAccount[]    findAll()
 * @method WeChatAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WeChatAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeChatAccount::class);
    }

    public function findByDeviceId(string $deviceId): ?WeChatAccount
    {
        return $this->findOneBy(['deviceId' => $deviceId]);
    }

    public function findByWechatId(string $wechatId): ?WeChatAccount
    {
        return $this->findOneBy(['wechatId' => $wechatId]);
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
            ->getResult();

        $counts = [];
        foreach ($result as $row) {
            $counts[$row['status']] = (int) $row['count'];
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
        return $this->createQueryBuilder('a')
            ->where('a.valid = :valid')
            ->setParameter('valid', true)
            ->orderBy('a.createdTime', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
