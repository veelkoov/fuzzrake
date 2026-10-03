<?php

declare(strict_types=1);

namespace App\Form\Mx;

use App\Entity\Label;
use Override;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractTypeWithDelete<Label>
 */
class LabelType extends AbstractTypeWithDelete
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('type')
            ->add('value', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('comment', TextareaType::class, [
                'required' => false,
                'empty_data' => '',
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
            ])
        ;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Label::class,
        ]);
    }
}
