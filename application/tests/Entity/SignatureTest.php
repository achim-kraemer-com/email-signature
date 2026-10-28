<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Signature;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class SignatureTest extends TestCase
{
    public function testSignatureBelongsToUserPassedToConstructor(): void
    {
        $user      = new User();
        $signature = new Signature($user);

        self::assertSame($user, $signature->getUser());
        self::assertNotSame($user->getId(), $signature->getId());
    }

    public function testOptionalFieldsAreEmptyByDefault(): void
    {
        $signature = new Signature(new User());

        self::assertNull($signature->getCompanyName());
        self::assertNull($signature->getPostalCode());
        self::assertNull($signature->getLogo());
        self::assertNull($signature->getWelcome());
    }

    public function testSettersAreFluentAndStoreValues(): void
    {
        $signature = (new Signature(new User()))
            ->setSignatureName('Business')
            ->setFirstName('Max')
            ->setLastName('Mustermann')
            ->setPostalCode(12345)
            ->setIban('DE02120300000000202051')
            ->setLogo('/uploads/images/logo.png');

        self::assertSame('Business', $signature->getSignatureName());
        self::assertSame('Max', $signature->getFirstName());
        self::assertSame('Mustermann', $signature->getLastName());
        self::assertSame(12345, $signature->getPostalCode());
        self::assertSame('DE02120300000000202051', $signature->getIban());
        self::assertSame('/uploads/images/logo.png', $signature->getLogo());
    }
}
