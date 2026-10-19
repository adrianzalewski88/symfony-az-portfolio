<?php

namespace App\Tests\Support;

use App\Entity\Category;
use App\Entity\Skill;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

trait DatabaseTestTrait
{
    protected function resetDatabase(): EntityManagerInterface
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        return $entityManager;
    }

    protected function makeCategory(
        string $name = 'Backend',
        string $slug = 'backend',
        int $sortOrder = 0,
        ?string $description = null,
    ): Category {
        return (new Category())
            ->setName($name)
            ->setSlug($slug)
            ->setSortOrder($sortOrder)
            ->setDescription($description);
    }

    protected function makeSkill(
        string $name = 'Symfony',
        string $slug = 'symfony',
        int $rating = 80,
        int $sortOrder = 0,
        ?Category $category = null,
        ?string $description = null,
    ): Skill {
        return (new Skill())
            ->setName($name)
            ->setSlug($slug)
            ->setRating($rating)
            ->setSortOrder($sortOrder)
            ->setCategory($category)
            ->setDescription($description);
    }

    protected function makeUser(
        string $email = 'viewer@example.test',
        array $roles = ['ROLE_USER'],
        string $plainPassword = 'correct-horse-battery-staple',
        bool $active = true,
    ): User {
        $user = (new User())
            ->setEmail($email)
            ->setFirstName('Portfolio')
            ->setLastName('Tester')
            ->setRoles($roles)
            ->setIsActive($active);

        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($hasher->hashPassword($user, $plainPassword));

        return $user;
    }
}
