<?php

namespace Tourze\WechatBotBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatGroup;

/**
 * @extends ServiceEntityRepository<WeChatGroup>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: WeChatGroup::class)]
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
            'valid' => true,
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
            'valid' => true,
        ], ['groupName' => 'ASC']);
    }

    /**
     * @return WeChatGroup[]
     */
    public function findByAccount(WeChatAccount $account): array
    {
        return $this->findBy([
            'account' => $account,
            'valid' => true,
        ], ['groupName' => 'ASC']);
    }

    /**
     * @return WeChatGroup[]
     */
    public function searchByName(WeChatAccount $account, string $name): array
    {
        $result = $this->createQueryBuilder('g')
            ->where('g.account = :account')
            ->andWhere('g.valid = :valid')
            ->andWhere('g.groupName LIKE :name OR g.remarkName LIKE :name')
            ->setParameter('account', $account)
            ->setParameter('valid', true)
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('g.groupName', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        // 确保返回正确的类型
        /** @var WeChatGroup[] */
        return is_array($result) ? $result : [];
    }

    public function countActiveByAccount(WeChatAccount $account): int
    {
        return $this->count([
            'account' => $account,
            'inGroup' => true,
            'valid' => true,
        ]);
    }

    public function save(WeChatGroup $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WeChatGroup $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
