<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Category;
use App\Entity\Skill;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class PortfolioEntityTest extends TestCase
{
    public function testCategorySkillHelpersKeepBothSidesInSync(): void
    {
        $category = (new Category())->setName('Backend')->setSlug('backend');
        $skill = (new Skill())->setName('Symfony')->setSlug('symfony');

        $category->addSkill($skill);

        self::assertSame($category, $skill->getCategory());
        self::assertTrue($category->getSkills()->contains($skill));

        $category->removeSkill($skill);

        self::assertNull($skill->getCategory());
        self::assertFalse($category->getSkills()->contains($skill));
    }

    public function testUserAlwaysHasTheBaseRoleAndDoesNotReturnDuplicates(): void
    {
        $user = (new User())->setRoles(['ROLE_EDITOR', 'ROLE_USER', 'ROLE_EDITOR']);

        self::assertSame(['ROLE_EDITOR', 'ROLE_USER'], $user->getRoles());
    }

    public function testUserSerializationDoesNotExposeThePasswordHash(): void
    {
        $hash = '$2y$13$example-password-hash-that-must-not-be-serialized';
        $user = (new User())->setPassword($hash);

        $serialized = serialize($user);

        self::assertStringNotContainsString($hash, $serialized);
        self::assertStringContainsString(hash('crc32c', $hash), $serialized);
    }
}
