<?php

declare(strict_types=1);

namespace App\Form\InclusionUpdate;

use App\Data\Definitions\Ages;
use App\Data\Definitions\Features;
use App\Data\Definitions\Fields\Validation;
use App\Data\Definitions\OrderTypes;
use App\Data\Definitions\ProductionModels;
use App\Data\Definitions\Styles;
use App\Form\RouterDependentTrait;
use App\Form\Transformers\AgesTransformer;
use App\Form\Transformers\BooleanTransformer;
use App\Form\Transformers\NullToEmptyArrayTransformer;
use App\Form\Transformers\SinceTransformer;
use App\Form\Transformers\StringListAsTextareaTransformer;
use App\ValueObject\Routing\RouteName;
use Override;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Data extends BaseForm
{
    use RouterDependentTrait;

    final public const string OPT_PHOTOS_COPYRIGHT_OK = 'photosCopyrightOk';
    final public const string FLD_PHOTOS_COPYRIGHT = 'photosCopyright';
    final public const string FLD_MAKER_ID = 'makerId';

    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $router = self::getRouter($options);
        $otherStylesPath = htmlspecialchars($router->generate(RouteName::STATISTICS, ['_fragment' => 'other_styles']));
        $otherOrderTypesPath = htmlspecialchars($router->generate(RouteName::STATISTICS, ['_fragment' => 'other_order_types']));
        $otherFeaturesPath = htmlspecialchars($router->generate(RouteName::STATISTICS, ['_fragment' => 'other_features']));
        $makerIdPagePath = htmlspecialchars($router->generate(RouteName::MAKER_IDS, [], UrlGeneratorInterface::ABSOLUTE_PATH));

        $builder
            ->add('name', TextType::class, [
                'label'      => 'Studio/maker\'s name',
                'required'   => true,
                'empty_data' => '',
            ])
            ->add('formerly', TextareaType::class, [
                'label'      => 'Formerly / also known as',
                'help'       => 'What was your studio known as before? Do you use multiple nicknames? You can keep any old names and aliases here. Please: each name on a separate line.',
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('since', DateType::class, [ // grep-default-auto-since-day-01
                'label'        => 'Since when are you crafting (maker\'s experience, NOT studio age)?',
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
            ])
            ->add('nsfwWebsite', ChoiceType::class, [
                'label'      => 'The websites linked above may contain "non-family-friendly" (or <em>NSFW</em>) content, such as, but not limited to:<br />&bull; <u>suggestive</u> or explicit adult content<br />&bull; triggering or controversial content<br />&bull; content not suitable for those under 18',
                'label_html' => true,
                'required'   => true,
                'choices'    => ['Yes / Possibly' => 'YES', 'No, all content is, and forever will be, safe for everyone to view' => 'NO'],
                'expanded'   => true,
            ])
            ->add('nsfwSocial', ChoiceType::class, [
                'label'      => 'Is there a possibility of <em>NSFW</em> (or the type of content listed above) being <u>liked</u>/shared/posted/commented on by your social media account?',
                'label_html' => true,
                'required'   => true,
                'choices'    => ['Yes / Possibly' => 'YES', 'No, all content is, and forever will be, safe for everyone to view' => 'NO'],
                'expanded'   => true,
            ])
            ->add('doesNsfw', ChoiceType::class, [
                'label'    => 'Do you offer fursuit features intended for adult use?',
                'choices'  => ['Yes' => 'YES', 'No' => 'NO'],
                'expanded' => true,
            ])
            ->add('worksWithMinors', ChoiceType::class, [
                'label'    => 'Do you accept commissions from minors or people under 18?',
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
                'help'       => 'Examples: <em>USD</em>, <em>AUD</em>, <em>CAD</em>, <em>EUR</em>, <em>BRL</em>, <em>CZK</em>. Each in a separate line, please. <strong>Note: using PayPal and similar systems doesn\'t mean you accept all currencies</strong> - those systems just convert the payments using some rates and possibly add conversion fees. Please list the <strong>target/primary</strong> currencies configured in your account.',
                'help_html'  => true,
                'required'   => false,
                'empty_data' => '',
            ])
            ->add('productionModels', ChoiceType::class, [
                'label'    => 'What do you do?',
                'required' => false,
                'choices'  => ProductionModels::getFormChoices(),
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
                'choices'  => Styles::getFormChoices(),
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
                'choices'  => OrderTypes::getFormChoices(),
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
                'choices'  => Features::getFormChoices(),
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
            ->add('mastodonUrl', UrlType::class, [
                'label'      => 'Got Mastodon? Please copy+paste full link to your profile:',
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
                'help'       => 'You can use photos hosted on either <strong>Scritch or Furtrack</strong> or both (mixed), <strong>getfursu.it cannot serve photos from anywhere else</strong>. Kindly place each photo link in a single line. Check the instructions on adding/<wbr>reordering below.',
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
            ->add(self::FLD_MAKER_ID, TextType::class, [
                'label'      => '"Maker ID"',
                'help'       => '<a href="'.$makerIdPagePath.'" target="_blank">Read about maker IDs here</a>. 7 characters, uppercase letters and/or digits. Examples: <em>VLKVFUR</em>, <em>FUR2022</em>.',
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
            $builder->get($fieldName)->addModelTransformer(new NullToEmptyArrayTransformer());
        }

        foreach ([
            'commissionsUrls', 'currenciesAccepted', 'formerly', 'languages', 'otherFeatures', 'otherOrderTypes',
            'otherStyles', 'otherUrls', 'paymentMethods', 'paymentPlans', 'photoUrls', 'pricesUrls', 'speciesDoes',
            'speciesDoesnt',
        ] as $fieldName) {
            $builder->get($fieldName)->addModelTransformer(new StringListAsTextareaTransformer());
        }

        $builder->get('since')->addModelTransformer(new SinceTransformer());
        $builder->get('ages')->addModelTransformer(new AgesTransformer());

        foreach (['nsfwWebsite', 'nsfwSocial', 'doesNsfw', 'worksWithMinors'] as $field) {
            $builder->get($field)->addModelTransformer(new BooleanTransformer());
        }
    }

    #[Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        self::configureRouterOption($resolver);

        $resolver
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
