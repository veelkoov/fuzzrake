<?php

declare(strict_types=1);

namespace App\Form;

use App\Service\Captcha;
use App\Service\CaptchaChallenge;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CaptchaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CaptchaChallenge::class,
        ])
            ->setRequired(['challenge'])
            ->setAllowedTypes('challenge', Captchachallenge::class);
    }
}
