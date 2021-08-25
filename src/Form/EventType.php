<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public const BTN_SAVE = 'save';
    public const BTN_DELETE = 'delete';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('timestamp', DateTimeType::class, [
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
            ->add(self::BTN_SAVE, SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;

        if (null !== $builder->getData()->getId()) {
            $builder->add(self::BTN_DELETE, SubmitType::class, [
                'attr' => [
                    'class'   => 'btn btn-danger',
                    'onclick' => 'return confirm("Delete?");',
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
