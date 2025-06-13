<?php

namespace Tourze\WechatBotBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatContact;

/**
 * @method WeChatContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method WeChatContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method WeChatContact[]    findAll()
 * @method WeChatContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
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
            'valid' => true
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
            'valid' => true
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
            'valid' => true
        ], ['nickname' => 'ASC']);
    }

    /**
     * @return WeChatContact[]
     */
    public function searchByName(WeChatAccount $account, string $name): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.account = :account')
            ->andWhere('c.valid = :valid')
            ->andWhere('c.nickname LIKE :name OR c.remarkName LIKE :name OR c.contactId LIKE :name')
            ->setParameter('account', $account)
            ->setParameter('valid', true)
            ->setParameter('name', '%' . $name . '%')
            ->orderBy('c.nickname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countByAccountAndType(WeChatAccount $account, string $contactType): int
    {
        return $this->count([
            'account' => $account,
            'contactType' => $contactType,
            'valid' => true
        ]);
    }
}
