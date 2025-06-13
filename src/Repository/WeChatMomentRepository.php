<?php

namespace Tourze\WechatBotBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\WechatBotBundle\Entity\WeChatAccount;
use Tourze\WechatBotBundle\Entity\WeChatMoment;

/**
 * @extends ServiceEntityRepository<WeChatMoment>
 *
 * @method WeChatMoment|null find($id, $lockMode = null, $lockVersion = null)
 * @method WeChatMoment|null findOneBy(array $criteria, array $orderBy = null)
 * @method WeChatMoment[]    findAll()
 * @method WeChatMoment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WeChatMomentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeChatMoment::class);
    }

    /**
     * 根据微信账号获取朋友圈动态
     *
     * @param WeChatAccount $account
     * @param int $limit
     * @param int $offset
     * @return WeChatMoment[]
     */
    public function findByAccount(WeChatAccount $account, int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.account = :account')
            ->andWhere('m.valid = :valid')
            ->setParameter('account', $account)
            ->setParameter('valid', true)
            ->orderBy('m.publishTime', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据发布者微信ID获取朋友圈动态
     *
     * @param string $authorWxid
     * @param int $limit
     * @param int $offset
     * @return WeChatMoment[]
     */
    public function findByAuthorWxid(string $authorWxid, int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.authorWxid = :authorWxid')
            ->andWhere('m.valid = :valid')
            ->setParameter('authorWxid', $authorWxid)
            ->setParameter('valid', true)
            ->orderBy('m.publishTime', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据动态类型获取朋友圈动态
     *
     * @param string $momentType
     * @param int $limit
     * @param int $offset
     * @return WeChatMoment[]
     */
    public function findByMomentType(string $momentType, int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.momentType = :momentType')
            ->andWhere('m.valid = :valid')
            ->setParameter('momentType', $momentType)
            ->setParameter('valid', true)
            ->orderBy('m.publishTime', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * 根据朋友圈ID查找动态
     *
     * @param string $momentId
     * @return WeChatMoment|null
     */
    public function findByMomentId(string $momentId): ?WeChatMoment
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.momentId = :momentId')
            ->setParameter('momentId', $momentId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 统计朋友圈动态数量
     *
     * @param WeChatAccount|null $account
     * @return int
     */
    public function countMoments(?WeChatAccount $account = null): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.valid = :valid')
            ->setParameter('valid', true);

        if ($account) {
            $qb->andWhere('m.account = :account')
                ->setParameter('account', $account);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * 根据时间范围获取朋友圈动态
     *
     * @param \DateTimeInterface $startTime
     * @param \DateTimeInterface $endTime
     * @param WeChatAccount|null $account
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
            ->orderBy('m.publishTime', 'DESC');

        if ($account) {
            $qb->andWhere('m.account = :account')
                ->setParameter('account', $account);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * 获取最新的朋友圈动态
     *
     * @param int $limit
     * @return WeChatMoment[]
     */
    public function findLatest(int $limit = 10): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.valid = :valid')
            ->setParameter('valid', true)
            ->orderBy('m.publishTime', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * 搜索朋友圈动态（按文本内容）
     *
     * @param string $keyword
     * @param int $limit
     * @param int $offset
     * @return WeChatMoment[]
     */
    public function searchByContent(string $keyword, int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.textContent LIKE :keyword')
            ->andWhere('m.valid = :valid')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->setParameter('valid', true)
            ->orderBy('m.publishTime', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }
}
