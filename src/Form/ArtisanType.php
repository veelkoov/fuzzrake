<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Artisan;
use App\Utils\Artisan\ContactPermit;
use App\Utils\Artisan\Features;
use App\Utils\Artisan\OrderTypes;
use App\Utils\Artisan\ProductionModels;
use App\Utils\Artisan\Styles;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
                'label'      => 'Former Maker IDs',
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
                'label'      => 'Species done',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('speciesDoesnt', TextareaType::class, [
                'label'      => 'Species not done',
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
            ->add('fursuitReviewUrl', UrlType::class, [
                'label'      => 'FursuitReview URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('websiteUrl', UrlType::class, [
                'label'      => 'Website URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('pricesUrl', UrlType::class, [
                'label'      => 'Prices URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('faqUrl', UrlType::class, [
                'label'      => 'FAQ URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('furAffinityUrl', UrlType::class, [
                'label'      => 'Fur Affinity URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('deviantArtUrl', UrlType::class, [
                'label'      => 'DeviantArt URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('twitterUrl', UrlType::class, [
                'label'      => 'Twitter URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('facebookUrl', UrlType::class, [
                'label'      => 'Facebook URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('tumblrUrl', UrlType::class, [
                'label'      => 'Tumblr URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('instagramUrl', UrlType::class, [
                'label'      => 'Instagram URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('youtubeUrl', UrlType::class, [
                'label'      => 'YouTube URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('queueUrl', UrlType::class, [
                'label'      => 'Queue URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('cstUrl', UrlType::class, [
                'label'      => 'CST URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('scritchUrl', UrlType::class, [
                'label'      => 'Scritch URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('scritchPhotoUrls', TextareaType::class, [
                'label'      => 'Scritch photos URLs',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('scritchMiniatureUrls', TextareaType::class, [
                'label'      => 'Scritch miniatures URLs',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('otherUrls', TextareaType::class, [
                'label'      => 'Other URLs',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('contactInfoOriginal', TextType::class, [
                'label'      => 'Original contact info',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('contactAllowed', ChoiceType::class, [
                'label'      => 'Contact allowed?',
                'choices'    => ContactPermit::getKeyKeyMap(),
                'empty_data' => ContactPermit::NO,
            ])
            ->add('passcode', PasswordType::class, [
                'label'      => 'New passcode',
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
