<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\HttpTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileControllerTest extends HttpTestCase
{
    public function testProfileShowsReadOnlyEmailForRegularUser(): void
    {
        $this->createUser('max@example.com');
        $this->logIn('max@example.com');

        $response = $this->request('GET', '/profile');

        self::assertSame(200, $response->getStatusCode());
        self::assertMatchesRegularExpression('/name="user\[email\]"[^>]*disabled/', (string) $response->getContent());
        self::assertStringNotContainsString('user[roles]', (string) $response->getContent());
    }

    public function testUserChangesOwnPassword(): void
    {
        $user = $this->createUser('max@example.com', 'alt12345');
        $this->logIn('max@example.com', 'alt12345');

        $form     = $this->request('GET', '/profile');
        $response = $this->request('POST', '/profile', ['user' => [
            'password' => 'neu12345',
            '_token'   => $this->inputValue($form, 'user[_token]'),
        ]]);

        self::assertRedirectsTo('/', $response);
        $reloaded = $this->reload(User::class, $user->getId());
        self::assertTrue(static::getContainer()->get(UserPasswordHasherInterface::class)->isPasswordValid($reloaded, 'neu12345'));
    }

    public function testUserCannotChangeOwnEmail(): void
    {
        $user = $this->createUser('max@example.com', 'alt12345');
        $this->logIn('max@example.com', 'alt12345');

        $form = $this->request('GET', '/profile');
        $this->request('POST', '/profile', ['user' => [
            'email'    => 'hacker@example.com',
            'password' => 'neu12345',
            '_token'   => $this->inputValue($form, 'user[_token]'),
        ]]);

        self::assertSame('max@example.com', $this->reload(User::class, $user->getId())?->getEmail());
    }

    public function testSavingProfileWithoutPasswordKeepsOldPassword(): void
    {
        $user = $this->createUser('max@example.com', 'alt12345');
        $this->logIn('max@example.com', 'alt12345');

        $form     = $this->request('GET', '/profile');
        $response = $this->request('POST', '/profile', ['user' => [
            '_token' => $this->inputValue($form, 'user[_token]'),
        ]]);

        self::assertRedirectsTo('/', $response);
        $reloaded = $this->reload(User::class, $user->getId());
        self::assertTrue(static::getContainer()->get(UserPasswordHasherInterface::class)->isPasswordValid($reloaded, 'alt12345'));
    }
}
