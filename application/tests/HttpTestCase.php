<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sends requests straight through the kernel and keeps cookies between them,
 * so functional tests work without symfony/browser-kit.
 */
abstract class HttpTestCase extends DatabaseTestCase
{
    private const HOST = 'http://localhost';

    /** @var array<string, string> */
    private array $cookies = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->cookies = [];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function request(string $method, string $uri, array $parameters = []): Response
    {
        // A matching Origin header satisfies the stateless CSRF check of forms and the login
        $request  = Request::create(self::HOST.$uri, $method, $parameters, $this->cookies, [], ['HTTP_ORIGIN' => self::HOST]);
        $response = self::$kernel->handle($request);
        self::$kernel->terminate($request, $response);

        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->isCleared()) {
                unset($this->cookies[$cookie->getName()]);
            } else {
                $this->cookies[$cookie->getName()] = (string) $cookie->getValue();
            }
        }

        return $response;
    }

    protected function logIn(string $email, string $password = 'password'): void
    {
        $response = $this->request('POST', '/login', [
            '_username' => $email,
            '_password' => $password,
        ]);

        self::assertRedirectsTo('/dashboard', $response);
    }

    /**
     * Reads the value of a hidden input (e.g. a CSRF token) from the rendered HTML.
     */
    protected function inputValue(Response $response, string $name): string
    {
        $pattern = '/<input[^>]*\sname="'.preg_quote($name, '/').'"[^>]*\svalue="([^"]*)"/';
        self::assertSame(1, preg_match($pattern, (string) $response->getContent(), $matches), sprintf('Input "%s" not found', $name));

        return html_entity_decode($matches[1]);
    }

    protected static function assertRedirectsTo(string $path, Response $response): void
    {
        self::assertTrue($response->isRedirect(), sprintf('Expected a redirect, got status %d', $response->getStatusCode()));
        // Security redirects are absolute URLs, controller redirects are paths
        $location = (string) $response->headers->get('Location');
        self::assertSame($path, str_starts_with($location, self::HOST) ? substr($location, \strlen(self::HOST)) : $location);
    }
}
