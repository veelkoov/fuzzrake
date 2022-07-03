<?php

declare(strict_types=1);

namespace App\Form\Mx;

use App\DataDefinitions\Ages;
use App\DataDefinitions\ContactPermit;
use App\DataDefinitions\Features;
use App\DataDefinitions\OrderTypes;
use App\DataDefinitions\ProductionModels;
use App\DataDefinitions\Styles;
use App\Form\AgesTransformer;
use App\Form\BooleanTransformer;
use App\Form\StringArrayTransformer;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtisanType extends AbstractType
{
    final public const BTN_SAVE = 'save';
    final public const BTN_DELETE = 'delete';

    public function buildForm(FormBuilderInterface $builder, array $options): void
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
            ->add('name', TextType::class, [
                'required'   => true,
                'empty_data' => '',
            ])
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
            ->add('productionModelsComment', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
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
            ->add('stylesComment', TextareaType::class, [
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
            ->add('orderTypesComment', TextareaType::class, [
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
            ->add('featuresComment', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('paymentPlans', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('currenciesAccepted', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('paymentMethods', TextareaType::class, [
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
            ->add('speciesComment', TextareaType::class, [
                'label'      => 'Species comment',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('languages', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('ages', ChoiceType::class, [
                'label'    => 'Age',
                'required' => true,
                'choices'  => Ages::getChoices(true),
                'expanded' => true,
            ])
            ->add('worksWithMinors', ChoiceType::class, [
                'label'    => 'Works with minors?',
                'required' => true,
                'choices'  => ['Yes' => 'YES', 'No' => 'NO', 'Unknown' => null],
                'expanded' => true,
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
            ->add('pricesUrls', TextareaType::class, [
                'label'      => 'Prices URLs',
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
            ->add('commissionsUrls', TextareaType::class, [
                'label'      => 'Commissions URLs',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('scritchUrl', UrlType::class, [
                'label'      => 'Scritch URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('linklistUrl', UrlType::class, [
                'label'      => 'Link list URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('furryAminoUrl', UrlType::class, [
                'label'      => 'Furry Amino URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('theDealersDenUrl', UrlType::class, [
                'label'      => 'The Dealers Den URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('etsyUrl', UrlType::class, [
                'label'      => 'Etsy URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('furtrackUrl', UrlType::class, [
                'label'      => 'Furtrack URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('photoUrls', TextareaType::class, [
                'label'      => 'Scritch photos URLs',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('miniatureUrls', TextareaType::class, [
                'label'      => 'Miniatures URLs',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('otherUrls', TextareaType::class, [
                'label'      => 'Other URLs',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('otherShopUrl', UrlType::class, [
                'label'      => 'Other shop URL',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('contactInfoOriginal', TextType::class, [
                'label'      => 'Original contact info',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('contactInfoObfuscated', TextType::class, [
                'label'      => 'Obfuscated contact info',
                'required'   => false,
                'empty_data' => '',
                'help'       => 'Leave unchanged for automated updates of contact fields based on "original". Introduce any change to suppress automation and customize obfuscated info.',
            ])
            ->add('contactAllowed', ChoiceType::class, [
                'label'      => 'Contact allowed?',
                'choices'    => ContactPermit::getKeyKeyMap(),
                'empty_data' => ContactPermit::NO,
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
        $builder->get('worksWithMinors')->addModelTransformer(BooleanTransformer::getInstance());
        $builder->get('ages')->addModelTransformer(AgesTransformer::getInstance());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artisan::class,
        ]);
    }
}
