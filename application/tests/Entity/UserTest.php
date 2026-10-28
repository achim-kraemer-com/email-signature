<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Signature;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class UserTest extends TestCase
{
    public function testNewUserGetsUuidAndTimestamps(): void
    {
        $user = new User();

        self::assertTrue(Uuid::isValid($user->getId()));
        self::assertSame($user->getId(), $user->getUuid()->toString());
        self::assertEqualsWithDelta(time(), $user->getCreatedAt()->getTimestamp(), 5);
        self::assertEqualsWithDelta(time(), $user->getUpdatedAt()->getTimestamp(), 5);
    }

    public function testEmailIsUserIdentifierAndStringRepresentation(): void
    {
        $user = (new User())->setEmail('max@example.com');

        self::assertSame('max@example.com', $user->getUserIdentifier());
        self::assertSame('max@example.com', (string) $user);
    }

    public function testEveryUserHasRoleUser(): void
    {
        self::assertSame(['ROLE_USER'], (new User())->getRoles());
    }

    public function testRolesAreNotDuplicated(): void
    {
        $user = (new User())->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        self::assertSame(['ROLE_ADMIN', 'ROLE_USER'], array_values($user->getRoles()));
    }

    public function testSerializedUserDoesNotContainPasswordHash(): void
    {
        $user = (new User())->setEmail('max@example.com')->setPassword('$2y$04$secret-hash');

        $data = $user->__serialize();

        self::assertSame(hash('crc32c', '$2y$04$secret-hash'), $data["\0".User::class."\0password"]);
        self::assertStringNotContainsString('secret-hash', serialize($user));
    }

    public function testAddSignatureLinksBothSidesOnlyOnce(): void
    {
        $user      = new User();
        $owner     = new User();
        $signature = new Signature($owner);

        $user->addSignature($signature);
        $user->addSignature($signature);

        self::assertCount(1, $user->getSignatures());
        self::assertSame($user, $signature->getUser());
    }

    public function testRemoveSignatureUnlinksIt(): void
    {
        $user      = new User();
        $signature = new Signature($user);
        $user->addSignature($signature);

        $user->removeSignature($signature);

        self::assertCount(0, $user->getSignatures());
        self::assertNull($signature->getUser());
    }
}
