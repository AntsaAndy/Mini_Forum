<?php

namespace App\Repository;

use App\Entity\Topic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Topic>
 */
class TopicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Topic::class);
    }

    public function findAllWithPostsCount(): array
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.posts', 'p')
            ->leftJoin('t.author', 'a')
            ->addSelect('COUNT(p.id) as postsCount')
            ->addSelect('a')
            ->groupBy('t.id')
            ->orderBy('t.isPinned', 'DESC')
            ->addOrderBy('t.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.category = :category')
            ->setParameter('category', $category)
            ->orderBy('t.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findRecentTopics(int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function searchTopics(string $query): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.title LIKE :query OR t.description LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('t.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}