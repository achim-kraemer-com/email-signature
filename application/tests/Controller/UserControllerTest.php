<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\HttpTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createUser('admin@example.com', roles: ['ROLE_ADMIN']);
        $this->logIn('admin@example.com');
    }

    public function testAdminCreatesUser(): void
    {
        $form     = $this->request('GET', '/admin/user/new');
        $response = $this->request('POST', '/admin/user/new', ['user' => [
            'email'    => 'max@example.com',
            'roles'    => ['ROLE_USER'],
            'password' => 'geheim123',
            '_token'   => $this->inputValue($form, 'user[_token]'),
        ]]);

        self::assertRedirectsTo('/admin/user', $response);

        $this->entityManager()->clear();
        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'max@example.com']);
        self::assertInstanceOf(User::class, $user);
        self::assertTrue(static::getContainer()->get(UserPasswordHasherInterface::class)->isPasswordValid($user, 'geheim123'));
    }

    public function testNewUserRequiresPassword(): void
    {
        $form     = $this->request('GET', '/admin/user/new');
        $response = $this->request('POST', '/admin/user/new', ['user' => [
            'email'  => 'max@example.com',
            'roles'  => ['ROLE_USER'],
            '_token' => $this->inputValue($form, 'user[_token]'),
        ]]);

        self::assertSame(422, $response->getStatusCode());
        self::assertStringContainsString('Bitte ein Passwort eingeben', (string) $response->getContent());
        self::assertNull(static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'max@example.com']));
    }

    public function testAdminChangesPassword(): void
    {
        $user = $this->createUser('max@example.com', 'alt12345');

        $form     = $this->request('GET', '/admin/user/'.$user->getId().'/edit');
        $response = $this->request('POST', '/admin/user/'.$user->getId().'/edit', ['user' => [
            'email'    => 'max@example.com',
            'roles'    => ['ROLE_USER'],
            'password' => 'neu12345',
            '_token'   => $this->inputValue($form, 'user[_token]'),
        ]]);

        self::assertRedirectsTo('/admin/user', $response);
        $reloaded = $this->reload(User::class, $user->getId());
        self::assertTrue(static::getContainer()->get(UserPasswordHasherInterface::class)->isPasswordValid($reloaded, 'neu12345'));
    }

    public function testAdminEditsUserWithoutChangingPassword(): void
    {
        $user = $this->createUser('max@example.com', 'alt12345');

        $form     = $this->request('GET', '/admin/user/'.$user->getId().'/edit');
        $response = $this->request('POST', '/admin/user/'.$user->getId().'/edit', ['user' => [
            'email'  => 'max@example.com',
            'roles'  => ['ROLE_ADMIN'],
            '_token' => $this->inputValue($form, 'user[_token]'),
        ]]);

        self::assertRedirectsTo('/admin/user', $response);
        $reloaded = $this->reload(User::class, $user->getId());
        self::assertContains('ROLE_ADMIN', $reloaded?->getRoles());
        self::assertTrue(static::getContainer()->get(UserPasswordHasherInterface::class)->isPasswordValid($reloaded, 'alt12345'));
    }

    public function testAdminDeletesUser(): void
    {
        $user = $this->createUser('max@example.com');

        $show     = $this->request('GET', '/admin/user/'.$user->getId());
        $response = $this->request('POST', '/admin/user/'.$user->getId(), ['_token' => $this->inputValue($show, '_token')]);

        self::assertRedirectsTo('/admin/user', $response);
        self::assertNull($this->reload(User::class, $user->getId()));
    }
}
