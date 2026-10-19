<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Tests\Support\DatabaseTestTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class UserManagementTest extends WebTestCase
{
    use DatabaseTestTrait;

    public function testUserManagementIsRestrictedToAdministrators(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $viewer = $this->makeUser('viewer@example.test', ['ROLE_USER']);
        $entityManager->persist($viewer);
        $entityManager->flush();
        $client->loginUser($viewer, 'main');

        $client->request('GET', '/dashboard/users');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdministratorCanCreateUserWithHashedPassword(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $admin = $this->makeUser('admin@example.test', ['ROLE_ADMIN']);
        $entityManager->persist($admin);
        $entityManager->flush();
        $client->loginUser($admin, 'main');

        $crawler = $client->request('GET', '/dashboard/users/new');
        $form = $crawler->selectButton('Create User')->form();
        $form['user[email]'] = 'new@example.test';
        $form['user[firstName]'] = 'New';
        $form['user[lastName]'] = 'Account';
        $form['user[role]'] = 'ROLE_EDITOR';
        $form['user[password]'] = 'plain-password';
        $form['user[isActive]']->tick();
        $client->submit($form);

        self::assertResponseRedirects('/dashboard/users');
        $createdUser = static::getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneBy(['email' => 'new@example.test']);
        self::assertNotNull($createdUser);
        self::assertNotSame('plain-password', $createdUser->getPassword());
        self::assertTrue(static::getContainer()
            ->get(UserPasswordHasherInterface::class)
            ->isPasswordValid($createdUser, 'plain-password'));
    }

    public function testBlankPasswordOnEditKeepsTheExistingPasswordHash(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $admin = $this->makeUser('admin@example.test', ['ROLE_ADMIN']);
        $user = $this->makeUser('member@example.test', ['ROLE_USER'], 'original-password');
        $entityManager->persist($admin);
        $entityManager->persist($user);
        $entityManager->flush();
        $userId = $user->getId();
        $oldHash = $user->getPassword();
        $client->loginUser($admin, 'main');

        $crawler = $client->request('GET', '/dashboard/users/'.$userId.'/edit');
        $form = $crawler->selectButton('Save Changes')->form();
        $form['user[email]'] = 'member@example.test';
        $form['user[firstName]'] = 'Portfolio';
        $form['user[lastName]'] = 'Tester';
        $form['user[role]'] = 'ROLE_USER';
        $form['user[password]'] = '';
        $form['user[isActive]']->tick();
        $client->submit($form);

        self::assertResponseRedirects('/dashboard/users');
        self::assertSame($oldHash, static::getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->find($userId)?->getPassword());
    }

    public function testMissingUserReturnsNotFound(): void
    {
        $client = static::createClient();
        $entityManager = $this->resetDatabase();
        $admin = $this->makeUser('admin@example.test', ['ROLE_ADMIN']);
        $entityManager->persist($admin);
        $entityManager->flush();
        $client->loginUser($admin, 'main');

        $client->request('GET', '/dashboard/users/999999/edit');

        self::assertResponseStatusCodeSame(404);
    }
}
