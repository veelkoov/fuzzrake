<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Entity\Artisan;
use App\Repository\ArtisanRepository;
use App\Tasks\DataImport;
use App\Tests\Controller\DbEnabledWebTestCase;
use App\Utils\Artisan\Field;
use App\Utils\Artisan\Fields;
use App\Utils\Artisan\Utils;
use App\Utils\Data\FdvFactory;
use App\Utils\Data\Printer;
use App\Utils\DataInput\DataInputException;
use App\Utils\DataInput\JsonFinder;
use App\Utils\DataInput\Manager;
use App\Utils\DataInput\RawImportItem;
use App\Utils\StringList;
use App\Utils\StrUtils;
use Doctrine\ORM\ORMException;
use JsonException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use TRegx\CleanRegex\Pattern;

class IuSubmissionTest extends DbEnabledWebTestCase
{
    private const VARIANT_FULL_DATA = 0;
    private const VARIANT_HALF_DATA_1 = 1;
    private const VARIANT_HALF_DATA_2 = 2;

    private const SKIP = '_SKIP_FIELD_';
    private const SET = '_SET_';
    private const CHECK = '_CHECK_';

    private const FIELDS = [
        'MAKER_ID'                  => ['MAKERI0', 'MAKERI1', 'MAKERI2'],
        'FORMER_MAKER_IDS'          => [
            self::SKIP,
            [self::SET => 'OLDMKR1', self::CHECK => 'OLDMKR1'],
            [self::SET => self::SKIP, self::CHECK => "MAKERI1\nOLDMKR1"],
        ],
        'NAME'                      => ['Maker name 0', 'Maker name old', 'Maker name new'],
        'FORMERLY'                  => ['Formerly 0', 'Formerly old', "Formerly old\nMaker name old"],
        'PASSCODE'                  => 'Passcode __VARIANT__',
        'CONTACT_INFO_OBFUSCATED'   => [
            [self::SET => 'E-mail: email@e.mail', self::CHECK => 'E-MAIL: e***l@e****l'],
            [self::SET => self::SKIP, self::CHECK => 'TELEGRAM: @te*****am'],
            [self::SET => 'Twitter: @twit_ter', self::CHECK => 'TWITTER: @tw****er'],
        ],
        'CONTACT_INFO_ORIGINAL'     => [
            [self::SET => self::SKIP, self::CHECK => 'E-mail: email@e.mail'],
            [self::SET => 'Telegram: @tele_gram', self::CHECK => 'Telegram: @tele_gram'],
            [self::SET => self::SKIP, self::CHECK => 'Twitter: @twit_ter'],
        ],
        'CONTACT_ADDRESS_PLAIN'     => [
            [self::SET => self::SKIP, self::CHECK => 'email@e.mail'],
            [self::SET => self::SKIP, self::CHECK => '@tele_gram'],
            [self::SET => self::SKIP, self::CHECK => '@twit_ter'],
        ],
        'CONTACT_METHOD'            => self::SKIP,
        'CONTACT_ALLOWED'           => ['CORRECTIONS', 'ANNOUNCEMENTS', 'FEEDBACK'],
        'INTRO'                     => 'Le intro __VARIANT__',
        'SINCE'                     => '2020-0__VARIANT__',
        'LANGUAGES'                 => "Czech__VARIANT__ (limited)\nEnglish__VARIANT__",
        'COUNTRY'                   => 'C__VARIANT__',
        'STATE'                     => 'of mind __VARIANT__',
        'CITY'                      => 'Lisek __VARIANT__',
        'PAYMENT_PLANS'             => '30% upfront, rest in 100 Eur/mth until fully paid (__VARIANT__)',
        'PAYMENT_METHODS'           => "Cash\nBank transfer\nPalPay\nHugs___VARIANT__",
        'CURRENCIES_ACCEPTED'       => "USD\nEU__VARIANT__",
        'PRODUCTION_MODELS_COMMENT' => 'Comment about production models __VARIANT__',
        'PRODUCTION_MODELS'         => 'Standard commissions', // FIXME
        'STYLES_COMMENT'            => 'Comment about styles __VARIANT__',
        'STYLES'                    => 'Toony', // FIXME
        'OTHER_STYLES'              => 'OTHER_STYLES___VARIANT__',
        'ORDER_TYPES_COMMENT'       => 'Comment for order types __VARIANT__',
        'ORDER_TYPES'               => 'Head (as parts/separate)', // FIXME
        'OTHER_ORDER_TYPES'         => 'OTHER_ORDER_TYPES___VARIANT__',
        'FEATURES_COMMENT'          => 'Comment about features __VARIANT__',
        'FEATURES'                  => 'Follow-me eyes', // FIXME
        'OTHER_FEATURES'            => 'OTHER_FEATURES___VARIANT__',
        'SPECIES_COMMENT'           => 'SPECIES_COMMENT___VARIANT__',
        'SPECIES_DOES'              => 'SPECIES_DOES___VARIANT__',
        'SPECIES_DOESNT'            => 'SPECIES_DOESNT___VARIANT__',
        'NOTES'                     => 'NOTES___VARIANT__',
        'INACTIVE_REASON'           => ['', 'INACTIVE_REASON12', 'INACTIVE_REASON12'],
        'URL_FURSUITREVIEW'         => 'http://fursuitreview.com/value___VARIANT__.html',
        'URL_WEBSITE'               => 'https://mywebsite.com/value___VARIANT__.html',
        'URL_PRICES'                => 'https://mywebsite.com/prices___VARIANT__.html',
        'URL_FAQ'                   => 'https://mywebsite.com/faq___VARIANT__.html',
        'URL_FUR_AFFINITY'          => 'http://www.furaffinity.net/user/value___VARIANT__.html',
        'URL_DEVIANTART'            => 'https://www.deviantart.com/value___VARIANT__.html',
        'URL_TWITTER'               => 'https://twitter.com/value___VARIANT__.html',
        'URL_FACEBOOK'              => 'https://www.facebook.com/value___VARIANT__.html/',
        'URL_TUMBLR'                => 'https://tumblr.com/value___VARIANT__.html',
        'URL_INSTAGRAM'             => 'https://www.instagram.com/value___VARIANT__.html/',
        'URL_YOUTUBE'               => 'https://youtube.com/value___VARIANT__.html',
        'URL_LINKLIST'              => 'https://linklist.com/value___VARIANT__.html',
        'URL_FURRY_AMINO'           => 'https://furryamino.com/value___VARIANT__.html',
        'URL_ETSY'                  => 'https://etsy.com/value___VARIANT__.html',
        'URL_THE_DEALERS_DEN'       => 'https://tdealrsdn.com/value___VARIANT__.html',
        'URL_OTHER_SHOP'            => 'https://othershop.com/value___VARIANT__.html',
        'URL_QUEUE'                 => 'https://queue.com/value___VARIANT__.html',
        'URL_SCRITCH'               => 'https://scritch.com/value___VARIANT__.html',
        'URL_SCRITCH_PHOTO'         => 'https://scritchphotos.com/value___VARIANT__.html',
        'URL_SCRITCH_MINIATURE'     => ['', 'URL_SCRITCH_MINIATURE12', 'URL_SCRITCH_MINIATURE12'],
        'URL_OTHER'                 => 'https://other.com/value___VARIANT__.html',
        'URL_CST'                   => 'https://cst.com/value___VARIANT__.html',
        'COMMISSIONS_STATUS'        => self::SKIP,
        'CST_LAST_CHECK'            => self::SKIP,
        'COMPLETENESS'              => self::SKIP,
    ];

    private const VALUE_NOT_SHOWN_IN_FORM = [
        'PASSCODE',
    ];

    private const FIELD_NOT_IN_FORM = [
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
     * - all data submitted in the form is saved in the submission.
     *
     * Two tested artisans: an updated one, and a new one.
     *
     * @throws ORMException|JsonException|DataInputException
     */
    public function testIuSubmissionAndImportFlow(): void
    {
        $client = static::createClient(); // Single client to be used throughout the whole test to avoid multiple in-memory DB

        $this->checkFieldsArrayCompleteness(); // Test self-test

        $this->emptyTestSubmissionsDir();

        $oldArtisan1 = $this->getArtisan(self::VARIANT_HALF_DATA_1, self::SET);
        Utils::updateContact($oldArtisan1, $oldArtisan1->getContactInfoOriginal());

        self::$entityManager->persist($oldArtisan1);
        self::$entityManager->flush();

        $repo = self::$entityManager->getRepository(Artisan::class);
        self::assertCount(1, $repo->findAll(), 'Single artisan in the DB before import');

        $oldArtisan1 = $this->getArtisan(self::VARIANT_HALF_DATA_1, self::CHECK);
        $this->processIuForm($client, $oldArtisan1->getMakerId(), $oldArtisan1, $this->getArtisan(self::VARIANT_HALF_DATA_2, self::SET));
        $this->processIuForm($client, '', new Artisan(), $this->getArtisan(self::VARIANT_FULL_DATA, self::SET));

        $this->performImport();
        self::$entityManager->flush();
        self::assertCount(2, $repo->findAll(), 'Expected two artisans in the DB after import');

        $this->validateArtisanAfterImport($this->getArtisan(self::VARIANT_HALF_DATA_2, self::CHECK));
        $this->validateArtisanAfterImport($this->getArtisan(self::VARIANT_FULL_DATA, self::CHECK));

        $this->emptyTestSubmissionsDir();
    }

    private function checkFieldsArrayCompleteness(): void
    {
        foreach (Fields::getAll() as $fieldName => $field) {
            self::assertArrayHasKey($fieldName, self::FIELDS);
        }
    }

    private function emptyTestSubmissionsDir(): void
    {
        (new Filesystem())->remove(self::IMPORT_DATA_DIR);
    }

    private function getArtisan(int $variant, string $purpose): Artisan
    {
        $result = new Artisan();

        foreach (self::FIELDS as $fieldName => $value) {
            if (is_array($value)) {
                $value = $value[$variant];
            }

            if (is_array($value)) {
                $value = $value[$purpose];
            }

            if (self::SKIP !== $value) {
                $result->set(Fields::get($fieldName), str_replace('__VARIANT__', $variant, $value));
            }
        }

        return $result;
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

    private function getIuFormUrlForMakerId(string $urlMakerId): string
    {
        return '/iu_form'.($urlMakerId ? '/'.$urlMakerId : '');
    }

    private function verifyGeneratedIuForm(Artisan $oldData, string $htmlBody): void
    {
        $htmlBodyLc = $this->removeLabelsFromLowercaseHtml(mb_strtolower($htmlBody));
        $checked = pattern('<input type="(checkbox|radio)" [^>]*value="(?<value>[^"]+)" checked="checked"\s?/?>')
            ->match($htmlBodyLc)->groupBy('value')->texts();

        foreach (Fields::getAll() as $fieldName => $field) {
            if (self::SKIP === self::FIELDS[$fieldName] || '' === ($value = $oldData->get($field))) {
                continue;
            }

            if (in_array($fieldName, self::VALUE_NOT_SHOWN_IN_FORM) || in_array($fieldName, self::FIELD_NOT_IN_FORM)) {
                self::assertEquals(0, substr_count($htmlBodyLc, mb_strtolower($value)),
                    "Field {$field->name()} value '$value' FOUND in the I/U form HTML code");
            } else {
                if (in_array($fieldName, self::EXPANDED)) {
                    $this->validateListFieldInGeneratedIuForm($field, $checked, $value);
                } else {
                    $this->validateNonListFieldInGeneratedIuForm($field, $htmlBodyLc, $value);
                }
            }
        }
    }

    private function removeLabelsFromLowercaseHtml(string $inputLowercaseHtml): string
    {
        return pattern('(<label[^>]*>[^<]+</label>)')->remove($inputLowercaseHtml)->all();
    }

    private function validateNonListFieldInGeneratedIuForm(Field $field, string $htmlBodyLowercase, string $value): void
    {
        // TODO: Try updating T-Regx and doing the Pattern::inject parameters according to the docs once again
        $count = Pattern::inject('(<textarea[^>]*>\s*@\s*</textarea>)|("\s*@\s*")', [
            mb_strtolower($value),
            mb_strtolower($value),
        ])->count($htmlBodyLowercase);

        self::assertEquals(1, $count,
            "Field {$field->name()} value '$value' NOT present exactly once in the I/U form HTML code");
    }

    /**
     * @param array[] $checked
     */
    private function validateListFieldInGeneratedIuForm(Field $field, array $checked, string $value): void
    {
        $valuesLc = StringList::unpack(mb_strtolower($value));

        foreach ($valuesLc as $valueLc) {
            self::assertArrayHasKey($valueLc, $checked,
                "Field {$field->name()} value '$value' NOT present in the I/U form HTML code");
            self::assertCount(1, $checked[$valueLc],
                "Field {$field->name()} value '$value' present more than once in the I/U form HTML code");
        }
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

    /**
     * @throws DataInputException|JsonException
     */
    private function performImport(): void
    {
        $output = new BufferedOutput();

        $printer = new Printer(new SymfonyStyle(new StringInput(''), $output));
        $import = new DataImport(self::$entityManager, $this->getImportManager(), $printer,
            static::$container->get(FdvFactory::class)->create($printer), false);

        $import->import(JsonFinder::arrayFromFiles(self::IMPORT_DATA_DIR));

        $this->validateConsoleOutput($output->fetch());
    }

    /**
     * @throws DataInputException|JsonException
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

    /**
     * @throws DataInputException|JsonException
     */
    private function getManagerCorrectionsFileContents(): string
    {
        $newMakerId = $this->getArtisan(self::VARIANT_FULL_DATA, self::SET)->getMakerId();
        $result = "ack new:$newMakerId:\n";

        foreach ($this->getImportDataFilesHashes() as $hash) {
            $result .= "set pin::$hash:\n";
        }

        return $result;
    }

    /**
     * @throws JsonException|DataInputException
     */
    private function getImportDataFilesHashes(): array
    {
        return array_map(function (array $data): string {
            return (new RawImportItem($data))->getHash();
        }, JsonFinder::arrayFromFiles(self::IMPORT_DATA_DIR));
    }

    private function validateArtisanAfterImport(Artisan $expected): void
    {
        $actual = static::$container->get(ArtisanRepository::class)->findOneBy(['makerId' => $expected->getMakerId()]);

        self::assertNotNull($actual);

        foreach (Fields::getAll() as $fieldName => $field) {
            if (self::SKIP !== self::FIELDS[$fieldName]) {
                self::assertEquals($expected->get($field), $actual->get($field), "Field {$fieldName} differs");
            }
        }
    }

    private function validateConsoleOutput(string $output): void
    {
        $output = str_replace("\r", "\n", $output);
        $output = pattern('^(OLD |NEW |IMP |replace:)[^\n]+\n+', 'm')->remove($output)->all();
        $output = pattern('^-+\n+', 'm')->remove($output)->all();

        $output = pattern('\[WARNING\]\s+?[a-zA-Z0-9 /\n]+?\s+?changed\s+?their\s+?maker\s+?ID\s+?from\s+?[A-Z0-9]{7}\s+?to\s+?[A-Z0-9]{7}')
            ->remove($output)->all();

        $header1 = StrUtils::artisanNamesSafeForCli($this->getArtisan(self::VARIANT_FULL_DATA, self::CHECK));
        $header2 = StrUtils::artisanNamesSafeForCli($this->getArtisan(self::VARIANT_HALF_DATA_2, self::CHECK));

        $output = str_replace([$header1, $header2], '', $output);

        $output = trim($output);

        self::assertEmpty($output, "Unexpected output in the console: \n".$output);
    }
}
