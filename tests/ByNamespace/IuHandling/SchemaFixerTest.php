<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\IuHandling;

use App\Data\Definitions\Fields\Fields;
use App\Entity\Submission;
use App\Utils\Json;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class SchemaFixerTest extends TestCase
{
    private const array PAYLOAD = [
        'MAKER_ID' => 'TESTING',
        'FORMER_MAKER_IDS' => [],
        'NAME' => 'Testing',
        'FORMERLY' => [],
        'DATE_ADDED' => 'unknown',
        'DATE_UPDATED' => 'unknown',
        'INTRO' => '',
        'SINCE' => '',
        'LANGUAGES' => [],
        'COUNTRY' => 'FI',
        'STATE' => '',
        'CITY' => '',
        'PRODUCTION_MODELS_COMMENT' => '',
        'PRODUCTION_MODELS' => [],
        'STYLES_COMMENT' => '',
        'STYLES' => [],
        'OTHER_STYLES' => [],
        'ORDER_TYPES_COMMENT' => '',
        'ORDER_TYPES' => [],
        'OTHER_ORDER_TYPES' => [],
        'FEATURES_COMMENT' => '',
        'FEATURES' => [],
        'OTHER_FEATURES' => [],
        'PAYMENT_PLANS' => [],
        'PAYMENT_METHODS' => [],
        'CURRENCIES_ACCEPTED' => [],
        'SPECIES_COMMENT' => '',
        'SPECIES_DOES' => [],
        'SPECIES_DOESNT' => [],
        'IS_MINOR' => null,
        'AGES' => 'MINORS',
        'NSFW_WEBSITE' => true,
        'NSFW_SOCIAL' => true,
        'DOES_NSFW' => null,
        'SAFE_DOES_NSFW' => false,
        'WORKS_WITH_MINORS' => null,
        'SAFE_WORKS_WITH_MINORS' => false,
        'URL_FURSUITREVIEW' => '',
        'URL_WEBSITE' => '',
        'URL_PRICES' => [],
        'URL_COMMISSIONS' => [],
        'URL_FAQ' => '',
        'URL_FUR_AFFINITY' => '',
        'URL_DEVIANTART' => '',
        'URL_MASTODON' => '',
        'URL_TWITTER' => '',
        'URL_FACEBOOK' => '',
        'URL_TUMBLR' => '',
        'URL_INSTAGRAM' => '',
        'URL_YOUTUBE' => '',
        'URL_LINKLIST' => '',
        'URL_FURRY_AMINO' => '',
        'URL_ETSY' => '',
        'URL_THE_DEALERS_DEN' => '',
        'URL_OTHER_SHOP' => '',
        'URL_QUEUE' => '',
        'URL_SCRITCH' => '',
        'URL_FURTRACK' => '',
        'URL_PHOTOS' => [],
        'URL_MINIATURES' => [],
        'URL_OTHER' => '',
        'NOTES' => 'MX testing',
        'INACTIVE_REASON' => '',
        'PASSWORD' => '...',
        'CS_LAST_CHECK' => 'unknown',
        'CS_TRACKER_ISSUE' => false,
        'OPEN_FOR' => [],
        'CLOSED_FOR' => [],
        'COMPLETENESS' => 0,
        'CONTACT_ALLOWED' => 'NO',
        'CONTACT_METHOD' => '',
        'CONTACT_ADDRESS_PLAIN' => '',
        'CONTACT_INFO_OBFUSCATED' => '',
        'CONTACT_INFO_ORIGINAL' => '',
        'SCHEMA_VERSION' => 13,
    ];

    public function testLegacyReviewPagesLoadProperly(): void
    {
        self::expectNotToPerformAssertions();

        // Should refactor... somehow. What are we testing here, schema fixer or submission data reader?
        $result = new Submission(false)->setPayload(Json::encode(self::PAYLOAD))->getReader();

        foreach (Fields::readFromSubmissionData() as $field) {
            $result->get($field);
        }
    }
}
