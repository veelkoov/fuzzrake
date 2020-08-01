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

    private const FIELDS = [
        'MAKER_ID'                => ['MAKERI1', true],
        'PASSCODE'                => ['Passcode 1', false],
        'CONTACT_INFO_OBFUSCATED' => ['Contact info obfuscated 1', true],
        'CONTACT_INFO_ORIGINAL'   => ['Contact info original 1', false],
        'URL_FURSUITREVIEW'       => ['http://fursuitreview.com/value_1.html', true],
        'URL_WEBSITE'             => ['https://mywebsite.com/value_1.html', true],
        'URL_PRICES'              => ['https://mywebsite.com/prices_1.html', true],
        'URL_FAQ'                 => ['https://mywebsite.com/faq_1.html', true],
        'URL_FUR_AFFINITY'        => ['http://furaffinity.com/value_1.html', true],
        'URL_DEVIANTART'          => ['https://deviantart.com/value_1.html', true],
        'URL_TWITTER'             => ['https://twitter.com/value_1.html', true],
        'URL_FACEBOOK'            => ['https://facebook.com/value_1.html', true],
        'URL_TUMBLR'              => ['https://tumblr.com/value_1.html', true],
        'URL_INSTAGRAM'           => ['https://instagram.com/value_1.html', true],
        'URL_YOUTUBE'             => ['https://youtube.com/value_1.html', true],
        'URL_LINKTREE'            => ['https://linktreee.com/value_1.html', false], // TODO
        'URL_FURRY_AMINO'         => ['https://furryamino.com/value_1.html', true],
        'URL_ETSY'                => ['https://etsy.com/value_1.html', true],
        'URL_THE_DEALERS_DEN'     => ['https://tdealrsdn.com/value_1.html', true],
        'URL_OTHER_SHOP'          => ['https://othershop.com/value_1.html', false], // TODO
        'URL_QUEUE'               => ['https://queue.com/value_1.html', true],
        'URL_SCRITCH'             => ['https://scritch.com/value_1.html', true],
        'URL_SCRITCH_PHOTO'       => ['https://scritchphotos.com/value_1.html', true],
        'URL_SCRITCH_MINIATURE'   => ['https://scritchphotosmini.com/value_1.html', false],
        'URL_OTHER'               => ['https://other.com/value_1.html', true],
        'URL_CST'                 => ['https://cst.com/value_1.html', true],
    ];

    /**
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
            if (!array_key_exists($fieldName, self::FIELDS)) {
                continue; // FIXME: Get rid of, complete test
            }

            self::assertArrayHasKey($fieldName, self::FIELDS);

            $fieldTestData = self::FIELDS[$fieldName];

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
            $result->set(Fields::get($fieldName), $fieldTestData[self::INITIAL_VALUE]);
        }

        return $result;
    }
}
