<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\DataDefinitions\Features;
use App\DataDefinitions\ProductionModels;
use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use App\Tests\TestUtils\Submissions;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use JsonException;
use Symfony\Component\Uid\Uuid;

class SubmissionsControllerWithEMTest extends WebTestCaseWithEM
{
    protected function setUp(): void
    {
        parent::setUp();

        Submissions::emptyTestSubmissionsDir();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Submissions::emptyTestSubmissionsDir();
    }

    /**
     * @throws JsonException
     */
    public function testLast20SubmissionsBeingShown(): void
    {
        $client = self::createClient();

        $this->generateRandomFakeSubmissions(19);

        $crawler = $client->request('GET', '/mx/submissions/');
        self::assertCount(19, $crawler->filter('table tbody tr'));

        $this->generateRandomFakeSubmissions(2);

        $crawler = $client->request('GET', '/mx/submissions/');
        self::assertCount(20, $crawler->filter('table tbody tr'));
    }

    /**
     * @throws JsonException
     */
    public function testChangesAreProperlyMarked(): void
    {
        $client = self::createClient();

        $entity = (new Artisan())
            ->setMakerId('MAKERID')
            ->setName('Some testing maker')
            ->setCountry('FI')
            ->setFeatures(Features::FOLLOW_ME_EYES."\n".Features::MOVABLE_JAW)
            ->setOtherFeatures("Hidden pocket\nSqueaker in nose")
            ->setProductionModels(ProductionModels::STANDARD_COMMISSIONS)
            ->setOtherOrderTypes('Arm sleeves')
        ;

        self::persistAndFlush($entity);

        $submission = (new Artisan())
            ->setMakerId('MAKERID') // Unchanged
            ->setName('Changed name') // Changed, no fixer
            ->setFormerly('Some testing maker')
            ->setCountry('Finland') // Changed, fixed, result same
            ->setTwitterUrl('http://www.twitter.com/getfursuit') // Changed, fixed, result different
            ->setFeatures(Features::FOLLOW_ME_EYES) // Changed, no fixer
            ->setOtherFeatures('Hidden pockets') // Changed, fixed, result changed
            ->setProductionModels(ProductionModels::STANDARD_COMMISSIONS) // Unchanged
            ->setOtherOrderTypes('Armsleeves')
        ;

        $id = Submissions::submit($submission);

        $client->request('GET', "/mx/submissions/$id");

        self::assertSelectorTextContains('tr.MAKER_ID.current', 'MAKERID');
        self::assertSelectorTextContains('tr.MAKER_ID.submitted', 'MAKERID');
        self::assertSelectorTextContains('tr.MAKER_ID.changed', 'MAKERID');
        self::assertSelectorExists('tr.MAKER_ID.submitted.submitted-same.not-fixed.not-changing');

        self::assertSelectorTextContains('tr.NAME.current', 'Some testing maker');
        self::assertSelectorTextContains('tr.NAME.submitted', 'Changed name');
        self::assertSelectorTextContains('tr.NAME.changed', 'Changed name');
        self::assertSelectorExists('tr.NAME.submitted.submitted-different.not-fixed.changing');

        self::assertSelectorTextContains('tr.COUNTRY.current', 'FI');
        self::assertSelectorTextContains('tr.COUNTRY.submitted', 'Finland');
        self::assertSelectorTextContains('tr.COUNTRY.changed', 'FI');
        self::assertSelectorExists('tr.COUNTRY.submitted.submitted-different.fixes-applied.not-changing');

        self::assertSelectorTextContains('tr.URL_TWITTER.current', ''); // FIXME: This may not work as expected
        self::assertSelectorTextContains('tr.URL_TWITTER.submitted', 'http://www.twitter.com/getfursuit');
        self::assertSelectorTextContains('tr.URL_TWITTER.changed', 'https://twitter.com/getfursuit');
        self::assertSelectorExists('tr.URL_TWITTER.submitted.submitted-different.fixes-applied.changing');

        self::assertSelectorTextContains('tr.PRODUCTION_MODELS.current', 'Standard commissions');
        self::assertSelectorTextContains('tr.PRODUCTION_MODELS.submitted', 'Standard commissions');
        self::assertSelectorTextContains('tr.PRODUCTION_MODELS.changed', 'Standard commissions');
        self::assertSelectorExists('tr.PRODUCTION_MODELS.submitted.submitted-same.not-fixed.not-changing');

        self::assertSelectorTextContains('tr.FEATURES.current', 'Follow-me eyes Movable jaw');
        self::assertSelectorTextContains('tr.FEATURES.submitted', 'Follow-me eyes');
        self::assertSelectorTextContains('tr.FEATURES.changed', 'Follow-me eyes');
        self::assertSelectorExists('tr.FEATURES.submitted.submitted-different.not-fixed.changing');

        self::assertSelectorTextContains('tr.OTHER_ORDER_TYPES.current', 'Arm sleeves');
        self::assertSelectorTextContains('tr.OTHER_ORDER_TYPES.submitted', 'Armsleeves');
        self::assertSelectorTextContains('tr.OTHER_ORDER_TYPES.changed', 'Arm sleeves');
        self::assertSelectorExists('tr.OTHER_ORDER_TYPES.submitted.submitted-different.fixes-applied.not-changing');

        self::assertSelectorTextContains('tr.OTHER_FEATURES.current', 'Hidden pocket Squeaker in nose');
        self::assertSelectorTextContains('tr.OTHER_FEATURES.submitted', 'Hidden pockets');
        self::assertSelectorTextContains('tr.OTHER_FEATURES.changed', 'Hidden pocket');
        self::assertSelectorExists('tr.OTHER_FEATURES.submitted.submitted-different.fixes-applied.changing');
    }

    // TODO: Test submission with multiple matched makers

    /**
     * @throws JsonException
     */
    private function generateRandomFakeSubmissions(int $count): void
    {
        while (--$count >= 0) {
            $artisan = new Artisan();
            $artisan->setName(Uuid::v4()->toRfc4122());

            Submissions::submit($artisan);
        }
    }
}
