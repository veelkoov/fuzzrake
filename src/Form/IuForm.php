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

class IuForm extends AbstractType
{
    const BTN_SUBMIT = 'submit';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label'      => 'Studio/maker\'s name',
                'required'   => true,
            ])
            ->add('formerly', TextareaType::class, [
                'label'      => 'Formerly known as',
                'required'   => false,
            ])
            ->add('since', TextType::class, [ // FIXME: date type
                'label'      => 'Since when are you crafting (maker\'s experience, NOT studio age)?',
                'required'   => false,
            ])
            ->add('country', TextType::class, [
                'label'      => 'What country is your studio located in?',
                'required'   => true,
            ])
            ->add('state', TextType::class, [
                'label'      => 'If your studio is in US or Canada, what state is it in?',
                'required'   => false,
            ])
            ->add('city', TextType::class, [
                'label'      => 'What city is your studio located in?',
                'required'   => false,
            ])
            ->add('paymentPlans', TextareaType::class, [
                'label'      => 'What payment plans do you support?',
                'required'   => false,
            ])
            ->add('pricesUrl', UrlType::class, [
                'label'      => 'Do you keep a prices list somewhere online? Please copy+paste full link:',
                'required'   => false,
            ])
            ->add('paymentMethods', TextareaType::class, [
                'label'      => 'What payment methods do you support?',
                'required'   => false,
            ])
            ->add('currenciesAccepted', TextareaType::class, [
                'label'      => 'What currencies do you accept?',
                'required'   => false,
            ])
            ->add('productionModels', ChoiceType::class, [
                'label'    => 'What do you do?',
                'required' => false,
                'choices'  => ProductionModels::getValues(),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('styles', ChoiceType::class, [
                'label'    => 'What styles do you manufacture?',
                'required' => false,
                'choices'  => Styles::getValues(),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('otherStyles', TextareaType::class, [
                'label'      => 'Any other styles?',
                'required'   => false,
            ])
            ->add('orderTypes', ChoiceType::class, [
                'label'    => 'What kind of fursuits/items do you sell?',
                'required' => false,
                'choices'  => OrderTypes::getValues(),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('otherOrderTypes', TextareaType::class, [
                'label'      => 'Any other kinds/items?',
                'required'   => false,
            ])
            ->add('features', ChoiceType::class, [
                'label'    => 'What features do you support?',
                'required' => false,
                'choices'  => Features::getValues(),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('otherFeatures', TextareaType::class, [
                'label'      => 'Any other features?',
                'required'   => false,
            ])
            ->add('speciesDoes', TextareaType::class, [
                'label'      => 'What species do you craft or are you willing to do?',
                'required'   => false,
            ])
            ->add('speciesDoesnt', TextareaType::class, [
                'label'      => 'Any species you will NOT do?',
                'required'   => false,
            ])
            ->add('speciesComment', TextareaType::class, [
                'label'      => 'Any comments on the species?',
                'required'   => false,
            ])

            ->add('fursuitReviewUrl', UrlType::class, [
                'label'      => 'If you are listed on http://fursuitreview.com/makers , please copy+paste full link:',
                'required'   => false,
            ])
            ->add('websiteUrl', UrlType::class, [
                'label'      => 'If you have a regular website, please copy+paste full link:',
                'required'   => false,
            ])
            ->add('faqUrl', UrlType::class, [
                'label'      => 'Do you have a FAQ anywhere? Please copy+paste full link:',
                'required'   => false,
            ])
            ->add('queueUrl', UrlType::class, [
                'label'      => 'Do you keep your queue/progress information on-line (e.g. Trello board)? Please copy+paste full link:',
                'required'   => false,
            ])
            ->add('furAffinityUrl', UrlType::class, [
                'label'      => 'Got FurAffinity? Please copy+paste full link to your user page:',
                'required'   => false,
            ])
            ->add('deviantArtUrl', UrlType::class, [
                'label'      => 'Got DeviantArt? Please copy+paste full link to your user page:',
                'required'   => false,
            ])
            ->add('twitterUrl', UrlType::class, [
                'label'      => 'Got Twitter? Please copy+paste full link to your profile:',
                'required'   => false,
            ])
            ->add('facebookUrl', UrlType::class, [
                'label'      => 'Got Facebook? Please copy+paste full link to your profile:',
                'required'   => false,
            ])
            ->add('tumblrUrl', UrlType::class, [
                'label'      => 'Got Tumblr? Please copy+paste full link to your user page:',
                'required'   => false,
            ])
            ->add('instagramUrl', UrlType::class, [
                'label'      => 'Got Instagram? Please copy+paste full link to your page:',
                'required'   => false,
            ])
            ->add('youtubeUrl', UrlType::class, [
                'label'      => 'Got YouTube? Please copy+paste full link to your userpage:',
                'required'   => false,
            ])
            ->add('etsyUrl', UrlType::class, [
                'label'      => 'Got Etsy? Please copy+paste full link to your store:',
                'required'   => false,
            ])
            ->add('theDealersDenUrl', UrlType::class, [
                'label'      => 'Got The Dealers Den? Please copy+paste full link to your store:',
                'required'   => false,
            ])
//            ->add('otherShopUrls', TextareaType::class, [
//                'label'      => 'Got any other on-line shop? Please copy+paste full link to your store:',
//                'required'   => false,
//            ])
//            ->add('linklistUrl', UrlType::class, [
//                'label'      => 'Got Linktree or similar link list? Please copy+paste full link here:',
//                'required'   => false,
//            ])
            ->add('furryAminoUrl', UrlType::class, [
                'label'      => 'Got Furry Amino? Please copy+paste full link to your profile here:',
                'required'   => false,
            ])
            ->add('otherUrls', TextareaType::class, [
                'label'      => 'Got any other websites/accounts? Please list them here:',
                'required'   => false,
            ])

            ->add('cstUrl', UrlType::class, [
                'label'      => 'If you keep an eligible webpage up-to-date with commissions status, please copy+paste the full link to it:',
                'required'   => false,
            ])

            ->add('scritchUrl', UrlType::class, [
                'label'      => 'Got scritch.es? Please copy+paste full link to your maker page:',
                'required'   => false,
            ])
            ->add('scritchPhotoUrls', TextareaType::class, [
                'label'      => 'Please post up to 5 full links to "featured" photos of your creations hosted on scritch.es',
                'required'   => false,
            ])

            ->add('languages', TextareaType::class, [
                'label'      => 'Which languages do you speak?',
                'required'   => false,
            ])
            ->add('makerId', TextType::class, [
                'label'      => '"Maker ID"',
                'required'   => true,
            ])
            ->add('intro', TextareaType::class, [
                'label'      => 'Short introduction',
                'required'   => false,
            ])

            ->add('notes', TextareaType::class, [
                'label'      => 'Anything else? ("notes")',
                'required'   => false,
            ])
            ->add('passcode', PasswordType::class, [
                'label'      => 'Updates passcode',
                'required'   => true,
            ])
            ->add('contactAllowed', ChoiceType::class, [
                'label'      => 'Contact allowed?',
                'choices'    => ContactPermit::getValueKeyMap(),
                'empty_data' => ContactPermit::NO,
                'expanded'   => true,
            ])
            ->add('contactInfoOriginal', TextType::class, [
                'label'      => 'How can I contact you',
                'required'   => true,
            ])
            ->add(self::BTN_SUBMIT, SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary'],
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
