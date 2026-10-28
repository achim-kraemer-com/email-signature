<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserTypeTest extends KernelTestCase
{
    public function testAdminCanEditEmailAndRoles(): void
    {
        $form = $this->createFormAs(['ROLE_ADMIN'], new User());

        self::assertTrue($form->has('roles'));
        self::assertFalse($form->get('email')->isDisabled());
        self::assertTrue($form->has('password'));
    }

    public function testUserCanNeitherChangeEmailNorRoles(): void
    {
        $form = $this->createFormAs(['ROLE_USER'], new User());

        self::assertFalse($form->has('roles'));
        self::assertTrue($form->get('email')->isDisabled());
    }

    public function testPasswordIsNotMappedToEntity(): void
    {
        $user = (new User())->setEmail('old@example.com')->setPassword('existing-hash');
        $form = $this->createFormAs(['ROLE_ADMIN'], $user);

        $form->submit(['email' => 'new@example.com', 'roles' => ['ROLE_USER'], 'password' => 'new-password']);

        self::assertTrue($form->isValid());
        self::assertSame('new@example.com', $user->getEmail());
        self::assertSame('existing-hash', $user->getPassword());
    }

    public function testEmailWithForbiddenCharactersIsInvalid(): void
    {
        $form = $this->createFormAs(['ROLE_ADMIN'], new User());

        $form->submit(['email' => "o'brien@example.com", 'roles' => ['ROLE_USER']]);

        self::assertFalse($form->isValid());
        self::assertStringContainsString('Bitte eine passende Email Adresse eintragen', (string) $form->get('email')->getErrors());
    }

    public function testFormRequiresLoggedInUser(): void
    {
        self::bootKernel();

        $this->expectException(\LogicException::class);

        static::getContainer()->get(FormFactoryInterface::class)->create(UserType::class, new User());
    }

    /**
     * @param list<string> $roles
     */
    private function createFormAs(array $roles, User $data): FormInterface
    {
        self::bootKernel();

        $currentUser = (new User())->setEmail('current@example.com')->setRoles($roles);
        static::getContainer()->get(TokenStorageInterface::class)
            ->setToken(new UsernamePasswordToken($currentUser, 'main', $currentUser->getRoles()));

        // CSRF needs a request, which a form built outside of HTTP doesn't have
        return static::getContainer()->get(FormFactoryInterface::class)
            ->create(UserType::class, $data, ['csrf_protection' => false]);
    }
}
