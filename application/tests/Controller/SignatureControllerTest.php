<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Signature;
use App\Repository\SignatureRepository;
use App\Tests\HttpTestCase;

class SignatureControllerTest extends HttpTestCase
{
    public function testHomepageListsOnlyOwnSignatures(): void
    {
        $max  = $this->createUser('max@example.com');
        $erika = $this->createUser('erika@example.com');
        $this->createSignature($max, 'Max Business');
        $this->createSignature($erika, 'Erika Privat');
        $this->logIn('max@example.com');

        $content = (string) $this->request('GET', '/')->getContent();

        self::assertStringContainsString('Max Business', $content);
        self::assertStringNotContainsString('Erika Privat', $content);
    }

    public function testCreateSignatureForLoggedInUser(): void
    {
        $user = $this->createUser('max@example.com');
        $this->logIn('max@example.com');

        $form     = $this->request('GET', '/signature/new');
        $response = $this->request('POST', '/signature/new', ['signature' => [
            'signatureName' => 'Business',
            'welcome'       => 'Liebe Grüße',
            'firstName'     => 'Max',
            'lastName'      => 'Mustermann',
            'companyName'   => 'Muster GmbH',
            'postalCode'    => '10115',
            '_token'        => $this->inputValue($form, 'signature[_token]'),
        ]]);

        self::assertRedirectsTo('/', $response);

        $this->entityManager()->clear();
        $signatures = static::getContainer()->get(SignatureRepository::class)->findAll();
        self::assertCount(1, $signatures);
        self::assertSame('Business', $signatures[0]->getSignatureName());
        self::assertSame(10115, $signatures[0]->getPostalCode());
        self::assertSame($user->getId(), $signatures[0]->getUser()?->getId());
    }

    public function testInvalidSignatureIsNotSaved(): void
    {
        $this->createUser('max@example.com');
        $this->logIn('max@example.com');

        $form     = $this->request('GET', '/signature/new');
        $response = $this->request('POST', '/signature/new', ['signature' => [
            'signatureName' => 'Business',
            'firstName'     => 'Max',
            'lastName'      => 'Mustermann',
            'postalCode'    => 'keine Zahl',
            '_token'        => $this->inputValue($form, 'signature[_token]'),
        ]]);

        self::assertSame(422, $response->getStatusCode());
        self::assertCount(0, static::getContainer()->get(SignatureRepository::class)->findAll());
    }

    public function testShowSignature(): void
    {
        $signature = $this->createSignature($this->createUser('max@example.com'));
        $this->logIn('max@example.com');

        $response = $this->request('GET', '/signature/'.$signature->getId());

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Mustermann', (string) $response->getContent());
    }

    public function testUnknownSignatureReturns404(): void
    {
        $this->createUser('max@example.com');
        $this->logIn('max@example.com');

        self::assertSame(404, $this->request('GET', '/signature/00000000-0000-4000-8000-000000000000')->getStatusCode());
    }

    public function testEditSignature(): void
    {
        $signature = $this->createSignature($this->createUser('max@example.com'));
        $this->logIn('max@example.com');

        $form     = $this->request('GET', '/signature/'.$signature->getId().'/edit');
        $response = $this->request('POST', '/signature/'.$signature->getId().'/edit', ['signature' => [
            'signatureName' => 'Privat',
            'firstName'     => 'Max',
            'lastName'      => 'Mustermann',
            'city'          => 'Hamburg',
            '_token'        => $this->inputValue($form, 'signature[_token]'),
        ]]);

        self::assertRedirectsTo('/', $response);

        $reloaded = $this->reload(Signature::class, $signature->getId());
        self::assertSame('Privat', $reloaded?->getSignatureName());
        self::assertSame('Hamburg', $reloaded?->getCity());
    }

    public function testDeleteSignatureWithValidToken(): void
    {
        $signature = $this->createSignature($this->createUser('max@example.com'));
        $this->logIn('max@example.com');

        $edit     = $this->request('GET', '/signature/'.$signature->getId().'/edit');
        $response = $this->request('POST', '/signature/'.$signature->getId(), ['_token' => $this->inputValue($edit, '_token')]);

        self::assertRedirectsTo('/', $response);
        self::assertNull($this->reload(Signature::class, $signature->getId()));
    }

    public function testDeleteSignatureWithInvalidTokenKeepsIt(): void
    {
        $signature = $this->createSignature($this->createUser('max@example.com'));
        $this->logIn('max@example.com');

        $response = $this->request('POST', '/signature/'.$signature->getId(), ['_token' => 'invalid']);

        self::assertRedirectsTo('/', $response);
        self::assertNotNull($this->reload(Signature::class, $signature->getId()));
    }

    public function testUserCannotOpenForeignSignature(): void
    {
        $signature = $this->createSignature($this->createUser('erika@example.com'));
        $this->createUser('max@example.com');
        $this->logIn('max@example.com');

        self::assertContains($this->request('GET', '/signature/'.$signature->getId())->getStatusCode(), [403, 404]);
        self::assertContains($this->request('GET', '/signature/'.$signature->getId().'/edit')->getStatusCode(), [403, 404]);
    }

    public function testSignatureIndexDoesNotListForeignSignatures(): void
    {
        $this->createSignature($this->createUser('erika@example.com'), 'Erika Privat');
        $this->createUser('max@example.com');
        $this->logIn('max@example.com');

        self::assertStringNotContainsString('Erika Privat', (string) $this->request('GET', '/signature')->getContent());
    }
}
