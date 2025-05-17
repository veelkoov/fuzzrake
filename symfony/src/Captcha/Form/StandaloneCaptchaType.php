<?php

declare(strict_types=1);

namespace App\Captcha\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<void>
 */
class StandaloneCaptchaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('captcha', CaptchaType::class);
    }
}
