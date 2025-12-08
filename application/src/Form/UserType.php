<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function __construct(private readonly Security $security)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new \LogicException();
        }

        $builder
            ->add('email', EmailType::class, [
                'attr'  => [
                    'class' => 'text-lowercase',
                ],
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[^\/\'%`\\\]+$/',
                        'message' => 'Bitte eine passende Email Adresse eintragen',
                    ]),
                ],
                'disabled' => !\in_array('ROLE_ADMIN', $user->getRoles(), true)
            ])
        ;
        if (\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $builder
                ->add('roles', ChoiceType::class, [
                    'choices' => User::ROLES,
                    'label' => 'Rolle',
                    'multiple' => true,
                    'expanded' => true,
                ]);
        }
        $builder
            ->add('password', PasswordType::class, [
                'required' => false,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
