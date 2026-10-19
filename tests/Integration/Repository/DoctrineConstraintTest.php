<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Category;
use App\Entity\Skill;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\Support\DatabaseTestTrait;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

final class DoctrineConstraintTest extends KernelTestCase
{
    use DatabaseTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->resetDatabase();
    }

    public function testCategoryNameMustBeUniqueInTheDatabase(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($this->makeCategory('Backend', 'backend'));
        $entityManager->flush();
        $entityManager->persist($this->makeCategory('Backend', 'server-side'));

        $this->expectException(UniqueConstraintViolationException::class);
        $entityManager->flush();
    }

    public function testCategorySlugMustBeUniqueInTheDatabase(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($this->makeCategory('Backend', 'backend'));
        $entityManager->flush();
        $entityManager->persist($this->makeCategory('Server Side', 'backend'));

        $this->expectException(UniqueConstraintViolationException::class);
        $entityManager->flush();
    }

    public function testSkillSlugMustBeUniqueInTheDatabase(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($this->makeSkill('Symfony', 'symfony'));
        $entityManager->flush();
        $entityManager->persist($this->makeSkill('Symfony Framework', 'symfony'));

        $this->expectException(UniqueConstraintViolationException::class);
        $entityManager->flush();
    }

    public function testSkillNameMustBeUniqueInTheDatabase(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($this->makeSkill('Symfony', 'symfony'));
        $entityManager->flush();
        $entityManager->persist($this->makeSkill('Symfony', 'symfony-framework'));

        $this->expectException(UniqueConstraintViolationException::class);
        $entityManager->flush();
    }

    public function testUserEmailMustBeUniqueInTheDatabase(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($this->makeUser('same@example.test'));
        $entityManager->flush();
        $entityManager->persist($this->makeUser('same@example.test'));

        $this->expectException(UniqueConstraintViolationException::class);
        $entityManager->flush();
    }

    public function testUpgradePasswordPersistsTheNewHash(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $user = $this->makeUser();
        $entityManager->persist($user);
        $entityManager->flush();

        static::getContainer()->get(UserRepository::class)->upgradePassword($user, 'new-password-hash');
        $entityManager->clear();

        self::assertSame('new-password-hash', $entityManager->getRepository(User::class)
            ->findOneBy(['email' => 'viewer@example.test'])?->getPassword());
    }

    public function testUpgradePasswordRejectsUnsupportedUserType(): void
    {
        $this->expectException(UnsupportedUserException::class);

        static::getContainer()->get(UserRepository::class)->upgradePassword(
            new InMemoryUser('user', 'hash'),
            'new-hash',
        );
    }

    public function testDeletingCategorySetsSkillCategoryToNull(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $category = $this->makeCategory();
        $skill = $this->makeSkill(category: $category);
        $entityManager->persist($category);
        $entityManager->persist($skill);
        $entityManager->flush();
        $categoryId = $category->getId();
        $skillId = $skill->getId();

        $entityManager->remove($category);
        $entityManager->flush();
        $entityManager->clear();

        self::assertNull($entityManager->getRepository(Category::class)->find($categoryId));
        self::assertNull($entityManager->getRepository(Skill::class)->find($skillId)?->getCategory());
    }
}
