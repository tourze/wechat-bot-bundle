<?php

namespace Tourze\WechatBotBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatMessage;

/**
 * @extends ServiceEntityRepository<WeChatMessage>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: WeChatMessage::class)]
class WeChatMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeChatMessage::class);
    }

    /**
     * @return WeChatMessage[]
     */
    public function findByAccount(WeChatAccount $account, int $limit = 100): array
    {
        $result = $this->createQueryBuilder('m')
            ->where('m.account = :account')
            ->andWhere('m.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('valid', true)
            ->orderBy('m.messageTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        /** @var WeChatMessage[] */
        return is_array($result) ? $result : [];
    }

    /**
     * @return WeChatMessage[]
     */
    public function findUnreadMessages(WeChatAccount $account): array
    {
        $result = $this->createQueryBuilder('m')
            ->where('m.account = :account')
            ->andWhere('m.direction = :direction')
            ->andWhere('m.isRead = :isRead')
            ->andWhere('m.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('direction', 'inbound')
            ->setParameter('isRead', false)
            ->setParameter('valid', true)
            ->orderBy('m.messageTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        /** @var WeChatMessage[] */
        return is_array($result) ? $result : [];
    }

    /**
     * @return WeChatMessage[]
     */
    public function findGroupMessages(WeChatAccount $account, string $groupId, int $limit = 50): array
    {
        $result = $this->createQueryBuilder('m')
            ->where('m.account = :account')
            ->andWhere('m.groupId = :groupId')
            ->andWhere('m.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('groupId', $groupId)
            ->setParameter('valid', true)
            ->orderBy('m.messageTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        /** @var WeChatMessage[] */
        return is_array($result) ? $result : [];
    }

    /**
     * @return WeChatMessage[]
     */
    public function findPrivateMessages(WeChatAccount $account, string $contactId, int $limit = 50): array
    {
        $result = $this->createQueryBuilder('m')
            ->where('m.account = :account')
            ->andWhere('m.groupId IS NULL')
            ->andWhere('(m.senderId = :contactId OR m.receiverId = :contactId)')
            ->andWhere('m.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('contactId', $contactId)
            ->setParameter('valid', true)
            ->orderBy('m.messageTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        /** @var WeChatMessage[] */
        return is_array($result) ? $result : [];
    }

    public function countUnreadByAccount(WeChatAccount $account): int
    {
        $result = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.account = :account')
            ->andWhere('m.direction = :direction')
            ->andWhere('m.isRead = :isRead')
            ->andWhere('m.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('direction', 'inbound')
            ->setParameter('isRead', false)
            ->setParameter('valid', true)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return (int) $result;
    }

    /**
     * @return array<string, int>
     */
    public function countByMessageType(WeChatAccount $account): array
    {
        $result = $this->createQueryBuilder('m')
            ->select('m.messageType', 'COUNT(m.id) as count')
            ->where('m.account = :account')
            ->andWhere('m.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('valid', true)
            ->groupBy('m.messageType')
            ->getQuery()
            ->getResult()
        ;

        // 确保 $result 是数组类型
        if (!is_array($result)) {
            return [];
        }

        $counts = [];
        foreach ($result as $row) {
            if (is_array($row) && isset($row['count'], $row['messageType'])) {
                $messageType = is_string($row['messageType']) ? $row['messageType'] : 'unknown';
                $count = is_numeric($row['count']) ? (int) $row['count'] : 0;
                $counts[$messageType] = $count;
            }
        }

        return $counts;
    }

    public function save(WeChatMessage $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WeChatMessage $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
