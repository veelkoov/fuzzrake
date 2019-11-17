<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Artisan;
use App\Utils\Artisan\Features;
use App\Utils\Artisan\OrderTypes;
use App\Utils\Artisan\ProductionModels;
use App\Utils\Artisan\Styles;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtisanType extends AbstractType
{
    const BTN_SAVE = 'save';
    const BTN_DELETE = 'delete';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('makerId', TextType::class, [
                'label'      => 'Maker ID',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('formerMakerIds', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('name')
            ->add('formerly', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('intro', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('since', TextType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('country', TextType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('state', TextType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('city', TextType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('productionModels', ChoiceType::class, [
                'required' => false,
                'choices'  => ProductionModels::getValues(),
                'multiple' => true,
            ])
            ->add('styles', ChoiceType::class, [
                'required' => false,
                'choices'  => Styles::getValues(),
                'multiple' => true,
            ])
            ->add('otherStyles', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('orderTypes', ChoiceType::class, [
                'required' => false,
                'choices'  => OrderTypes::getValues(),
                'multiple' => true,
            ])
            ->add('otherOrderTypes', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('features', ChoiceType::class, [
                'required' => false,
                'choices'  => Features::getValues(),
                'multiple' => true,
            ])
            ->add('otherFeatures', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('paymentPlans', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('speciesDoes', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('speciesDoesnt', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('languages', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('notes', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('inactiveReason', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add(self::BTN_SAVE, SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->add(self::BTN_DELETE, SubmitType::class, [
                'attr' => [
                    'class'   => 'btn btn-danger',
                    'onclick' => 'return confirm("Delete?");',
                ],
            ])
        ;

        foreach (['productionModels', 'styles', 'orderTypes', 'features'] as $fieldName) {
            $builder->get($fieldName)->addModelTransformer(StringArrayTransformer::getInstance());
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Artisan::class,
        ]);
    }
}
