<?php

namespace Tourze\WechatBotBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatMoment;

/**
 * @extends ServiceEntityRepository<WeChatMoment>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: WeChatMoment::class)]
class WeChatMomentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeChatMoment::class);
    }

    /**
     * 根据微信账号获取朋友圈动态
     *
     * @return WeChatMoment[]
     */
    public function findByAccount(WeChatAccount $account, int $limit = 20, int $offset = 0): array
    {
        $result = $this->createQueryBuilder('m')
            ->andWhere('m.account = :account')
            ->andWhere('m.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('valid', true)
            ->orderBy('m.publishTime', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;

        /** @var WeChatMoment[] */
        return is_array($result) ? $result : [];
    }

    /**
     * 根据发布者微信ID获取朋友圈动态
     *
     * @return WeChatMoment[]
     */
    public function findByAuthorWxid(string $authorWxid, int $limit = 20, int $offset = 0): array
    {
        $result = $this->createQueryBuilder('m')
            ->andWhere('m.authorWxid = :authorWxid')
            ->andWhere('m.valid = :valid')
            ->setParameter('authorWxid', $authorWxid)
            ->setParameter('valid', true)
            ->orderBy('m.publishTime', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;

        /** @var WeChatMoment[] */
        return is_array($result) ? $result : [];
    }

    /**
     * 根据动态类型获取朋友圈动态
     *
     * @return WeChatMoment[]
     */
    public function findByMomentType(string $momentType, int $limit = 20, int $offset = 0): array
    {
        $result = $this->createQueryBuilder('m')
            ->andWhere('m.momentType = :momentType')
            ->andWhere('m.valid = :valid')
            ->setParameter('momentType', $momentType)
            ->setParameter('valid', true)
            ->orderBy('m.publishTime', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;

        /** @var WeChatMoment[] */
        return is_array($result) ? $result : [];
    }

    /**
     * 根据朋友圈ID查找动态
     */
    public function findByMomentId(string $momentId): ?WeChatMoment
    {
        $result = $this->createQueryBuilder('m')
            ->andWhere('m.momentId = :momentId')
            ->setParameter('momentId', $momentId)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result instanceof WeChatMoment ? $result : null;
    }

    /**
     * 统计朋友圈动态数量
     */
    public function countMoments(?WeChatAccount $account = null): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.valid = :valid')
            ->setParameter('valid', true)
        ;

        if ((bool) $account) {
            $qb->andWhere('m.account = :account')
                ->setParameter('account', $account)
            ;
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 根据时间范围获取朋友圈动态
     *
     * @return WeChatMoment[]
     */
    public function findByTimeRange(\DateTimeInterface $startTime, \DateTimeInterface $endTime, ?WeChatAccount $account = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->andWhere('m.publishTime >= :startTime')
            ->andWhere('m.publishTime <= :endTime')
            ->andWhere('m.valid = :valid')
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime)
            ->setParameter('valid', true)
            ->orderBy('m.publishTime', 'DESC')
        ;

        if ((bool) $account) {
            $qb->andWhere('m.account = :account')
                ->setParameter('account', $account)
            ;
        }

        $result = $qb->getQuery()->getResult();

        /** @var WeChatMoment[] */
        return is_array($result) ? $result : [];
    }

    /**
     * 获取最新的朋友圈动态
     *
     * @return WeChatMoment[]
     */
    public function findLatest(int $limit = 10): array
    {
        $result = $this->createQueryBuilder('m')
            ->andWhere('m.valid = :valid')
            ->setParameter('valid', true)
            ->orderBy('m.publishTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;

        /** @var WeChatMoment[] */
        return is_array($result) ? $result : [];
    }

    /**
     * 搜索朋友圈动态（按文本内容）
     *
     * @return WeChatMoment[]
     */
    public function searchByContent(string $keyword, int $limit = 20, int $offset = 0): array
    {
        $result = $this->createQueryBuilder('m')
            ->andWhere('m.textContent LIKE :keyword')
            ->andWhere('m.valid = :valid')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->setParameter('valid', true)
            ->orderBy('m.publishTime', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;

        /** @var WeChatMoment[] */
        return is_array($result) ? $result : [];
    }

    public function save(WeChatMoment $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(WeChatMoment $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
