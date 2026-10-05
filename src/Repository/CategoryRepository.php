<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SortDirection;

/**
 * @extends ServiceEntityRepository<Category>
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    /**
     * @return Category[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.sortOrder', SortDirection::Ascending)
            ->addOrderBy('c.name', SortDirection::Ascending)
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlug(string $slug): ?Category
    {
        return $this->findOneBy([
            'slug' => $slug,
        ]);
    }
}