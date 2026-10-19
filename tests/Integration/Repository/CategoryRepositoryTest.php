<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CategoryRepositoryTest extends KernelTestCase
{
    use DatabaseTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->resetDatabase();
    }

    public function testFindAllOrderedUsesSortOrderThenName(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($this->makeCategory('Zoology', 'zoology', 1));
        $entityManager->persist($this->makeCategory('Backend', 'backend', 0));
        $entityManager->persist($this->makeCategory('Frontend', 'frontend', 0));
        $entityManager->flush();

        $categories = static::getContainer()->get(CategoryRepository::class)->findAllOrdered();

        self::assertSame(['Backend', 'Frontend', 'Zoology'], array_map(
            static fn (Category $category): ?string => $category->getName(),
            $categories,
        ));
    }

    public function testFindOneBySlugReturnsCategoryOrNull(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $category = $this->makeCategory();
        $entityManager->persist($category);
        $entityManager->flush();

        $repository = static::getContainer()->get(CategoryRepository::class);

        self::assertSame($category->getId(), $repository->findOneBySlug('backend')?->getId());
        self::assertNull($repository->findOneBySlug('missing'));
    }

    public function testHomepageQueryIncludesEmptyCategoriesAndOrdersSkills(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $backend = $this->makeCategory('Backend', 'backend', 0);
        $empty = $this->makeCategory('Empty', 'empty', 1);
        $entityManager->persist($backend);
        $entityManager->persist($empty);
        $entityManager->persist($this->makeSkill('Zulu', 'zulu', 70, 2, $backend));
        $entityManager->persist($this->makeSkill('Alpha', 'alpha', 70, 1, $backend));
        $entityManager->flush();
        $entityManager->clear();

        $categories = static::getContainer()
            ->get(CategoryRepository::class)
            ->findAllForHomepage();

        self::assertSame(['Backend', 'Empty'], array_map(
            static fn (Category $category): ?string => $category->getName(),
            $categories,
        ));
        self::assertSame(['Alpha', 'Zulu'], array_map(
            static fn ($skill): ?string => $skill->getName(),
            $categories[0]->getSkills()->toArray(),
        ));
        self::assertCount(0, $categories[1]->getSkills());
    }
}
