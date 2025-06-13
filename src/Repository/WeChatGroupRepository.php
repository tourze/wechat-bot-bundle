<?php

namespace Tourze\WechatBotBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatGroup;

/**
 * @method WeChatGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method WeChatGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method WeChatGroup[]    findAll()
 * @method WeChatGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WeChatGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeChatGroup::class);
    }

    public function findByAccountAndGroupId(WeChatAccount $account, string $groupId): ?WeChatGroup
    {
        return $this->findOneBy([
            'account' => $account,
            'groupId' => $groupId,
            'valid' => true
        ]);
    }

    /**
     * @return WeChatGroup[]
     */
    public function findActiveGroupsByAccount(WeChatAccount $account): array
    {
        return $this->findBy([
            'account' => $account,
            'inGroup' => true,
            'valid' => true
        ], ['groupName' => 'ASC']);
    }

    /**
     * @return WeChatGroup[]
     */
    public function findByAccount(WeChatAccount $account): array
    {
        return $this->findBy([
            'account' => $account,
            'valid' => true
        ], ['groupName' => 'ASC']);
    }

    /**
     * @return WeChatGroup[]
     */
    public function searchByName(WeChatAccount $account, string $name): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.account = :account')
            ->andWhere('g.valid = :valid')
            ->andWhere('g.groupName LIKE :name OR g.remarkName LIKE :name')
            ->setParameter('account', $account)
            ->setParameter('valid', true)
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('g.groupName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countActiveByAccount(WeChatAccount $account): int
    {
        return $this->count([
            'account' => $account,
            'inGroup' => true,
            'valid' => true
        ]);
    }
}
