<?php

namespace Tourze\WechatBotBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatTag;

/**
 * @extends ServiceEntityRepository<WeChatTag>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: WeChatTag::class)]
class WeChatTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeChatTag::class);
    }

    /**
     * 根据微信账号获取标签列表
     *
     * @return WeChatTag[]
     */
    public function findByAccount(WeChatAccount $account): array
    {
        $result = $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('valid', true)
            ->orderBy('t.sortOrder', 'DESC')
            ->addOrderBy('t.tagName', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        /** @var WeChatTag[] */
        return is_array($result) ? $result : [];
    }

    /**
     * 根据标签ID查找标签
     */
    public function findByTagId(string $tagId): ?WeChatTag
    {
        $result = $this->createQueryBuilder('t')
            ->andWhere('t.tagId = :tagId')
            ->setParameter('tagId', $tagId)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result instanceof WeChatTag ? $result : null;
    }

    /**
     * 根据账号和标签ID查找标签
     */
    public function findByAccountAndTagId(WeChatAccount $account, string $tagId): ?WeChatTag
    {
        $result = $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.tagId = :tagId')
            ->setParameter('account', $account)
            ->setParameter('tagId', $tagId)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result instanceof WeChatTag ? $result : null;
    }

    /**
     * 根据账号和标签名称查找标签
     */
    public function findByAccountAndTagName(WeChatAccount $account, string $tagName): ?WeChatTag
    {
        $result = $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.tagName = :tagName')
            ->andWhere('t.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('tagName', $tagName)
            ->setParameter('valid', true)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result instanceof WeChatTag ? $result : null;
    }

    /**
     * 获取系统标签
     *
     * @return WeChatTag[]
     */
    public function findSystemTags(WeChatAccount $account): array
    {
        $result = $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.isSystem = :isSystem')
            ->andWhere('t.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('isSystem', true)
            ->setParameter('valid', true)
            ->orderBy('t.sortOrder', 'DESC')
            ->addOrderBy('t.tagName', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        /** @var WeChatTag[] */
        return is_array($result) ? $result : [];
    }

    /**
     * 获取用户自定义标签
     *
     * @return WeChatTag[]
     */
    public function findCustomTags(WeChatAccount $account): array
    {
        $result = $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.isSystem = :isSystem')
            ->andWhere('t.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('isSystem', false)
            ->setParameter('valid', true)
            ->orderBy('t.sortOrder', 'DESC')
            ->addOrderBy('t.tagName', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        /** @var WeChatTag[] */
        return is_array($result) ? array_filter($result, fn ($item) => $item instanceof WeChatTag) : [];
    }

    /**
     * 根据好友微信ID查找包含该好友的标签
     *
     * @return WeChatTag[]
     */
    public function findTagsByFriend(WeChatAccount $account, string $friendWxid): array
    {
        // 使用LIKE查询替代JSON_CONTAINS，适用于SQLite测试环境
        $result = $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.friendList LIKE :friendWxid')
            ->andWhere('t.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('friendWxid', '%"' . $friendWxid . '"%')
            ->setParameter('valid', true)
            ->orderBy('t.sortOrder', 'DESC')
            ->addOrderBy('t.tagName', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        /** @var WeChatTag[] */
        return is_array($result) ? array_filter($result, fn ($item) => $item instanceof WeChatTag) : [];
    }

    /**
     * 统计标签数量
     */
    public function countTags(?WeChatAccount $account = null, ?bool $isSystem = null): int
    {
        $qb = $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->andWhere('t.valid = :valid')
            ->setParameter('valid', true)
        ;

        if ((bool) $account) {
            $qb->andWhere('t.account = :account')
                ->setParameter('account', $account)
            ;
        }

        if (null !== $isSystem) {
            $qb->andWhere('t.isSystem = :isSystem')
                ->setParameter('isSystem', $isSystem)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 搜索标签（按名称）
     *
     * @return WeChatTag[]
     */
    public function searchByName(WeChatAccount $account, string $keyword): array
    {
        $result = $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.tagName LIKE :keyword')
            ->andWhere('t.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('keyword', '%' . $keyword . '%')
            ->setParameter('valid', true)
            ->orderBy('t.sortOrder', 'DESC')
            ->addOrderBy('t.tagName', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        /** @var WeChatTag[] */
        return is_array($result) ? array_filter($result, fn ($item) => $item instanceof WeChatTag) : [];
    }

    /**
     * 获取空标签（没有好友的标签）
     *
     * @return WeChatTag[]
     */
    public function findEmptyTags(WeChatAccount $account): array
    {
        $result = $this->createQueryBuilder('t')
            ->andWhere('t.account = :account')
            ->andWhere('t.friendCount = 0')
            ->andWhere('t.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('valid', true)
            ->orderBy('t.tagName', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        /** @var WeChatTag[] */
        return is_array($result) ? array_filter($result, fn ($item) => $item instanceof WeChatTag) : [];
    }

    /**
     * 获取最大排序权重
     */
    public function getMaxSortOrder(WeChatAccount $account): int
    {
        $result = $this->createQueryBuilder('t')
            ->select('MAX(t.sortOrder)')
            ->andWhere('t.account = :account')
            ->setParameter('account', $account)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return null === $result ? 0 : (int) $result;
    }

    public function save(WeChatTag $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WeChatTag $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
