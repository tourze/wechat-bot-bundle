<?php

namespace Tourze\WechatBotBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatContact;

/**
 * @extends ServiceEntityRepository<WeChatContact>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: WeChatContact::class)]
class WeChatContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeChatContact::class);
    }

    public function findByAccountAndContactId(WeChatAccount $account, string $contactId): ?WeChatContact
    {
        return $this->findOneBy([
            'account' => $account,
            'contactId' => $contactId,
            'valid' => true,
        ]);
    }

    /**
     * @return WeChatContact[]
     */
    public function findFriendsByAccount(WeChatAccount $account): array
    {
        return $this->findBy([
            'account' => $account,
            'contactType' => 'friend',
            'valid' => true,
        ], ['nickname' => 'ASC']);
    }

    /**
     * @return WeChatContact[]
     */
    public function findByAccountAndType(WeChatAccount $account, string $contactType): array
    {
        return $this->findBy([
            'account' => $account,
            'contactType' => $contactType,
            'valid' => true,
        ], ['nickname' => 'ASC']);
    }

    /**
     * @return WeChatContact[]
     */
    public function searchByName(WeChatAccount $account, string $name): array
    {
        $result = $this->createQueryBuilder('c')
            ->where('c.account = :account')
            ->andWhere('c.valid = :valid')
            ->andWhere('c.nickname LIKE :name OR c.remarkName LIKE :name OR c.contactId LIKE :name')
            ->setParameter('account', $account)
            ->setParameter('valid', true)
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('c.nickname', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        // 确保返回正确的类型
        /** @var WeChatContact[] */
        return is_array($result) ? $result : [];
    }

    public function countByAccountAndType(WeChatAccount $account, string $contactType): int
    {
        return $this->count([
            'account' => $account,
            'contactType' => $contactType,
            'valid' => true,
        ]);
    }

    public function save(WeChatContact $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WeChatContact $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
