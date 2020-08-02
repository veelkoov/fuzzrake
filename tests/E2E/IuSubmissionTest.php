<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Entity\Artisan;
use App\Tests\Controller\DbEnabledWebTestCase;
use App\Utils\Artisan\Fields;
use App\Utils\DataInput\DataInputException;
use App\Utils\DataInput\JsonFinder;
use App\Utils\DataInput\Manager;
use App\Utils\StringList;
use Doctrine\ORM\ORMException;
use JsonException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

class IuSubmissionTest extends DbEnabledWebTestCase
{
    private const VARIANT_FULL_DATA = '0';
    private const VARIANT_HALF_DATA_1 = '1';
    private const VARIANT_HALF_DATA_2 = '2';

    private const SKIP = 'SKIP_FIELD_CHECK';

    private const FIELDS = [ // TODO: Make values something more specific
        'MAKER_ID'                  => 'MAKERI__VARIANT__',
        'PASSCODE'                  => 'Passcode __VARIANT__',
        'CONTACT_INFO_OBFUSCATED'   => 'Contact info obfuscated __VARIANT__',
        'CONTACT_INFO_ORIGINAL'     => 'Contact info original __VARIANT__',
        'FORMER_MAKER_IDS'          => "MAK__VARIANT__RID\nART__VARIANT__SID", // TODO: Should verify they're updated
        'NAME'                      => 'Turbopumpernikiel__VARIANT__',
        'FORMERLY'                  => "Ultrapu__VARIANT__mpernikiel\nSzyc__VARIANT__iciel",
        'INTRO'                     => 'Le intro __VARIANT__',
        'SINCE'                     => '2020-0__VARIANT__',
        'LANGUAGES'                 => "English__VARIANT__\nCzech__VARIANT__ (limited)",
        'COUNTRY'                   => 'C__VARIANT__',
        'STATE'                     => 'of mind __VARIANT__',
        'CITY'                      => 'Lisek __VARIANT__',
        'PAYMENT_PLANS'             => '30% upfront, rest in 100 Eur/mth until fully paid (__VARIANT__)',
        'PAYMENT_METHODS'           => "Cash\nBank transfer\nPalPay\nHugs",
        'CURRENCIES_ACCEPTED'       => "USD\nEUR",
        'PRODUCTION_MODELS_COMMENT' => 'Prod mod com',
        'PRODUCTION_MODELS'         => 'Standard commissions', // FIXME
        'STYLES_COMMENT'            => 'STYLES_COMMENT',
        'STYLES'                    => 'Toony', // FIXME
        'OTHER_STYLES'              => 'OTHER_STYLES',
        'ORDER_TYPES_COMMENT'       => 'ORDER_TYPES_COMMENT',
        'ORDER_TYPES'               => 'Head (as parts/separate)', // FIXME
        'OTHER_ORDER_TYPES'         => 'OTHER_ORDER_TYPES',
        'FEATURES_COMMENT'          => 'FEATURES_COMMENT',
        'FEATURES'                  => 'Follow-me eyes', // FIXME
        'OTHER_FEATURES'            => 'OTHER_FEATURES',
        'SPECIES_COMMENT'           => 'SPECIES_COMMENT',
        'SPECIES_DOES'              => 'SPECIES_DOES',
        'SPECIES_DOESNT'            => 'SPECIES_DOESNT',
        'URL_FURSUITREVIEW'         => 'http://fursuitreview.com/value_1.html',
        'URL_WEBSITE'               => 'https://mywebsite.com/value_1.html',
        'URL_PRICES'                => 'https://mywebsite.com/prices_1.html',
        'URL_FAQ'                   => 'https://mywebsite.com/faq_1.html',
        'URL_FUR_AFFINITY'          => 'http://furaffinity.com/value_1.html',
        'URL_DEVIANTART'            => 'https://deviantart.com/value_1.html',
        'URL_TWITTER'               => 'https://twitter.com/value_1.html',
        'URL_FACEBOOK'              => 'https://facebook.com/value_1.html',
        'URL_TUMBLR'                => 'https://tumblr.com/value_1.html',
        'URL_INSTAGRAM'             => 'https://instagram.com/value_1.html',
        'URL_YOUTUBE'               => 'https://youtube.com/value_1.html',
        'URL_LINKTREE'              => 'https://linktreee.com/value_1.html',
        'URL_FURRY_AMINO'           => 'https://furryamino.com/value_1.html',
        'URL_ETSY'                  => 'https://etsy.com/value_1.html',
        'URL_THE_DEALERS_DEN'       => 'https://tdealrsdn.com/value_1.html',
        'URL_OTHER_SHOP'            => 'https://othershop.com/value_1.html',
        'URL_QUEUE'                 => 'https://queue.com/value_1.html',
        'URL_SCRITCH'               => 'https://scritch.com/value_1.html',
        'URL_SCRITCH_PHOTO'         => 'https://scritchphotos.com/value_1.html',
        'URL_SCRITCH_MINIATURE'     => 'https://scritchphotosmini.com/value_1.html',
        'URL_OTHER'                 => 'https://other.com/value_1.html',
        'URL_CST'                   => 'https://cst.com/value_1.html',
        'NOTES'                     => 'NOTES',
        'INACTIVE_REASON'           => 'INACTIVE_REASON',
        'COMMISSIONS_STATUS'        => self::SKIP,
        'CST_LAST_CHECK'            => self::SKIP,
        'COMPLETENESS'              => self::SKIP,
        'CONTACT_ALLOWED'           => 'CORRECTIONS', // FIXME: VARIABLES
        'CONTACT_METHOD'            => 'CONTACT_METHOD',
        'CONTACT_ADDRESS_PLAIN'     => 'CONTACT_ADDRESS_PLAIN',
    ];

    private const VALUE_NOT_SHOWN_IN_FORM = [
        'PASSCODE',
    ];

    private const FIELD_NOT_IN_FORM = [
        'URL_LINKTREE', // TODO: Should be in the form
        'URL_OTHER_SHOP', // TODO: Should be in the form
        'PRODUCTION_MODELS_COMMENT', // TODO: Should be in the form
        'STYLES_COMMENT', // TODO: Should be in the form
        'ORDER_TYPES_COMMENT', // TODO: Should be in the form
        'FEATURES_COMMENT', // TODO: Should be in the form
        'FORMER_MAKER_IDS',
        'URL_SCRITCH_MINIATURE',
        'CONTACT_INFO_ORIGINAL',
        'CONTACT_METHOD',
        'CONTACT_ADDRESS_PLAIN',
        'INACTIVE_REASON',
        'COMPLETENESS',
        'COMMISSIONS_STATUS',
        'CST_LAST_CHECK',
    ];

    private const EXPANDED = [
        'PRODUCTION_MODELS',
        'FEATURES',
        'STYLES',
        'ORDER_TYPES',
    ];

    private const IMPORT_DATA_DIR = __DIR__.'/../../var/testIuFormData'; // TODO: This path should be coming from the container

    /**
     * Purpose of this test is to make sure:
     * - all fields, which values should be displayed in the I/U form, are,
     * - all fields, which values should NOT be displayed, are not,
     * - no newly added field gets overseen in the I/U form,
     * - TODO: all data submitted in the form is saved in the submission.
     *
     * Two tested artisans: an updated one, and a new one.
     *
     * @throws ORMException
     * @throws JsonException
     * @throws DataInputException
     */
    public function testIuSubmissionAndImportFlow(): void
    {
        // TODO: refactor initialization of the Entity Manager so that this can be done in processIuForm each time
        $client = static::createClient();

        $this->checkFieldsArrayCompleteness(); // Test self-test

        $this->emptyTestSubmissionsDir();

        self::$entityManager->persist($this->getArtisan(self::VARIANT_HALF_DATA_1));
        self::$entityManager->flush();

        $oldArtisan1 = $this->getArtisan(self::VARIANT_HALF_DATA_1);

        $this->processIuForm($client, $oldArtisan1->getMakerId(), $oldArtisan1, $this->getArtisan(self::VARIANT_HALF_DATA_2));
        $this->processIuForm($client, '', new Artisan(), $this->getArtisan(self::VARIANT_FULL_DATA));

        $this->performImport();

        $this->validateArtisanAfterImport($this->getArtisan(self::VARIANT_HALF_DATA_2));
        $this->validateArtisanAfterImport($this->getArtisan(self::VARIANT_FULL_DATA));
    }

    private function processIuForm(KernelBrowser $client, string $urlMakerId, Artisan $oldData, Artisan $newData): void
    {
        $client->request('GET', $this->getIuFormUrlForMakerId($urlMakerId));

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $this->verifyGeneratedIuForm($oldData, $client->getResponse()->getContent());

        $client->submitForm('Submit', $this->getFormDataForClient($newData));

        if (302 !== $client->getResponse()->getStatusCode()) {
            echo $client->getResponse()->getContent();
        }

        self::assertEquals(302, $client->getResponse()->getStatusCode());
        $client->followRedirect();
        self::assertSelectorTextContains('h1#how_to_add', 'How do I get my info added/updated?');
    }

    private function verifyGeneratedIuForm(Artisan $oldData, string $body): void
    {
        foreach (Fields::getAll() as $fieldName => $field) {
            if (self::SKIP === self::FIELDS[$fieldName]) {
                continue;
            }

            $oldValue = $oldData->get($field);

            // TODO: lists?
            if (!in_array($fieldName, self::VALUE_NOT_SHOWN_IN_FORM) && !in_array($fieldName, self::FIELD_NOT_IN_FORM)) {
                self::assertStringContainsString($oldValue, $body,
                    "Field $fieldName value '$oldValue' NOT found in the I/U form HTML code"); // TODO: Check COUNT of encounters
            } elseif ('' !== $oldValue) {
                self::assertStringNotContainsStringIgnoringCase($oldValue, $body,
                    "Field $fieldName value '$oldValue' FOUND in the I/U form HTML code");
            }
        }
    }

    /**
     * @throws DataInputException
     * @throws ORMException
     * @throws JsonException
     */
    private function performImport(): void
    {
        $output = new BufferedOutput();
        $io = new SymfonyStyle(new StringInput(''), $output);

        /** @noinspection CaseSensitivityServiceInspection */
        $import = static::$container->get('App\Tasks\DataImportFactory')->get($this->getImportManager(), $io, false);
        $import->import(JsonFinder::arrayFromFiles(self::IMPORT_DATA_DIR));

        //echo $output->fetch(); // TODO: Check the output

        self::$entityManager->flush();
    }

    private function validateArtisanAfterImport(Artisan $artisan)
    {
        // TODO
    }

    private function getArtisan(string $variant): Artisan
    {
        $result = new Artisan();

        foreach (self::FIELDS as $fieldName => $value) {
            if (self::SKIP !== $value) {
                $result->set(Fields::get($fieldName), str_replace('__VARIANT__', $variant, $value));
            }
        }

        return $result;
    }

    private function checkFieldsArrayCompleteness(): void
    {
        foreach (Fields::getAll() as $fieldName => $field) {
            self::assertArrayHasKey($fieldName, self::FIELDS);
        }
    }

    private function getIuFormUrlForMakerId(string $urlMakerId): string
    {
        return '/iu_form'.($urlMakerId ? '/'.$urlMakerId : '');
    }

    private function getFormDataForClient(Artisan $modelData): array
    {
        $result = [];

        foreach (Fields::getAll() as $fieldName => $field) {
            if (!in_array($fieldName, self::FIELD_NOT_IN_FORM)) {
                $newValue = $modelData->get($field);

                if (in_array($fieldName, self::EXPANDED)) {
                    $newValue = StringList::unpack($newValue);
                }

                $result["iu_form[{$field->modelName()}]"] = $newValue;
            }
        }

        return $result;
    }

    private function emptyTestSubmissionsDir(): void
    {
        (new Filesystem())->remove(self::IMPORT_DATA_DIR);
    }

    private function getManagerCorrectionsFileContents(): string
    {
        $newMakerId = $this->getArtisan(self::VARIANT_FULL_DATA)->getMakerId();
        $result = "ack new:$newMakerId:\n";

        foreach ($this->getImportDataFilesHashes() as $hash) {
            $result .= "set pin::$hash:\n";
        }

        return $result;
    }

    /**
     * @return string[]
     */
    private function getImportDataFilesHashes(): array
    {
        return [
            'asdfgqwer', // FIXME
        ];
    }

    /**
     * @throws DataInputException
     */
    private function getImportManager(): Manager
    {
        $filesystem = new Filesystem();
        $tmpFilePath = $filesystem->tempnam(sys_get_temp_dir(), 'import_manager');
        $filesystem->dumpFile($tmpFilePath, $this->getManagerCorrectionsFileContents());

        $result = new Manager($tmpFilePath);

        $filesystem->remove($tmpFilePath);

        return $result;
    }
}
