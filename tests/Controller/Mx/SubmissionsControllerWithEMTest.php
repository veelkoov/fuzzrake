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
            ->setFeatures(Features::MOVABLE_JAW."\n".Features::FOLLOW_ME_EYES)
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

        self::assertSelectorExists('tr.MAKER_ID.submitted.submitted-same.not-fixed.not-changing');

        self::assertSelectorExists('tr.NAME.submitted.submitted-different.not-fixed.changing');
        self::assertSelectorExists('tr.COUNTRY.submitted.submitted-different.fixes-applied.not-changing');
        self::assertSelectorExists('tr.URL_TWITTER.submitted.submitted-different.fixes-applied.changing');

        self::assertSelectorExists('tr.PRODUCTION_MODELS.submitted.submitted-same.not-fixed.not-changing');

        self::assertSelectorExists('tr.FEATURES.submitted.submitted-different.not-fixed.changing');
        self::assertSelectorExists('tr.OTHER_ORDER_TYPES.submitted.submitted-different.fixes-applied.not-changing');
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
