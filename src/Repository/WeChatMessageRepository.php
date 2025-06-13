<?php

namespace Tourze\WechatBotBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatMessage;

/**
 * @method WeChatMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method WeChatMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method WeChatMessage[]    findAll()
 * @method WeChatMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
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
        return $this->createQueryBuilder('m')
            ->where('m.account = :account')
            ->andWhere('m.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('valid', true)
            ->orderBy('m.messageTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return WeChatMessage[]
     */
    public function findUnreadMessages(WeChatAccount $account): array
    {
        return $this->createQueryBuilder('m')
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
            ->getResult();
    }

    /**
     * @return WeChatMessage[]
     */
    public function findGroupMessages(WeChatAccount $account, string $groupId, int $limit = 50): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.account = :account')
            ->andWhere('m.groupId = :groupId')
            ->andWhere('m.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('groupId', $groupId)
            ->setParameter('valid', true)
            ->orderBy('m.messageTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return WeChatMessage[]
     */
    public function findPrivateMessages(WeChatAccount $account, string $contactId, int $limit = 50): array
    {
        return $this->createQueryBuilder('m')
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
            ->getResult();
    }

    public function countUnreadByAccount(WeChatAccount $account): int
    {
        return $this->createQueryBuilder('m')
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
            ->getSingleScalarResult();
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
            ->getResult();

        $counts = [];
        foreach ($result as $row) {
            $counts[$row['messageType']] = (int) $row['count'];
        }

        return $counts;
    }
}
