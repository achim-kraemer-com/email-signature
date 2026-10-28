<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Entity\Signature;
use App\Entity\User;
use App\Form\SignatureType;
use Symfony\Component\Form\Test\TypeTestCase;

class SignatureTypeTest extends TypeTestCase
{
    public function testSubmittedDataIsMappedToSignature(): void
    {
        $signature = new Signature(new User());
        $form      = $this->factory->create(SignatureType::class, $signature);

        $form->submit([
            'signatureName' => 'Business',
            'welcome'       => 'Liebe Grüße',
            'firstName'     => 'Max',
            'lastName'      => 'Mustermann',
            'postalCode'    => '12345',
            'city'          => 'Berlin',
            'email'         => 'max@example.com',
            'iban'          => 'DE02120300000000202051',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertSame('Business', $signature->getSignatureName());
        self::assertSame('Liebe Grüße', $signature->getWelcome());
        self::assertSame('Max', $signature->getFirstName());
        self::assertSame('Mustermann', $signature->getLastName());
        self::assertSame(12345, $signature->getPostalCode());
        self::assertSame('Berlin', $signature->getCity());
        self::assertSame('max@example.com', $signature->getEmail());
        self::assertSame('DE02120300000000202051', $signature->getIban());
    }

    public function testNonNumericPostalCodeIsRejected(): void
    {
        $form = $this->factory->create(SignatureType::class, new Signature(new User()));

        $form->submit(['postalCode' => 'abc']);

        self::assertFalse($form->get('postalCode')->isSynchronized());
    }

    public function testLogoIsNotMappedToEntity(): void
    {
        $signature = (new Signature(new User()))->setLogo('/uploads/images/existing.png');
        $form      = $this->factory->create(SignatureType::class, $signature);

        $form->submit(['signatureName' => 'Business', 'firstName' => 'Max', 'lastName' => 'Mustermann', 'logo' => null]);

        self::assertFalse($form->get('logo')->getConfig()->getMapped());
        self::assertSame('/uploads/images/existing.png', $signature->getLogo());
    }
}
