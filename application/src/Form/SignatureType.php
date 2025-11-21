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
            ->add('firstName', TextType::class, [
                'label' => 'Vorname',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nachname',
            ])
            ->add('companyName', TextType::class, [
                'label' => 'Firmenname',
            ])
            ->add('jobTitle', TextType::class, [
                'label' => 'Jobtitel',
            ])
            ->add('street', TextType::class, [
                'label' => 'Strasse',
            ])
            ->add('houseNumber', TextType::class, [
                'label' => 'Hausnummer',
            ])
            ->add('postalCode', IntegerType::class, [
                'label' => 'PLZ',
            ])
            ->add('city', TextType::class, [
                'label' => 'Ort',
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
            ])
            ->add('phone', TextType::class, [
                'label' => 'Telefonnummer',
            ])
            ->add('fax', TextType::class, [
                'label' => 'Fax',
            ])
            ->add('website', TextType::class, [
                'label' => 'Webseite',
            ])
            ->add('instagram', TextType::class, [
                'label' => 'Instagram',
            ])
            ->add('facebook', TextType::class, [
                'label' => 'Facebook',
            ])
            ->add('linkedin', TextType::class, [
                'label' => 'LinkedIn',
            ])
            ->add('xing', TextType::class, [
                'label' => 'Xing',
            ])
            ->add('github', TextType::class, [
                'label' => 'Github',
            ])
            ->add('bank', TextType::class, [
                'label' => 'Bank',
            ])
            ->add('iban', TextType::class, [
                'label' => 'IBAN',
            ])
            ->add('bic', TextType::class, [
                'label' => 'BIC',
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
