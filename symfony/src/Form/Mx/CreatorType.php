<?php

declare(strict_types=1);

namespace App\Form\Mx;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Features;
use App\Data\Definitions\OrderTypes;
use App\Data\Definitions\ProductionModels;
use App\Data\Definitions\Styles;
use App\Form\Transformers\AgesTransformer;
use App\Form\Transformers\BooleanTransformer;
use App\Form\Transformers\ContactPermitTransformer;
use App\Form\Transformers\StringListAsCheckBoxesTransformer;
use App\Form\Transformers\StringListAsTextareaTransformer;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Override;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreatorType extends AbstractTypeWithDelete
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('name', TextType::class, [
                'required'   => true,
                'empty_data' => '',
            ])
            ->add('formerly', TextareaType::class, [
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
            ->add('paymentPlans', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('pricesUrls', TextareaType::class, [
                'label'      => 'Prices URLs',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('paymentMethods', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('currenciesAccepted', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('productionModels', ChoiceType::class, [
                'required' => false,
                'choices'  => ProductionModels::getFormChoices(),
                'multiple' => true,
            ])
            ->add('productionModelsComment', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('styles', ChoiceType::class, [
                'required' => false,
                'choices'  => Styles::getFormChoices(),
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
                'choices'  => OrderTypes::getFormChoices(),
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
                'choices'  => Features::getFormChoices(),
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
            ->add('fursuitReviewUrl', UrlType::class, [
                'label'            => 'FursuitReview URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('websiteUrl', UrlType::class, [
                'label'            => 'Website URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('faqUrl', UrlType::class, [
                'label'            => 'FAQ URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('queueUrl', UrlType::class, [
                'label'            => 'Queue URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('furAffinityUrl', UrlType::class, [
                'label'            => 'Fur Affinity URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('deviantArtUrl', UrlType::class, [
                'label'            => 'DeviantArt URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('mastodonUrl', UrlType::class, [
                'label'            => 'Mastodon URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('telegramChannelUrl', UrlType::class, [
                'label'            => 'Telegram channel URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('blueskyUrl', UrlType::class, [
                'label'            => 'Bluesky URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('tikTokUrl', UrlType::class, [
                'label'            => 'TikTok URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('donationsUrl', UrlType::class, [
                'label'            => 'Donations URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('twitterUrl', UrlType::class, [
                'label'            => 'Twitter URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('facebookUrl', UrlType::class, [
                'label'            => 'Facebook URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('tumblrUrl', UrlType::class, [
                'label'            => 'Tumblr URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('instagramUrl', UrlType::class, [
                'label'            => 'Instagram URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('youtubeUrl', UrlType::class, [
                'label'            => 'YouTube URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('etsyUrl', UrlType::class, [
                'label'            => 'Etsy URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('theDealersDenUrl', UrlType::class, [
                'label'            => 'The Dealers Den URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('otherShopUrl', UrlType::class, [
                'label'            => 'Other shop URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('linklistUrl', UrlType::class, [
                'label'            => 'Link list URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('furryAminoUrl', UrlType::class, [
                'label'            => 'Furry Amino URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('otherUrls', TextareaType::class, [
                'label'      => 'Other URLs',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('commissionsUrls', TextareaType::class, [
                'label'      => 'Commissions URLs',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('scritchUrl', UrlType::class, [
                'label'            => 'Scritch URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
            ])
            ->add('furtrackUrl', UrlType::class, [
                'label'            => 'Furtrack URL',
                'required'         => false,
                'empty_data'       => '',
                'default_protocol' => 'https',
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
            ->add('languages', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('creatorId', TextType::class, [
                'label'      => 'Creator ID',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('formerCreatorIds', TextareaType::class, [
                'label'      => 'Former creator IDs',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('intro', TextareaType::class, [
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('contactAllowed', ChoiceType::class, [
                'label'   => 'Contact allowed?',
                'choices' => ContactPermit::getChoices(true),
            ])
            ->add('emailAddress', TextType::class, [
                'label'      => 'Email address',
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
        ;

        foreach (['productionModels', 'styles', 'orderTypes', 'features'] as $fieldName) {
            $builder->get($fieldName)->addModelTransformer(new StringListAsCheckBoxesTransformer());
        }

        foreach ([
            'commissionsUrls', 'currenciesAccepted', 'formerly', 'languages', 'otherFeatures', 'otherOrderTypes',
            'otherStyles', 'otherUrls', 'paymentMethods', 'paymentPlans', 'photoUrls', 'pricesUrls', 'speciesDoes',
            'speciesDoesnt', 'formerCreatorIds', 'miniatureUrls',
        ] as $fieldName) {
            $builder->get($fieldName)->addModelTransformer(new StringListAsTextareaTransformer());
        }

        $builder->get('worksWithMinors')->addModelTransformer(new BooleanTransformer());
        $builder->get('ages')->addModelTransformer(new AgesTransformer());
        $builder->get('contactAllowed')->addModelTransformer(new ContactPermitTransformer());
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Creator::class,
        ]);
    }
}
