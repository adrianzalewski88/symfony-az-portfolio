<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Tests\Support\DatabaseTestTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class CreateUserCommandTest extends KernelTestCase
{
    use DatabaseTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->resetDatabase();
    }

    public function testCommandCreatesAnAdministratorWithNormalizedEmailAndHashedPassword(): void
    {
        $command = $this->getCommandTester();
        $command->setInputs([
            '  ADMIN@EXAMPLE.TEST  ',
            'Ada',
            'Lovelace',
            'plain-password',
            'plain-password',
        ]);

        $status = $command->execute([]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertStringContainsString('created successfully', $command->getDisplay());

        $user = static::getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'admin@example.test']);

        self::assertNotNull($user);
        self::assertContains('ROLE_ADMIN', $user->getRoles());
        self::assertNotSame('plain-password', $user->getPassword());
        self::assertTrue(static::getContainer()
            ->get(UserPasswordHasherInterface::class)
            ->isPasswordValid($user, 'plain-password'));
    }

    public function testCommandRejectsAnExistingEmail(): void
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($this->makeUser('exists@example.test'));
        $entityManager->flush();

        $command = $this->getCommandTester();
        $command->setInputs(['exists@example.test']);

        $status = $command->execute([]);

        self::assertSame(Command::FAILURE, $status);
        self::assertStringContainsString('already exists', $command->getDisplay());
    }

    public function testCommandRejectsMismatchedPasswordConfirmation(): void
    {
        $command = $this->getCommandTester();
        $command->setInputs([
            'user@example.test',
            'Test',
            'User',
            'first-password',
            'different-password',
        ]);

        $status = $command->execute([]);

        self::assertSame(Command::INVALID, $status);
        self::assertNull(static::getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'user@example.test']));
    }

    #[DataProvider('missingRequiredAnswers')]
    public function testCommandRejectsMissingRequiredAnswers(array $answers): void
    {
        $command = $this->getCommandTester();
        $command->setInputs($answers);

        self::assertSame(Command::INVALID, $command->execute([]));
    }

    public static function missingRequiredAnswers(): iterable
    {
        yield 'blank email' => [[' ']];
        yield 'blank first name' => [['user@example.test', ' ']];
        yield 'blank last name' => [['user@example.test', 'Test', ' ']];
        yield 'blank password' => [['user@example.test', 'Test', 'User', '']];
    }

    private function getCommandTester(): CommandTester
    {
        $application = new Application(static::$kernel);

        return new CommandTester($application->find('app:create-user'));
    }
}
