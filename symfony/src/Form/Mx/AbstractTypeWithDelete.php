<?php

declare(strict_types=1);

namespace App\Form\Mx;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbstractTypeWithDelete extends AbstractType
{
    final public const string BTN_SAVE = 'save';
    final public const string BTN_DELETE = 'delete';

    final public const string OPT_DELETABLE = 'deletable';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(self::BTN_SAVE, SubmitType::class, [
            'attr' => ['class' => 'btn btn-primary'],
        ]);

        if (true === $options[self::OPT_DELETABLE]) {
            $builder->add(self::BTN_DELETE, SubmitType::class, [
                'attr' => [
                    'class'   => 'btn btn-danger',
                    'onclick' => 'return confirm("Delete?");',
                ],
            ]);
        }
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(self::OPT_DELETABLE)
            ->setAllowedTypes(self::OPT_DELETABLE, 'bool')
        ;
    }
}
