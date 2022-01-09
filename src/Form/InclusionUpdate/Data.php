<?php

declare(strict_types=1);

namespace App\Form\InclusionUpdate;

use App\DataDefinitions\Ages;
use App\DataDefinitions\Features;
use App\DataDefinitions\Fields\Validation;
use App\DataDefinitions\OrderTypes;
use App\DataDefinitions\ProductionModels;
use App\DataDefinitions\Styles;
use App\Form\BooleanTransformer;
use App\Form\SinceTransformer;
use App\Form\StringArrayTransformer;
use App\ValueObject\Routing\RouteName;
use InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class Data extends BaseForm
{
    final public const OPT_ROUTER = 'router';
    final public const OPT_PHOTOS_COPYRIGHT_OK = 'photosCopyrightOk';
    final public const FLD_PHOTOS_COPYRIGHT = 'photosCopyright';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if (!(($router = $options[self::OPT_ROUTER]) instanceof RouterInterface)) {
            throw new InvalidArgumentException('I wanted a router.');
        }

        $otherStylesPath = $router->generate(RouteName::STATISTICS, ['_fragment' => 'other_styles'], UrlGeneratorInterface::ABSOLUTE_PATH);
        $otherOrderTypesPath = $router->generate(RouteName::STATISTICS, ['_fragment' => 'other_order_types'], UrlGeneratorInterface::ABSOLUTE_PATH);
        $otherFeaturesPath = $router->generate(RouteName::STATISTICS, ['_fragment' => 'other_features'], UrlGeneratorInterface::ABSOLUTE_PATH);
        $makerIdPagePath = $router->generate(RouteName::MAKER_IDS, referenceType: UrlGeneratorInterface::ABSOLUTE_PATH);
        $rulesPagePath = $router->generate(RouteName::RULES, referenceType: UrlGeneratorInterface::ABSOLUTE_PATH);

        $builder
            ->add('name', TextType::class, [
                'label'      => 'Studio/maker\'s name',
                'required'   => true,
                'empty_data' => '',
            ])
            ->add('formerly', TextareaType::class, [
                'label'      => 'Formerly known as',
                'help'       => 'If your studio changed its name in the past, what was it? You can keep any old names here. Please: each name on a separate line.',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('since', DateType::class, [ // grep-default-auto-since-day-01
                'label'        => 'Since when are you crafting (maker\'s experience, NOT studio age)?',
                'help'         => 'If your studio has more than one maker, please provide information on the experience you give a guarantee for with your products. (e.g. senior maker checks how junior is doing, if all is done well, and will make sure any repairs/improvements will be done - then give senior experience information; two seniors - how about an average?)',
                'required'     => false,
                'empty_data'   => '',
                'widget'       => 'choice',
                'input'        => 'string',
                'input_format' => 'Y-m-d',
                'format'       => 'yyyy/MM dd',
                'placeholder'  => ['year' => 'Year', 'month' => 'Month', 'day' => 'Day'],
                'years'        => $this->getSinceYears(),
            ])
            ->add('country', TextType::class, [
                'label'      => 'Country',
                'required'   => true,
                'empty_data' => '',
            ])
            ->add('state', TextType::class, [
                'label'      => 'State',
                'help'       => 'Only for the US and Canada, otherwise please leave empty.',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('city', TextType::class, [
                'label'      => 'What city is your studio located in?',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('ages', ChoiceType::class, [
                'label'      => 'What is your age?',
                'required'   => true,
                'choices'    => Ages::getChoices(false),
                'expanded'   => true,
                'help'       => '<strong>NOTE:</strong> minors are currently still required to state their age on their website as well, <a href="'.$rulesPagePath.'" target="_blank">as per rules</a>.', // grep-state-age-on-website-until-filters-are-in-place
                'help_html'  => true,
            ])
            ->add('worksWithMinors', ChoiceType::class, [
                'label'    => 'Do you accept commissions from minors/underage clients?',
                'required' => true,
                'choices'  => ['Yes' => 'YES', 'No' => 'NO'],
                'expanded' => true,
            ])
            ->add('paymentPlans', TextareaType::class, [
                'label'      => 'What payment plans do you support?',
                'help'       => 'Please provide a precise description. If you leave this empty, getfursu.it will treat this information as missing! (see the first example). Examples: <em>None/100% upfront</em>, <em>40% upfront to reserve a slot, 40% after 2 months, 20% after next 2 months</em>, <em>50% upfront to reserve a slot, 10% each next month</em>, <em>50% upfront for slot reservation, 100$ each next month until fully paid</em>.',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('pricesUrls', TextareaType::class, [
                'label'      => 'Do you keep a prices list somewhere online? Please copy+paste full link:',
                'help'       => 'Please supply a <strong>precise</strong> link, not one to e.g. the homepage. Preferred: typical webpages, FurAffinity. Avoid images, social media. If you keep prices for multiple offers on different pages, you can paste multiple addresses here, each one in a separate line.',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('paymentMethods', TextareaType::class, [
                'label'      => 'What payment methods do you support?',
                'help'       => 'Examples: <em>Bank transfers</em>, <em>Debit cards</em>, on-line payments (please mention the services names, e.g. <em>PayPal</em>), <em>Cash</em>. Each in a separate line.',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('currenciesAccepted', TextareaType::class, [
                'label'      => 'What currencies do you accept?',
                'help'       => 'Examples: <em>USD</em>, <em>AUD</em>, <em>CAD</em>, <em>EUR</em>, <em>BRL</em>, <em>CZK</em>. Each in a separate line, please.',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('productionModels', ChoiceType::class, [
                'label'    => 'What do you do?',
                'required' => false,
                'choices'  => ProductionModels::getValues(),
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('productionModelsComment', TextareaType::class, [
                'label'      => 'Any comments on the production models?',
                'help'       => 'Example: <em>I usually work with pre-mades, but I\'m willing to do an interesting commission</em>.',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
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
                'help'       => 'You can check what other makers listed <a href="'.$otherStylesPath.'" target="_blank">here</a>. Please: one item = one line.',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('stylesComment', TextareaType::class, [
                'label'      => 'Any comments on the styles?',
                'help'       => 'Example: <em>Realistic are my speciality</em>.',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
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
                'help'       => 'You can check what other makers listed <a href="'.$otherOrderTypesPath.'" target="_blank">here</a>. Please: one item = one line.',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('orderTypesComment', TextareaType::class, [
                'label'      => 'Any comments on the order types?',
                'help'       => 'Example: <em>Especially pumped to do digitigrades because I have too much foam stacked, and I want to get rid of it.</em>',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
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
                'help'       => 'You can check what other makers listed <a href="'.$otherFeaturesPath.'" target="_blank">here</a>. Please: one item = one line.',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('featuresComment', TextareaType::class, [
                'label'      => 'Any comments on the features?',
                'help'       => 'Example: <em>Prefer not doing follow-me eyes, because I don\'t like being observed while in the workshop.</em>',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('speciesDoes', TextareaType::class, [
                'label'      => 'What species do you craft or are you willing to do?',
                'help'       => 'Examples: <em>Most species</em> (+ more specific in the "will not do" field), but you may list only some groups here, e.g. <em>Scalies</em>, <em>Fantasy creatures</em>, or even particular species, e.g. <em>Lions</em>, <em>Tigers</em>. Just keep in mind that if you specify a wider group (<em>Most species</em> or <em>Felines</em>), then listing particular species is redundant (e.g. <em>Lions</em>). Don\'t put any comments here, e.g. <em>willing to try XYZ</em> or <em>most experience with XYZ</em> should be placed in the separate comments field instead. Please: one specie (group) = one line.',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('speciesDoesnt', TextareaType::class, [
                'label'      => 'Any species you will NOT do?',
                'help'       => 'Please: one specie (group) = one line.',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('speciesComment', TextareaType::class, [
                'label'      => 'Any comments on the species?',
                'help'       => 'Examples: <em>Most experienced in canines</em>, <em>I especially enjoy dragons (and scalies in general)</em>, <em>Willing to try anything</em>, <em>Just ask!</em>',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('fursuitReviewUrl', UrlType::class, [
                'label'      => 'If you are listed on FursuitReview, please copy+paste full link:',
                'help'       => '<a href="https://fursuitreview.com/m/" target="_blank">Check here</a>. This is for my convenience - I will check that either way, and add the link if you are there. Thank you for filling this one for me!',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('websiteUrl', UrlType::class, [
                'label'      => 'If you have a regular website, please copy+paste full link:',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('faqUrl', UrlType::class, [
                'label'      => 'Do you have a FAQ anywhere? Please copy+paste full link:',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('queueUrl', UrlType::class, [
                'label'      => 'Do you keep your queue/progress information on-line (e.g. Trello board)? Please copy+paste full link:',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('furAffinityUrl', UrlType::class, [
                'label'      => 'Got FurAffinity? Please copy+paste full link to your user page:',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('deviantArtUrl', UrlType::class, [
                'label'      => 'Got DeviantArt? Please copy+paste full link to your user page:',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('twitterUrl', UrlType::class, [
                'label'      => 'Got Twitter? Please copy+paste full link to your profile:',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('facebookUrl', UrlType::class, [
                'label'      => 'Got Facebook? Please copy+paste full link to your profile:',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('tumblrUrl', UrlType::class, [
                'label'      => 'Got Tumblr? Please copy+paste full link to your user page:',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('instagramUrl', UrlType::class, [
                'label'      => 'Got Instagram? Please copy+paste full link to your page:',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('youtubeUrl', UrlType::class, [
                'label'      => 'Got YouTube? Please copy+paste full link to your userpage:',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('etsyUrl', UrlType::class, [
                'label'      => 'Got Etsy? Please copy+paste full link to your store:',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('theDealersDenUrl', UrlType::class, [
                'label'      => 'Got The Dealers Den? Please copy+paste full link to your store:',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('otherShopUrl', TextareaType::class, [
                'label'      => 'Got any other on-line shop? Please copy+paste full link to your store:',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('linklistUrl', UrlType::class, [
                'label'      => 'Got Linktree or similar link list? Please copy+paste full link here:',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('furryAminoUrl', UrlType::class, [
                'label'      => 'Got Furry Amino? Please copy+paste full link to your profile here:',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('otherUrls', TextareaType::class, [
                'label'      => 'Got any other websites/accounts? Please list them here:',
                'help'       => 'Please note that they will <strong>not</strong> be visible on the website, but if I see a significant number of links to one portal, I might add support for it in the future. Please: one address = one line.',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('commissionsUrls', TextareaType::class, [
                'label'      => 'Where do you publish your commissions/quotes status? Put the address here:',
                'help'       => 'Simply writing "open" in this field will not work, only accepted values are URLs (addresses). Please remember that you need to provide precise address - <strong>no clicking/tapping</strong> to see the status text is allowed (scrolling is OK). For example, if you created a webpage with the address like <em>https://example.com/</em> and you post the commissions status on a subpage like <em>https://example.com/commissions</em>, then you should use the latter address.', // Not mentioning possibility to track multiple pages at once, as it may be taken as suggestion to list different pages/social DUPLICATING the information
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('scritchUrl', UrlType::class, [
                'label'      => 'Got Scritch page? Please copy+paste full link to your maker page:',
                'help'       => '<strong>You may already have one created for you.</strong> Go claim your page already if it\'s there.',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('furtrackUrl', UrlType::class, [
                'label'      => 'Got Furtrack page? Please copy+paste full link to your maker page:',
                'help'       => '<strong>Someone may have already created one for you.</strong> Go there check for photos tagged with your studio\'s name already.',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('photoUrls', TextareaType::class, [
                'label'      => 'Choose up to 5 "featured" photos of your creations',
                'help'       => 'You can use photos hosted on either Scritch or Furtrack or both (mixed). To copy a link from <strong>Scritch</strong>, open the photo and click "Get link" on the upper-right of the pop-up. You should end up with something similar to this: <em>https://scritch.es/pictures/25ae6f07-9855-445f-9c1d-a8c78166b81b</em>. To copy a link from <strong>Furtrack</strong>, open the photo and click the link on the upper-right corner of the pop-up. You should end up with something similar to this: <em>https://www.furtrack.com/p/49767</em>. Kindly place each photo link in a single line. Note: If you want to reorder the photos, do this in the field and notify the maintainer about this by adding notes or contacting ("I reordered the photos").', // grep-cannot-easily-reorder-photos
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add(self::FLD_PHOTOS_COPYRIGHT, ChoiceType::class, [
                'label'     => 'Copyright acknowledgement',
                'help'      => 'Fact of the photos being published on Scritch or Furtrack <strong>doesn\'t necessarily mean the photographers agreed to repost/reuse it elsewhere</strong>, including getfursu.it. Please make sure you are allowed to link those photos here.',
                'help_html' => true,
                'data'      => $options[self::OPT_PHOTOS_COPYRIGHT_OK] ? ['OK'] : [],
                'required'  => false,
                'mapped'    => false,
                'expanded'  => true,
                'multiple'  => true,
                'choices'   => ['I captured those photos myself or I got photographer\'s permission to use them on getfursu.it' => 'OK'],
            ])
            ->add('languages', TextareaType::class, [
                'label'      => 'Which languages do you speak?',
                'help'       => 'Each one in a separate line, please. Examples: <em>English</em>, <em>English (limited)</em>.',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('makerId', TextType::class, [
                'label'      => '"Maker ID"',
                'help'       => 'This will be your <strong>Maker ID</strong>, a short identification thingy. Please <a href="'.$makerIdPagePath.'" target="_blank">read about it here</a>. 7 characters, uppercase letters and/or digits. Here are some possible examples: <em>SILVENA</em>, <em>2STFURS</em>, <em>DIRECRT</em>, <em>DHCACTI</em>, <em>NUKECTS</em>, <em>GOFURIT</em>, <em>ALPHADG</em>. You might use abbreviations, state or country codes, etc.',
                'help_html'  => true,
                'required'   => true,
                'empty_data' => '',
            ])
            ->add('intro', TextareaType::class, [
                'label'      => 'Short introduction',
                'help'       => 'Feel free to put here any "welcome" or "who are we/am I" or "what makes me/us special" text that\'ll be displayed on top of your details pop-up. Max 500 characters!',
                'required'   => false,
                'empty_data' => '',
            ])
        ;

        foreach (['productionModels', 'styles', 'orderTypes', 'features'] as $fieldName) {
            $builder->get($fieldName)->addModelTransformer(StringArrayTransformer::getInstance());
        }

        $builder->get('worksWithMinors')->addModelTransformer(BooleanTransformer::getInstance());
        $builder->get('since')->addModelTransformer(SinceTransformer::getInstance());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->define(self::OPT_ROUTER)
            ->allowedTypes(RouterInterface::class)
            ->required()

            ->define(self::OPT_PHOTOS_COPYRIGHT_OK)
            ->allowedTypes('boolean')
            ->required()
        ;

        $resolver->setDefault('validation_groups', ['Default', Validation::GRP_DATA]);
    }

    /**
     * @return int[]
     */
    private function getSinceYears(): array
    {
        $year = (int) date('Y');
        $result = [];

        do {
            $result[] = $year;
        } while (--$year >= 1990);

        return $result;
    }
}
