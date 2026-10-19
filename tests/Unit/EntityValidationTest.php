<?php

namespace App\Tests\Unit;

use App\Entity\Category;
use App\Entity\Skill;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class EntityValidationTest extends KernelTestCase
{
    #[DataProvider('invalidRatings')]
    public function testSkillRatingMustBeBetweenZeroAndOneHundred(int $rating): void
    {
        self::bootKernel();
        $skill = (new Skill())->setName('Symfony')->setSlug('symfony')->setRating($rating);

        $violations = static::getContainer()
            ->get(ValidatorInterface::class)
            ->validate($skill);

        self::assertNotEmpty($violations);
        self::assertSame('rating', $violations[0]->getPropertyPath());
    }

    public static function invalidRatings(): iterable
    {
        yield 'below minimum' => [-1];
        yield 'above maximum' => [101];
    }

    public function testRatingBoundariesAndOptionalFieldsAreValid(): void
    {
        self::bootKernel();
        $validator = static::getContainer()->get(ValidatorInterface::class);

        foreach ([0, 100] as $rating) {
            $skill = (new Skill())
                ->setName('Skill '.$rating)
                ->setSlug('skill-'.$rating)
                ->setRating($rating);

            self::assertCount(0, $validator->validate($skill));
        }
    }

    public function testCategoryRequiresNameAndSlugAndNonNegativeSortOrder(): void
    {
        self::bootKernel();
        $category = (new Category())->setName('')->setSlug('')->setSortOrder(-1);

        $violations = static::getContainer()
            ->get(ValidatorInterface::class)
            ->validate($category);

        $invalidProperties = array_map(
            static fn ($violation): string => $violation->getPropertyPath(),
            iterator_to_array($violations),
        );

        self::assertContains('name', $invalidProperties);
        self::assertContains('slug', $invalidProperties);
        self::assertContains('sortOrder', $invalidProperties);
    }

    public function testSkillRequiresNameAndSlugAndNonNegativeSortOrder(): void
    {
        self::bootKernel();
        $skill = (new Skill())->setName('')->setSlug('')->setSortOrder(-1);

        $violations = static::getContainer()
            ->get(ValidatorInterface::class)
            ->validate($skill);

        $invalidProperties = array_map(
            static fn ($violation): string => $violation->getPropertyPath(),
            iterator_to_array($violations),
        );

        self::assertContains('name', $invalidProperties);
        self::assertContains('slug', $invalidProperties);
        self::assertContains('sortOrder', $invalidProperties);
    }

    public function testCategoryAndSkillNamesAndSlugsCannotExceedColumnLength(): void
    {
        self::bootKernel();
        $validator = static::getContainer()->get(ValidatorInterface::class);
        $longValue = str_repeat('x', 256);
        $category = (new Category())->setName($longValue)->setSlug($longValue);
        $skill = (new Skill())->setName($longValue)->setSlug($longValue);

        self::assertNotEmpty($validator->validate($category));
        self::assertNotEmpty($validator->validate($skill));
    }
}
