<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\DatabaseTestCase;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\InMemoryUser;

class UserRepositoryTest extends DatabaseTestCase
{
    public function testUpgradePasswordStoresNewHash(): void
    {
        $user = $this->createUser('max@example.com');

        static::getContainer()->get(UserRepository::class)->upgradePassword($user, 'new-hash');

        self::assertSame('new-hash', $this->reload(User::class, $user->getId())?->getPassword());
    }

    public function testUpgradePasswordRejectsForeignUserClasses(): void
    {
        $this->expectException(UnsupportedUserException::class);

        static::getContainer()->get(UserRepository::class)
            ->upgradePassword(new InMemoryUser('max', 'hash'), 'new-hash');
    }

    public function testEmailMustBeUnique(): void
    {
        $this->createUser('max@example.com');

        $this->expectException(UniqueConstraintViolationException::class);

        $this->createUser('max@example.com');
    }
}
