<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Skill;
use App\Repository\SkillRepository;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SkillRepositoryTest extends KernelTestCase
{
    use DatabaseTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->resetDatabase();
    }

    public function testDashboardSearchMatchesNamesAndDescriptionsIgnoringCase(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($this->makeSkill('Symfony', 'symfony', 90, 0, null, 'PHP framework'));
        $entityManager->persist($this->makeSkill('Testing', 'testing', 80, 1, null, 'Symfony and PHPUnit'));
        $entityManager->persist($this->makeSkill('React', 'react', 70, 2, null, 'UI library'));
        $entityManager->flush();

        $skills = static::getContainer()
            ->get(SkillRepository::class)
            ->findForDashboard('SYMFONY');

        self::assertSame(['Symfony', 'Testing'], array_map(
            static fn (Skill $skill): ?string => $skill->getName(),
            $skills,
        ));
    }

    public function testDashboardQueryFiltersByCategoryAndSortsByRating(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $backend = $this->makeCategory('Backend', 'backend');
        $frontend = $this->makeCategory('Frontend', 'frontend');
        $entityManager->persist($backend);
        $entityManager->persist($frontend);
        $entityManager->persist($this->makeSkill('PHP', 'php', 80, 0, $backend));
        $entityManager->persist($this->makeSkill('Symfony', 'symfony', 95, 1, $backend));
        $entityManager->persist($this->makeSkill('Vue', 'vue', 100, 0, $frontend));
        $entityManager->flush();

        $skills = static::getContainer()->get(SkillRepository::class)->findForDashboard(
            category: $backend,
            sort: 'rating',
            direction: \SortDirection::Descending,
        );

        self::assertSame(['Symfony', 'PHP'], array_map(
            static fn (Skill $skill): ?string => $skill->getName(),
            $skills,
        ));
    }

    public function testUnknownSortFieldFallsBackToSortOrder(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($this->makeSkill('Second', 'second', 10, 2));
        $entityManager->persist($this->makeSkill('First', 'first', 90, 1));
        $entityManager->flush();

        $skills = static::getContainer()
            ->get(SkillRepository::class)
            ->findForDashboard(sort: 'arbitrary_sql', direction: \SortDirection::Ascending);

        self::assertSame(['First', 'Second'], array_map(
            static fn (Skill $skill): ?string => $skill->getName(),
            $skills,
        ));
    }

    public function testFindOneBySlugReturnsSkillOrNull(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $skill = $this->makeSkill();
        $entityManager->persist($skill);
        $entityManager->flush();

        $repository = static::getContainer()->get(SkillRepository::class);

        self::assertSame($skill->getId(), $repository->findOneBySlug('symfony')?->getId());
        self::assertNull($repository->findOneBySlug('missing'));
    }
}
