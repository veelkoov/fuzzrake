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
use App\Utils\DataInput\IuSubmission;
use App\Utils\DataInput\IuSubmissionFinder;
use App\Utils\DataInput\Manager;
use App\Utils\DataInput\RawImportItem;
use App\Utils\StringList;
use App\Utils\StrUtils;
use Doctrine\ORM\ORMException;
use InvalidArgumentException;
use JsonException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\DomCrawler\Form;
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
        'SINCE'                     => ['2020-10', '2020-11', '2020-12'],
        'LANGUAGES'                 => "Czech__VARIANT__ (limited)\nEnglish__VARIANT__",
        'COUNTRY'                   => 'C__VARIANT__',
        'STATE'                     => 'of mind __VARIANT__',
        'CITY'                      => 'Lisek __VARIANT__',
        'PAYMENT_PLANS'             => '30% upfront, rest in 100 Eur/mth until fully paid (__VARIANT__)',
        'PAYMENT_METHODS'           => "Cash\nBank transfer\nPalPay\nHugs___VARIANT__",
        'CURRENCIES_ACCEPTED'       => "USD\nEU__VARIANT__",
        'PRODUCTION_MODELS_COMMENT' => 'Comment about production models __VARIANT__',
        'PRODUCTION_MODELS'         => "Artistic liberty commissions\nPremades\nStandard commissions",
        'STYLES_COMMENT'            => 'Comment about styles __VARIANT__',
        'STYLES'                    => "Anime\nKemono\nKigurumi\nRealistic\nSemi Realistic\nSemi Toony\nToony",
        'OTHER_STYLES'              => 'OTHER_STYLES___VARIANT__',
        'ORDER_TYPES_COMMENT'       => 'Comment for order types __VARIANT__',
        'ORDER_TYPES'               => "Bodysuits (as parts/separate)\nFeetpaws (as parts/separate)\nFull digitigrade\nFull plantigrade\nHandpaws (as parts/separate)\nHead (as parts/separate)\nMini partial (head + handpaws + tail)\nPartial (head + handpaws + tail + feetpaws)\nTails (as parts/separate)\nThree-fourth (head + handpaws + tail + legs/pants + feetpaws)",
        'OTHER_ORDER_TYPES'         => 'OTHER_ORDER_TYPES___VARIANT__',
        'FEATURES_COMMENT'          => 'Comment about features __VARIANT__',
        'FEATURES'                  => "Adjustable eyebrows\nAdjustable/wiggle ears\nAttached handpaws and feetpaws\nAttached tail\nElectronics/animatronics\nExchangeable hairs\nExchangeable tongues\nFollow-me eyes\nIn-head fans\nIndoor feet\nLED eyes\nLED/EL lights\nMovable jaw\nOutdoor feet\nRemovable blush\nRemovable eyelids\nRemovable horns/antlers\nWashable heads",
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

            if (self::SKIP === $value) {
                continue;
            }

            if (in_array($fieldName, self::EXPANDED) && in_array($variant, [self::VARIANT_HALF_DATA_1, self::VARIANT_HALF_DATA_2])) {
                /* For testing purposes, select only either odd or even options available */
                $value = $this->selectOddOrEvenItems($value, self::VARIANT_HALF_DATA_1 === $variant);
            }

            $result->set(Fields::get($fieldName), str_replace('__VARIANT__', $variant, $value));
        }

        return $result;
    }

    private function processIuForm(KernelBrowser $client, string $urlMakerId, Artisan $oldData, Artisan $newData): void
    {
        $client->request('GET', $this->getIuFormUrlForMakerId($urlMakerId));

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        $this->verifyGeneratedIuForm($oldData, $client->getResponse()->getContent());

        $form = $client->getCrawler()->selectButton('Submit')->form();
        $this->setValuesInForm($form, $newData);
        $client->submit($form);

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
        $htmlBodyLc = $this->removeFalsePositivesFromLowercaseHtml(mb_strtolower($htmlBody));
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
                    if (Fields::SINCE == $fieldName) {
                        $this->validateSinceFieldInGeneratedIuForm($field, $htmlBodyLc, $value);
                    } else {
                        $this->validateNonListFieldInGeneratedIuForm($field, $htmlBodyLc, $value);
                    }
                }
            }
        }
    }

    private function removeFalsePositivesFromLowercaseHtml(string $inputLowercaseHtml): string
    {
        $result = pattern('(<label[^>]*>[^<]+</label>)')->remove($inputLowercaseHtml)->all();
        $result = pattern('<select id="iu_form_since_day"[^>]*><option value="">day</option>(<option value="\d{1,2}"( selected="selected")?>\d{2}</option>)+</select>')
            ->remove($result)->first();

        return $result;
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

    private function validateSinceFieldInGeneratedIuForm(Field $field, string $htmlBodyLc, $value)
    {
        list($year, $month) = explode('-', $value);

        $this->validateNonListFieldInGeneratedIuForm($field, $htmlBodyLc, $year);
        $this->validateNonListFieldInGeneratedIuForm($field, $htmlBodyLc, $month);
    }

    private function setValuesInForm(Form $form, Artisan $data): void
    {
        foreach (Fields::getAll() as $fieldName => $field) {
            if (in_array($fieldName, self::FIELD_NOT_IN_FORM)) {
                continue;
            }

            $value = $data->get($field);
            $fields = $form["iu_form[{$field->modelName()}]"];

            if (Fields::SINCE === $fieldName) {
                $this->setValuesInSinceField($value, $fields);
            } elseif (in_array($fieldName, self::EXPANDED)) {
                $this->setValuesInExpandedField($value, $fields);
            } else {
                $fields->setValue($value);
            }
        }
    }

    /**
     * @param FormField[] $fields
     */
    private function setValuesInSinceField(string $value, array $fields): void
    {
        list($year, $month) = explode('-', $value);

        if (!($fields['year'] instanceof ChoiceFormField) || !($fields['month'] instanceof ChoiceFormField) || !($fields['day'] instanceof ChoiceFormField)) {
            throw new InvalidArgumentException('Expected array of '.ChoiceFormField::class);
        }

        $fields['year']->select($year);
        $fields['month']->select($month);
        $fields['day']->select('1'); // grep-default-auto-since-day-01
    }

    private function setValuesInExpandedField(string $value, array $fields): void
    {
        $values = StringList::unpack($value);

        foreach ($fields as $formField) {
            if (!($formField instanceof ChoiceFormField)) {
                throw new InvalidArgumentException('Expected choice field');
            }

            if (in_array($formField->availableOptionValues()[0], $values)) {
                $formField->tick();
            } else {
                $formField->untick();
            }
        }
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

        $import->import(IuSubmissionFinder::getFrom(self::IMPORT_DATA_DIR));

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

        foreach ($this->getIuSubmissionsIds() as $hash) {
            $result .= "set pin::$hash:\n";
        }

        return $result;
    }

    /**
     * @throws JsonException|DataInputException
     */
    private function getIuSubmissionsIds(): array
    {
        return array_map(function (IuSubmission $submission): string {
            return $submission->getId();
        }, IuSubmissionFinder::getFrom(self::IMPORT_DATA_DIR));
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

    private function selectOddOrEvenItems(string $itemsListPacked, bool $selectOdd): string
    {
        $values = StringList::unpack($itemsListPacked);

        $rest = $selectOdd ? 0 : 1;

        for ($i = 0, $c = count($values); $i < $c; ++$i) {
            if ($rest === $i % 2) {
                unset($values[$i]);
            }
        }

        return StringList::pack($values);
    }
}
