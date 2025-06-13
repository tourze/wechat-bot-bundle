<?php

namespace Tourze\WechatBotBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatTag;

/**
 * @extends ServiceEntityRepository<WeChatTag>
 *
 * @method WeChatTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method WeChatTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method WeChatTag[]    findAll()
 * @method WeChatTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WeChatTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeChatTag::class);
    }

    /**
     * 根据微信账号获取标签列表
     *
     * @param WeChatAccount $account
     * @return WeChatTag[]
     */
    public function findByAccount(WeChatAccount $account): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('valid', true)
            ->orderBy('t.sortOrder', 'DESC')
            ->addOrderBy('t.tagName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据标签ID查找标签
     *
     * @param string $tagId
     * @return WeChatTag|null
     */
    public function findByTagId(string $tagId): ?WeChatTag
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.tagId = :tagId')
            ->setParameter('tagId', $tagId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 根据账号和标签ID查找标签
     *
     * @param WeChatAccount $account
     * @param string $tagId
     * @return WeChatTag|null
     */
    public function findByAccountAndTagId(WeChatAccount $account, string $tagId): ?WeChatTag
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.tagId = :tagId')
            ->setParameter('account', $account)
            ->setParameter('tagId', $tagId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 根据账号和标签名称查找标签
     *
     * @param WeChatAccount $account
     * @param string $tagName
     * @return WeChatTag|null
     */
    public function findByAccountAndTagName(WeChatAccount $account, string $tagName): ?WeChatTag
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.tagName = :tagName')
            ->andWhere('t.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('tagName', $tagName)
            ->setParameter('valid', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 获取系统标签
     *
     * @param WeChatAccount $account
     * @return WeChatTag[]
     */
    public function findSystemTags(WeChatAccount $account): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.isSystem = :isSystem')
            ->andWhere('t.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('isSystem', true)
            ->setParameter('valid', true)
            ->orderBy('t.sortOrder', 'DESC')
            ->addOrderBy('t.tagName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取用户自定义标签
     *
     * @param WeChatAccount $account
     * @return WeChatTag[]
     */
    public function findCustomTags(WeChatAccount $account): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.isSystem = :isSystem')
            ->andWhere('t.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('isSystem', false)
            ->setParameter('valid', true)
            ->orderBy('t.sortOrder', 'DESC')
            ->addOrderBy('t.tagName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据好友微信ID查找包含该好友的标签
     *
     * @param WeChatAccount $account
     * @param string $friendWxid
     * @return WeChatTag[]
     */
    public function findTagsByFriend(WeChatAccount $account, string $friendWxid): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('JSON_CONTAINS(t.friendList, :friendWxid) = 1')
            ->andWhere('t.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('friendWxid', json_encode($friendWxid))
            ->setParameter('valid', true)
            ->orderBy('t.sortOrder', 'DESC')
            ->addOrderBy('t.tagName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 统计标签数量
     *
     * @param WeChatAccount|null $account
     * @param bool|null $isSystem
     * @return int
     */
    public function countTags(?WeChatAccount $account = null, ?bool $isSystem = null): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.valid = :valid')
            ->setParameter('valid', true);

        if ($account) {
            $qb->andWhere('t.account = :account')
                ->setParameter('account', $account);
        }

        if ($isSystem !== null) {
            $qb->andWhere('t.isSystem = :isSystem')
                ->setParameter('isSystem', $isSystem);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 搜索标签（按名称）
     *
     * @param WeChatAccount $account
     * @param string $keyword
     * @return WeChatTag[]
     */
    public function searchByName(WeChatAccount $account, string $keyword): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.tagName LIKE :keyword')
            ->andWhere('t.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('keyword', '%' . $keyword . '%')
            ->setParameter('valid', true)
            ->orderBy('t.sortOrder', 'DESC')
            ->addOrderBy('t.tagName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取空标签（没有好友的标签）
     *
     * @param WeChatAccount $account
     * @return WeChatTag[]
     */
    public function findEmptyTags(WeChatAccount $account): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.friendCount = 0')
            ->andWhere('t.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('valid', true)
            ->orderBy('t.tagName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 获取最大排序权重
     *
     * @param WeChatAccount $account
     * @return int
     */
    public function getMaxSortOrder(WeChatAccount $account): int
    {
        $result = $this->createQueryBuilder('t')
            ->select('MAX(t.sortOrder)')
            ->andWhere('t.account = :account')
            ->setParameter('account', $account)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (int) $result : 0;
    }
}
