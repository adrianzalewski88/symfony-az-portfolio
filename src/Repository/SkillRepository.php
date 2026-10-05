<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Skill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SortDirection;

/**
 * @extends ServiceEntityRepository<Skill>
 */
class SkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Skill::class);
    }

    /**
     * Find skills for the dashboard with optional search, category,
     * sorting, and sort direction.
     *
     * @return Skill[]
     */
    public function findForDashboard(
        ?string $search = null,
        ?Category $category = null,
        string $sort = 'sortOrder',
        SortDirection $direction = SortDirection::Ascending
    ): array {
        $queryBuilder = $this->createQueryBuilder('s')
            ->leftJoin('s.category', 'c')
            ->addSelect('c');

        if ($search !== null && $search !== '') {
            $queryBuilder
                ->andWhere(
                    'LOWER(s.name) LIKE :search
                    OR LOWER(s.description) LIKE :search'
                )
                ->setParameter(
                    'search',
                    '%' . mb_strtolower($search) . '%'
                );
        }

        if ($category !== null) {
            $queryBuilder
                ->andWhere('s.category = :category')
                ->setParameter('category', $category);
        }

        $allowedSorts = [
            'name' => 's.name',
            'rating' => 's.rating',
            'sortOrder' => 's.sortOrder',
            'updatedAt' => 's.updatedAt',
        ];

        $sortField = $allowedSorts[$sort] ?? $allowedSorts['sortOrder'];

        $queryBuilder
            ->orderBy($sortField, $direction)
            ->addOrderBy('s.name', SortDirection::Ascending);

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Skill[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.category', 'c')
            ->addSelect('c')
            ->orderBy('s.sortOrder', SortDirection::Ascending)
            ->addOrderBy('s.name', SortDirection::Ascending)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Skill[]
     */
    public function findByCategory(Category $category): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.category = :category')
            ->setParameter('category', $category)
            ->orderBy('s.sortOrder', SortDirection::Ascending)
            ->addOrderBy('s.name', SortDirection::Ascending)
            ->getQuery()
            ->getResult();
    }

    public function findOneBySlug(string $slug): ?Skill
    {
        return $this->findOneBy([
            'slug' => $slug,
        ]);
    }
}