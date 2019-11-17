<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Artisan;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtisanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('makerId')
            ->add('formerMakerIds')
            ->add('name')
            ->add('formerly')
            ->add('intro')
            ->add('since')
            ->add('country')
            ->add('state')
            ->add('city')
            ->add('productionModels')
            ->add('styles')
            ->add('otherStyles')
            ->add('orderTypes')
            ->add('otherOrderTypes')
            ->add('features')
            ->add('otherFeatures')
            ->add('paymentPlans')
            ->add('speciesDoes')
            ->add('speciesDoesnt')
            ->add('languages')
            ->add('notes')
            ->add('inactiveReason')
            ->add('contactAllowed')
            ->add('contactMethod')
            ->add('contactInfoObfuscated')
//            ->add('commissionsStatus')
//            ->add('privateData')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Artisan::class,
        ]);
    }
}
