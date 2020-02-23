<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Artisan;
use App\Service\IuFormService;
use App\Service\WebpageSnapshotManager;
use App\Utils\Artisan\ContactPermit;
use App\Utils\Artisan\Features;
use App\Utils\Artisan\Fields;
use App\Utils\Artisan\OrderTypes;
use App\Utils\Artisan\ProductionModels;
use App\Utils\Artisan\Styles;
use App\Utils\Regexp\Regexp;
use App\Utils\Web\FreeUrl;
use App\Utils\Web\Snapshot\WebpageSnapshotCache;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\CssSelector\Exception\SyntaxErrorException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class IuFormServiceTest extends WebTestCase
{
    private const REGEXP_DATA_ITEM_PUSH = '#\s\d+ +=> (?:\$this->transform[a-z]+\()?\$artisan->get(?<name>[a-z]+)\(\)\)?,#i';
    private const POSSIBLE_CONTACT_PERMITS = ['NO', 'CORRECTIONS', 'ANNOUNCEMENTS', 'FEEDBACK'];

    /**
     * Don't judge, I'm having a lot of fun here!
     */
    public function testServiceCodeNaively(): void
    {
        $checkedSource = file_get_contents(__DIR__.'/../../src/Service/IuFormService.php');

        static::assertGreaterThan(0, Regexp::matchAll(self::REGEXP_DATA_ITEM_PUSH, $checkedSource, $matches));

        $fieldsInForm = Fields::exportedToIuForm();
        unset($fieldsInForm[Fields::VALIDATION_CHECKBOX]);

        foreach ($matches['name'] as $modelName) {
            $field = Fields::getByModelName(lcfirst($modelName));
            $name = $field->is(Fields::CONTACT_INFO_OBFUSCATED) ? Fields::CONTACT_INPUT_VIRTUAL : $field->name();

            static::assertArrayHasKey($name, $fieldsInForm);

            unset($fieldsInForm[$name]);
        }

        static::assertEmpty($fieldsInForm, 'Fields left to be matched: '.join(', ', $fieldsInForm));
    }

    public function testTestCompleteness(): void
    {
        static::assertEquals(ContactPermit::count(), count(self::POSSIBLE_CONTACT_PERMITS));
    }

    /**
     * @dataProvider formDataPrefillDataProvider
     *
     * @throws ExceptionInterface
     */
    public function testFormDataPrefill(Artisan $artisan): void
    {
        $iuFormService = self::getIuFormService();
        $webpageSnapshotManager = self::getWebpageSnapshotManager();

        $updateUrl = $iuFormService->getUpdateUrl($artisan);
        $formWebpage = $webpageSnapshotManager->get(new FreeUrl($updateUrl), false, true);

        $crawler = new Crawler($formWebpage->getContents());

        foreach ([
                     ProductionModels::getValues(),
                     Features::getValues(),
                     OrderTypes::getValues(),
                     Styles::getValues(),
                 ] as $list) {
            foreach ($list as $value) {
                self::assertCount(1, $crawler->filter('input[type=hidden][name^="entry."][value="'.$value.'"]'), "Failed to find '$value'");
            }
        }

        $textareas = $crawler->filter('textarea[name^="entry."]')
            ->each(function ($node, /* @noinspection PhpUnusedParameterInspection */ int $i) {
                /* @noinspection PhpUndefinedMethodInspection */
                return $node->text();
            });

        foreach (Fields::exportedToIuForm() as $field) {
            switch ($field->name()) {
                case Fields::PRODUCTION_MODELS:
                case Fields::STYLES:
                case Fields::ORDER_TYPES:
                case Fields::FEATURES:
                    break; // Validated above

                case Fields::INTRO:
                case Fields::OTHER_FEATURES:
                case Fields::OTHER_STYLES:
                case Fields::OTHER_ORDER_TYPES:
                case Fields::PAYMENT_PLANS:
                case Fields::SPECIES_DOES:
                case Fields::SPECIES_DOESNT:
                case Fields::URL_SCRITCH_PHOTO:
                case Fields::URL_OTHER:
                case Fields::NOTES:
                case Fields::LANGUAGES:
                    $value = $artisan->get($field);
                    self::assertContains($value, $textareas, "Failed to find $value");
                    break;

                case Fields::CONTACT_ALLOWED:
                    $value = ContactPermit::getValues()[$artisan->get($field)];
                    self::assertCount(1, $crawler->filter('input[type=hidden][name^="entry."][value="'.$value.'"]'), "Failed to find '$value' for field '{$field->name()}'");
                    break;

                case Fields::SINCE:
                    // FIXME: validate; Google throws 3 text inputs at fetcher instead of one date
                    break;

                case Fields::VALIDATION_CHECKBOX:
                    $value = 'Yes, I\'m not on the list yet, or I used the update link';
                    self::assertCount(1, $crawler->filter('input[type=hidden][name^="entry."][value="'.$value.'"]'), "Failed to find '$value' for field '{$field->name()}'");
                    break;

                default:
                    $value = $artisan->get($field);

                    try {
                        self::assertCount(1, $crawler->filter('input[type=text][name^="entry."][value="'.$value.'"]'), "Failed to find '$value' for field '{$field->name()}'");
                    } /* @noinspection PhpRedundantCatchClauseInspection */ catch (SyntaxErrorException $e) {
                        self::fail("Value caused syntax error {$e->getMessage()}: $value");
                    }
                    break;
            }
        }
    }

    public function formDataPrefillDataProvider(): array
    {
        return array_map(function (string $contactAllowed) {
            return [
                (new Artisan())
                    ->setName('ARTISAN_NAME')
                    ->setFormerly('ARTISAN_FORMERLY')
                    ->setSince('2019-09')
                    ->setCountry('FI')
                    ->setState('ARTISAN_STATE')
                    ->setCity('ARTISAN_CITY')
                    ->setPaymentPlans('ARTISAN_PAYMENT_PLANS')
                    ->setPricesUrl('ARTISAN_PRICES_URL')
                    ->setProductionModels(ProductionModels::getValuesAsString())
                    ->setStyles(Styles::getValuesAsString())
                    ->setOtherStyles('ARTISAN_OTHER_STYLES')
                    ->setOrderTypes(OrderTypes::getValuesAsString())
                    ->setOtherOrderTypes('ARTISAN_OTHER_ORDER_TYPES')
                    ->setFeatures(Features::getValuesAsString())
                    ->setOtherFeatures('ARTISAN_OTHER_FEATURES')
                    ->setSpeciesDoes('ARTISAN_SPECIES_DOES')
                    ->setSpeciesDoesnt('ARTISAN_SPECIES_DOESNT')
                    ->setFursuitReviewUrl('ARTISAN_FURSUITREVIEW_URL')
                    ->setWebsiteUrl('ARTISAN_WEBSITE_URL')
                    ->setFaqUrl('ARTISAN_FAQ_URL')
                    ->setQueueUrl('ARTISAN_QUEUE_URL')
                    ->setFurAffinityUrl('ARTISAN_FURAFFINITY_URL')
                    ->setDeviantArtUrl('ARTISAN_DEVIANTART_URL')
                    ->setTwitterUrl('ARTISAN_TWITTER_URL')
                    ->setFacebookUrl('ARTISAN_FACEBOOK_URL')
                    ->setTumblrUrl('ARTISAN_TUMBLR_URL')
                    ->setInstagramUrl('ARTISAN_INSTAGRAM_URL')
                    ->setYoutubeUrl('ARTISAN_YOUTUBE_URL')
                    ->setOtherUrls('ARTISAN_OTHER_URLS')
                    ->setCstUrl('ARTISAN_CST_URL')
                    ->setScritchUrl('ARTISAN_SCRITCH_URL')
                    ->setScritchPhotoUrls('ARTISAN_SCRITCH_PHOTOS_URLS')
                    ->setLanguages('ARTISAN_LANGUAGES')
                    ->setMakerId('ARTISAN_MAKER_UI')
                    ->setIntro('ARTISAN_INTRO')
                    ->setNotes('ARTISAN_NOTES')
                    ->setContactAllowed($contactAllowed)
                    ->setContactInfoObfuscated('ARTISAN_CONTACT_INFO_OBFUSCATED'),
            ];
        }, self::POSSIBLE_CONTACT_PERMITS);
    }

    private static function getIuFormService(): IuFormService
    {
        $servicesYamlPath = __DIR__.'/../../config/services.yaml';
        $iuFormUrl = trim(`sed -n '/iu_form_url:/p' '$servicesYamlPath' | cut -f2 -d"'"`);

        return new IuFormService($iuFormUrl);
    }

    private static function getWebpageSnapshotManager(): WebpageSnapshotManager
    {
        return new WebpageSnapshotManager(new NullLogger(), new WebpageSnapshotCache(new NullLogger(),
            __DIR__.'/../../var/snapshots'));
    }
}
