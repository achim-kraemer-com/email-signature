<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\DatabaseTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserCommandTest extends DatabaseTestCase
{
    public function testCreatesUserWithHashedPassword(): void
    {
        $tester = new CommandTester((new Application(self::$kernel))->find('app:create-user'));

        $tester->execute(['email' => 'new@example.com', 'password' => 'geheim123']);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('User new@example.com successfully created!', $tester->getDisplay());

        $this->entityManager()->clear();
        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'new@example.com']);

        self::assertInstanceOf(User::class, $user);
        self::assertSame(['ROLE_USER'], $user->getRoles());
        self::assertNotSame('geheim123', $user->getPassword());
        self::assertTrue(static::getContainer()->get(UserPasswordHasherInterface::class)->isPasswordValid($user, 'geheim123'));
    }

    public function testAliasIsRegistered(): void
    {
        $command = (new Application(self::$kernel))->find('app:add-user');

        self::assertSame('app:create-user', $command->getName());
    }

    public function testFailsWithoutArguments(): void
    {
        $tester = new CommandTester((new Application(self::$kernel))->find('app:create-user'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        self::assertNotSame(Command::SUCCESS, $tester->execute([]));
    }
}
