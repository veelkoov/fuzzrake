<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Entity\Artisan;
use App\Tests\Controller\DbEnabledWebTestCase;
use App\Utils\Artisan\Fields;
use Doctrine\ORM\ORMException;

class IuSubmissionTest extends DbEnabledWebTestCase
{
    private const INITIAL_VALUE = 0;
    private const VALUE_EXPECTED_IN_FORM = 1;

    private const SKIP = 'SKIP_FIELD_CHECK';

    private const FIELDS = [ // TODO: Make values something more specific
        'MAKER_ID'                  => ['MAKERI1', true],
        'PASSCODE'                  => ['Passcode 1', false],
        'CONTACT_INFO_OBFUSCATED'   => ['Contact info obfuscated 1', true],
        'CONTACT_INFO_ORIGINAL'     => ['Contact info original 1', false],

        'FORMER_MAKER_IDS'          => ['MAKERID\nMAKERI0', false],
        'NAME'                      => ['Turbopumpernikiel', true],
        'FORMERLY'                  => ['Ultrapumpernikiel\nSzyciciel', true],
        'INTRO'                     => ['Le intro', true],
        'SINCE'                     => ['2020-07', true],
        'LANGUAGES'                 => ['English\nCzech (limited)', true],
        'COUNTRY'                   => ['CZ', true],
        'STATE'                     => ['of mind', true],
        'CITY'                      => ['Lisek', true],
        'PAYMENT_PLANS'             => ['30% upfront, rest in 100 Eur/mth until fully paid', true],
        'PAYMENT_METHODS'           => ['Cash\nBank transfer\nPalPay\nHugs', true],
        'CURRENCIES_ACCEPTED'       => ['USD\nEUR', true],
        'PRODUCTION_MODELS_COMMENT' => ['Prod mod com', false], // TODO
        'PRODUCTION_MODELS'         => ['Standard commissions', true],
        'STYLES_COMMENT'            => ['STYLES_COMMENT', false], // TODO
        'STYLES'                    => ['STYLES', true],
        'OTHER_STYLES'              => ['OTHER_STYLES', true],
        'ORDER_TYPES_COMMENT'       => ['ORDER_TYPES_COMMENT', false], // TODO
        'ORDER_TYPES'               => ['ORDER_TYPES', true],
        'OTHER_ORDER_TYPES'         => ['OTHER_ORDER_TYPES', true],
        'FEATURES_COMMENT'          => ['FEATURES_COMMENT', false], // TODO
        'FEATURES'                  => ['FEATURES', true],
        'OTHER_FEATURES'            => ['OTHER_FEATURES', true],
        'SPECIES_COMMENT'           => ['SPECIES_COMMENT', true], // TODO
        'SPECIES_DOES'              => ['SPECIES_DOES', true],
        'SPECIES_DOESNT'            => ['SPECIES_DOESNT', true],
        'URL_FURSUITREVIEW'         => ['http://fursuitreview.com/value_1.html', true],
        'URL_WEBSITE'               => ['https://mywebsite.com/value_1.html', true],
        'URL_PRICES'                => ['https://mywebsite.com/prices_1.html', true],
        'URL_FAQ'                   => ['https://mywebsite.com/faq_1.html', true],
        'URL_FUR_AFFINITY'          => ['http://furaffinity.com/value_1.html', true],
        'URL_DEVIANTART'            => ['https://deviantart.com/value_1.html', true],
        'URL_TWITTER'               => ['https://twitter.com/value_1.html', true],
        'URL_FACEBOOK'              => ['https://facebook.com/value_1.html', true],
        'URL_TUMBLR'                => ['https://tumblr.com/value_1.html', true],
        'URL_INSTAGRAM'             => ['https://instagram.com/value_1.html', true],
        'URL_YOUTUBE'               => ['https://youtube.com/value_1.html', true],
        'URL_LINKTREE'              => ['https://linktreee.com/value_1.html', false], // TODO
        'URL_FURRY_AMINO'           => ['https://furryamino.com/value_1.html', true],
        'URL_ETSY'                  => ['https://etsy.com/value_1.html', true],
        'URL_THE_DEALERS_DEN'       => ['https://tdealrsdn.com/value_1.html', true],
        'URL_OTHER_SHOP'            => ['https://othershop.com/value_1.html', false], // TODO
        'URL_QUEUE'                 => ['https://queue.com/value_1.html', true],
        'URL_SCRITCH'               => ['https://scritch.com/value_1.html', true],
        'URL_SCRITCH_PHOTO'         => ['https://scritchphotos.com/value_1.html', true],
        'URL_SCRITCH_MINIATURE'     => ['https://scritchphotosmini.com/value_1.html', false],
        'URL_OTHER'                 => ['https://other.com/value_1.html', true],
        'URL_CST'                   => ['https://cst.com/value_1.html', true],
        'NOTES'                     => ['NOTES', true],
        'INACTIVE_REASON'           => ['INACTIVE_REASON', false],
        'COMMISSIONS_STATUS'        => [self::SKIP, false],
        'CST_LAST_CHECK'            => [self::SKIP, false],
        'COMPLETENESS'              => [self::SKIP, false],
        'CONTACT_ALLOWED'           => ['CONTACT_ALLOWED', false],
        'CONTACT_METHOD'            => ['CONTACT_METHOD', false],
        'CONTACT_ADDRESS_PLAIN'     => ['CONTACT_ADDRESS_PLAIN', false],
    ];

    /**
     * Purpose of this test is to make sure:
     * - all fields, which values should be displayed in the I/U form, are,
     * - all fields, which values should NOT be displayed, are not,
     * - no newly added field gets overseen in the I/U form,
     * - TODO: all data submitted in the form is saved in the submission.
     *
     * @throws ORMException
     */
    public function testIuSubmissionFlow(): void
    {
        $client = static::createClient();
        $artisan = $this->getTestArtisan();

        self::$entityManager->persist($artisan);
        self::$entityManager->flush();

        $client->request('GET', '/iu_form/'.self::FIELDS['MAKER_ID'][self::INITIAL_VALUE]);
        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $body = $client->getResponse()->getContent();

        foreach (Fields::getAll() as $fieldName => $field) {
            self::assertArrayHasKey($fieldName, self::FIELDS);

            $fieldTestData = self::FIELDS[$fieldName];

            if (self::SKIP === $fieldTestData[self::INITIAL_VALUE]) {
                continue;
            }

            if ($fieldTestData[self::VALUE_EXPECTED_IN_FORM]) {
                self::assertStringContainsString($fieldTestData[self::INITIAL_VALUE], $body,
                    "Field $fieldName value NOT found in the I/U form HTML code"); // TODO: Check COUNT of encounters
            } else {
                self::assertStringNotContainsStringIgnoringCase($fieldTestData[self::INITIAL_VALUE], $body,
                    "Field $fieldName value FOUND in the I/U form HTML code");
            }
        }
    }

    private function getTestArtisan(): Artisan
    {
        $result = new Artisan();

        foreach (self::FIELDS as $fieldName => $fieldTestData) {
            if (self::SKIP !== $fieldTestData[self::INITIAL_VALUE]) {
                $result->set(Fields::get($fieldName), $fieldTestData[self::INITIAL_VALUE]);
            }
        }

        return $result;
    }
}
