<?php

declare(strict_types=1);

namespace App\Form\Mx;

use App\Entity\Event;
use Override;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractTypeWithDelete
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('timestamp', DateTimeType::class, [
                'input'       => 'datetime_immutable',
                'label'       => 'Timestamp',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ])
            ->add('description', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    Event::TYPE_DATA_UPDATED => Event::TYPE_DATA_UPDATED,
                    Event::TYPE_GENERIC      => Event::TYPE_GENERIC,
                ],
                'expanded' => true,
            ])
            ->add('newMakersCount', NumberType::class, [
            ])
            ->add('updatedMakersCount', NumberType::class, [
            ])
            ->add('reportedUpdatedMakersCount', NumberType::class, [
            ])
            ->add('gitCommits', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
        ;
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
