<?php

declare(strict_types=1);

namespace App\Form;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ChangeEmailFormType extends AbstractType
{
    public const string FLD_PASSWORD = 'password';
    public const string FLD_NEW_EMAIL = 'newEmail';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(self::FLD_PASSWORD, PasswordType::class, [
                'label' => 'Your password',
                'mapped' => false,
                'required' => true,
            ])
            ->add(self::FLD_NEW_EMAIL, EmailType::class, [
                'label' => 'New email',
                'mapped' => false,
                'required' => true,
            ])
        ;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
