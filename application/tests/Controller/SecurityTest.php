<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\HttpTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class SecurityTest extends HttpTestCase
{
    /**
     * @return iterable<string, array{string}>
     */
    public static function protectedPages(): iterable
    {
        yield 'dashboard' => ['/dashboard'];
        yield 'profile' => ['/profile'];
        yield 'new signature' => ['/signature/new'];
        yield 'user admin' => ['/admin/user'];
    }

    #[DataProvider('protectedPages')]
    public function testAnonymousVisitorIsSentToLogin(string $uri): void
    {
        self::assertRedirectsTo('/login', $this->request('GET', $uri));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function publicPages(): iterable
    {
        yield 'landing page' => ['/'];
        yield 'login' => ['/login'];
        yield 'impress' => ['/impress'];
        yield 'data protection' => ['/data-protection'];
    }

    #[DataProvider('publicPages')]
    public function testPublicPageIsReachableWithoutLogin(string $uri): void
    {
        self::assertSame(200, $this->request('GET', $uri)->getStatusCode());
    }

    public function testLandingPageLinksToLogin(): void
    {
        $content = (string) $this->request('GET', '/')->getContent();

        self::assertStringContainsString('href="/login"', $content);
        self::assertStringContainsString('href="/impress"', $content);
    }

    public function testLoggedInUserSkipsLandingPage(): void
    {
        $this->createUser('max@example.com');
        $this->logIn('max@example.com');

        self::assertRedirectsTo('/dashboard', $this->request('GET', '/'));
    }

    public function testLoginPageIsPublic(): void
    {
        $response = $this->request('GET', '/login');

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('name="_username"', (string) $response->getContent());
        self::assertStringContainsString('name="_password"', (string) $response->getContent());
    }

    public function testUserCanLogInAndSeeHomepage(): void
    {
        $this->createUser('max@example.com', 'geheim123');

        $this->logIn('max@example.com', 'geheim123');

        self::assertSame(200, $this->request('GET', '/dashboard')->getStatusCode());
    }

    public function testWrongPasswordShowsError(): void
    {
        $this->createUser('max@example.com', 'geheim123');

        $response = $this->request('POST', '/login', ['_username' => 'max@example.com', '_password' => 'falsch']);
        self::assertRedirectsTo('/login', $response);

        $response = $this->request('GET', '/login');
        self::assertStringContainsString('alert-danger', (string) $response->getContent());
        self::assertStringContainsString('value="max@example.com"', (string) $response->getContent());
    }

    public function testLogoutEndsSession(): void
    {
        $this->createUser('max@example.com');
        $this->logIn('max@example.com');

        self::assertRedirectsTo('/', $this->request('GET', '/logout'));
        self::assertRedirectsTo('/login', $this->request('GET', '/dashboard'));
    }

    public function testRegularUserCannotOpenUserAdmin(): void
    {
        $this->createUser('max@example.com');
        $this->logIn('max@example.com');

        self::assertSame(403, $this->request('GET', '/admin/user')->getStatusCode());
    }

    public function testAdminCanOpenUserAdmin(): void
    {
        $this->createUser('admin@example.com', roles: ['ROLE_ADMIN']);
        $this->createUser('max@example.com');
        $this->logIn('admin@example.com');

        $response = $this->request('GET', '/admin/user');

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('max@example.com', (string) $response->getContent());
    }
}
