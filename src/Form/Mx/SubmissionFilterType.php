<?php

declare(strict_types=1);

namespace App\Form\Mx;

use App\Data\Submission\Filter;
use App\Data\Submission\Status;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubmissionFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('statuses', EnumType::class, [
                'class' => Status::class,
                'expanded' => true,
                'multiple' => true,
                'choice_label' => 'label',
            ])
            ->add('update', ChoiceType::class, [
                'choices' => [
                    'Both' => null,
                    'Updates' => true,
                    'Additions' => false,
                ],
                'label' => 'Kinds of submission',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
        ]);
    }
}
