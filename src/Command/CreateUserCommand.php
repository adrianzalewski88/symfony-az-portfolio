<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a portfolio dashboard user.',
)]
class CreateUserCommand
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $io->title('AZ Portfolio User Creation');

        $email = strtolower(trim($io->ask(
            'Email address'
        )));

        if ($email === '') {
            $io->error('Email address is required.');

            return Command::INVALID;
        }

        if ($this->userRepository->findOneBy(['email' => $email])) {
            $io->error(sprintf(
                'A user with the email "%s" already exists.',
                $email
            ));

            return Command::FAILURE;
        }

        $firstName = trim($io->ask(
            'First name'
        ));

        if ($firstName === '') {
            $io->error('First name is required.');

            return Command::INVALID;
        }

        $lastName = trim($io->ask(
            'Last name'
        ));

        if ($lastName === '') {
            $io->error('Last name is required.');

            return Command::INVALID;
        }

        $password = $io->askHidden(
            'Password'
        );

        if ($password === null || $password === '') {
            $io->error('Password is required.');

            return Command::INVALID;
        }

        $confirmation = $io->askHidden(
            'Confirm password'
        );

        if ($password !== $confirmation) {
            $io->error('Passwords do not match.');

            return Command::INVALID;
        }

        $user = new User();

        $user
            ->setEmail($email)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRoles(['ROLE_ADMIN'])
            ->setIsActive(true)
            ->setPassword(
                $this->passwordHasher->hashPassword(
                    $user,
                    $password
                )
            );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf(
            'User "%s %s" (%s) was created successfully.',
            $firstName,
            $lastName,
            $email
        ));

        return Command::SUCCESS;
    }
}