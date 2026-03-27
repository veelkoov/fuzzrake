<?php

declare(strict_types=1);

namespace App\Form;

use App\Captcha\Form\CaptchaType;
use App\Entity\User;
use App\Security\Password;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<User>
 */
final class RegistrationFormType extends AbstractType
{
    public const string FLD_NEW_PASSWORD = 'newPassword';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'empty_data' => '',
            ])
            ->add(self::FLD_NEW_PASSWORD, RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'constraints' => Password::getConstraints(),
                    'label' => 'New password',
                ],
                'second_options' => [
                    'label' => 'Repeat password',
                ],
                'invalid_message' => 'The password fields must match.',
                'mapped' => false,
            ])
            ->add('captcha', CaptchaType::class)
        ;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
