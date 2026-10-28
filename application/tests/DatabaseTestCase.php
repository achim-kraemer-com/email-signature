<?php

declare(strict_types=1);

namespace App\Tests;

use App\Entity\Signature;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Boots the kernel and recreates the SQLite schema before every test.
 */
abstract class DatabaseTestCase extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();

        $entityManager = $this->entityManager();
        $metadata      = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool    = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    protected function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * @param list<string> $roles
     */
    protected function createUser(string $email, string $password = 'password', array $roles = ['ROLE_USER']): User
    {
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword($hasher->hashPassword($user, $password));

        $this->entityManager()->persist($user);
        $this->entityManager()->flush();

        return $user;
    }

    protected function createSignature(User $user, string $name = 'Business'): Signature
    {
        $signature = new Signature($user);
        $signature->setSignatureName($name);
        $signature->setFirstName('Max');
        $signature->setLastName('Mustermann');

        $this->entityManager()->persist($signature);
        $this->entityManager()->flush();

        return $signature;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T|null
     */
    protected function reload(string $class, string $id): ?object
    {
        $this->entityManager()->clear();

        return $this->entityManager()->find($class, $id);
    }
}
