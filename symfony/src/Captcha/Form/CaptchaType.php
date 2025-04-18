<?php

declare(strict_types=1);

namespace App\Captcha\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<void>
 */
class CaptchaType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'error_bubbling' => false,
            'label' => 'Captcha!',
            'mapped' => false,
        ]);
    }
}
