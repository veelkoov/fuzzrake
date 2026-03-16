<?php

declare(strict_types=1);

namespace App\Form;

use App\Captcha\Form\CaptchaType;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ResetPasswordRequestFormType extends AbstractType
{
    public const string FLD_EMAIL = 'email';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(self::FLD_EMAIL, EmailType::class, [
                'attr' => ['autocomplete' => 'email'],
                'help' => 'Enter your email address to receive a message with a link to reset your password.',
                'constraints' => [new NotBlank(message: 'Please enter your email')],
            ])
            ->add('captcha', CaptchaType::class)
        ;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
