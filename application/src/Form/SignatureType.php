<?php

namespace App\Form;

use App\Entity\Signature;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('signatureName', TextType::class, [
                'label' => 'Name der Signatur',
            ])
            ->add('welcome', TextType::class, [
                'label' => 'Begrüßung',
                'attr' => [
                    'placeholder' => 'z.B. Liebe Grüße',
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Vorname',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nachname',
            ])
            ->add('companyName', TextType::class, [
                'label' => 'Firmenname',
                'required' => false,
            ])
            ->add('owner', TextType::class, [
                'label' => 'Inhaber',
                'required' => false,
            ])
            ->add('tagNumber', TextType::class, [
                'label' => 'Steuer-Nummer',
                'required' => false,
            ])
            ->add('jobTitle', TextType::class, [
                'label' => 'Jobtitel',
                'required' => false,
            ])
            ->add('street', TextType::class, [
                'label' => 'Strasse',
                'required' => false,
            ])
            ->add('houseNumber', TextType::class, [
                'label' => 'Hausnummer',
                'required' => false,
            ])
            ->add('postalCode', IntegerType::class, [
                'label' => 'PLZ',
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'label' => 'Ort',
                'required' => false,
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
                'required' => false,
            ])
            ->add('phone', TextType::class, [
                'label' => 'Telefonnummer',
                'required' => false,
            ])
            ->add('mobile', TextType::class, [
                'label' => 'Handynummer',
                'required' => false,
            ])
            ->add('fax', TextType::class, [
                'label' => 'Fax',
                'required' => false,
            ])
            ->add('website', TextType::class, [
                'label' => 'Webseite',
                'required' => false,
            ])
            ->add('instagram', TextType::class, [
                'label' => 'Instagram URL',
                'required' => false,
            ])
            ->add('facebook', TextType::class, [
                'label' => 'Facebook URL',
                'required' => false,
            ])
            ->add('linkedin', TextType::class, [
                'label' => 'LinkedIn URL',
                'required' => false,
            ])
            ->add('xing', TextType::class, [
                'label' => 'Xing URL',
                'required' => false,
            ])
            ->add('github', TextType::class, [
                'label' => 'Github URL',
                'required' => false,
            ])
            ->add('bank', TextType::class, [
                'label' => 'Bank',
                'required' => false,
            ])
            ->add('iban', TextType::class, [
                'label' => 'IBAN',
                'required' => false,
            ])
            ->add('bic', TextType::class, [
                'label' => 'BIC',
                'required' => false,
            ])
            ->add('logo', FileType::class, [
                'label'      => 'Logo',
                'mapped'     => false,
                'required'   => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Signature::class,
        ]);
    }
}
