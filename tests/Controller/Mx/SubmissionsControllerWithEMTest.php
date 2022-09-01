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
    public function testAdditionIsProperlyRendered(): void
    {
        $client = self::createClient();

        $submission = (new Artisan())
            // NOT fixed
            ->setMakerId('MAKERID')

            // Fixed
            ->setCountry('Finland')

            // Fixed
            ->setTwitterUrl('http://www.twitter.com/getfursuit')

            // NOT fixed
            ->setFeatures(Features::FOLLOW_ME_EYES)

            // Fixed
            ->setOtherFeatures('Hidden pockets')
        ;

        $id = Submissions::submit($submission);

        $client->request('GET', "/mx/submissions/$id");

        self::assertSelectorNotExists('tr.MAKER_ID.before');
        self::assertSelectorTextSame('tr.MAKER_ID.submitted td+td', 'MAKERID');
        self::assertSelectorTextSame('tr.MAKER_ID.after td+td', 'MAKERID');
        self::assertSelectorExists('tr.MAKER_ID.submitted-different.not-fixed.changing');

        self::assertSelectorNotExists('tr.COUNTRY.before');
        self::assertSelectorTextSame('tr.COUNTRY.submitted td+td', 'Finland');
        self::assertSelectorTextSame('tr.COUNTRY.after td+td', 'FI');
        self::assertSelectorExists('tr.COUNTRY.submitted-different.fixes-applied.changing');

        self::assertSelectorNotExists('tr.URL_TWITTER.before');
        self::assertSelectorTextSame('tr.URL_TWITTER.submitted td+td', 'http://www.twitter.com/getfursuit');
        self::assertSelectorTextSame('tr.URL_TWITTER.after td+td', 'https://twitter.com/getfursuit');
        self::assertSelectorExists('tr.URL_TWITTER.submitted-different.fixes-applied.changing');

        self::assertSelectorNotExists('tr.FEATURES.before');
        self::assertSelectorTextSame('tr.FEATURES.submitted td+td', 'Follow-me eyes');
        self::assertSelectorTextSame('tr.FEATURES.after td+td', 'Follow-me eyes');
        self::assertSelectorExists('tr.FEATURES.submitted-different.not-fixed.changing');

        self::assertSelectorNotExists('tr.OTHER_FEATURES.before');
        self::assertSelectorTextSame('tr.OTHER_FEATURES.submitted td+td', 'Hidden pockets');
        self::assertSelectorTextSame('tr.OTHER_FEATURES.after td+td', 'Hidden pocket');
        self::assertSelectorExists('tr.OTHER_FEATURES.submitted-different.fixes-applied.changing');
    }

    /**
     * @throws JsonException
     */
    public function testUpdateIsProperlyRendered(): void
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
            ->setCurrenciesAccepted('Euro')
        ;

        self::persistAndFlush($entity);

        $submission = (new Artisan())
            // Submitted the same, NOT fixed, NOT changed
            ->setMakerId('MAKERID')

            // Submitted different, NOT fixed, changed
            ->setName('Changed name')

            // Submitted different, NOT fixed, changed / not tested
            ->setFormerly('Some testing maker')

            // Submitted different, fixed, NOT changed
            ->setCountry('Finland')

            // Submitted different, fixed, changed
            ->setTwitterUrl('http://www.twitter.com/getfursuit')

            // Submitted different, NOT fixed, changed
            ->setFeatures(Features::FOLLOW_ME_EYES)

            // Submitted different, fixed, changed
            ->setOtherFeatures('Hidden pockets')

            // Submitted the same, NOT fixed, NOT changed
            ->setProductionModels(ProductionModels::STANDARD_COMMISSIONS)

            // Submitted different, fixed, NOT changed
            ->setOtherOrderTypes('Armsleeves')

            // Submitted the same, fixed, changed
            ->setCurrenciesAccepted('Euro')
        ;

        $id = Submissions::submit($submission);

        $client->request('GET', "/mx/submissions/$id");

        self::assertSelectorTextSame('tr.MAKER_ID.before td+td', 'MAKERID');
        self::assertSelectorTextSame('tr.MAKER_ID.submitted td+td', 'MAKERID');
        self::assertSelectorTextSame('tr.MAKER_ID.after td+td', 'MAKERID');
        self::assertSelectorExists('tr.MAKER_ID.submitted-same.not-fixed.not-changing');

        self::assertSelectorTextSame('tr.NAME.before td+td', 'Some testing maker');
        self::assertSelectorTextSame('tr.NAME.submitted td+td', 'Changed name');
        self::assertSelectorTextSame('tr.NAME.after td+td', 'Changed name');
        self::assertSelectorExists('tr.NAME.submitted-different.not-fixed.changing');

        self::assertSelectorTextSame('tr.COUNTRY.before td+td', 'FI');
        self::assertSelectorTextSame('tr.COUNTRY.submitted td+td', 'Finland');
        self::assertSelectorTextSame('tr.COUNTRY.after td+td', 'FI');
        self::assertSelectorExists('tr.COUNTRY.submitted-different.fixes-applied.not-changing');

        self::assertSelectorTextSame('tr.URL_TWITTER.before td+td', '');
        self::assertSelectorTextSame('tr.URL_TWITTER.submitted td+td', 'http://www.twitter.com/getfursuit');
        self::assertSelectorTextSame('tr.URL_TWITTER.after td+td', 'https://twitter.com/getfursuit');
        self::assertSelectorExists('tr.URL_TWITTER.submitted-different.fixes-applied.changing');

        self::assertSelectorTextSame('tr.FEATURES.before td+td', 'Follow-me eyes Movable jaw');
        self::assertSelectorTextSame('tr.FEATURES.submitted td+td', 'Follow-me eyes');
        self::assertSelectorTextSame('tr.FEATURES.after td+td', 'Follow-me eyes');
        self::assertSelectorExists('tr.FEATURES.submitted-different.not-fixed.changing');

        self::assertSelectorTextSame('tr.OTHER_FEATURES.before td+td', 'Hidden pocket Squeaker in nose');
        self::assertSelectorTextSame('tr.OTHER_FEATURES.submitted td+td', 'Hidden pockets');
        self::assertSelectorTextSame('tr.OTHER_FEATURES.after td+td', 'Hidden pocket');
        self::assertSelectorExists('tr.OTHER_FEATURES.submitted-different.fixes-applied.changing');

        self::assertSelectorTextSame('tr.PRODUCTION_MODELS.before td+td', 'Standard commissions');
        self::assertSelectorTextSame('tr.PRODUCTION_MODELS.submitted td+td', 'Standard commissions');
        self::assertSelectorTextSame('tr.PRODUCTION_MODELS.after td+td', 'Standard commissions');
        self::assertSelectorExists('tr.PRODUCTION_MODELS.submitted-same.not-fixed.not-changing');

        self::assertSelectorTextSame('tr.OTHER_ORDER_TYPES.before td+td', 'Arm sleeves');
        self::assertSelectorTextSame('tr.OTHER_ORDER_TYPES.submitted td+td', 'Armsleeves');
        self::assertSelectorTextSame('tr.OTHER_ORDER_TYPES.after td+td', 'Arm sleeves');
        self::assertSelectorExists('tr.OTHER_ORDER_TYPES.submitted-different.fixes-applied.not-changing');

        self::assertSelectorTextSame('tr.CURRENCIES_ACCEPTED.before td+td', 'Euro');
        self::assertSelectorTextSame('tr.CURRENCIES_ACCEPTED.submitted td+td', 'Euro');
        self::assertSelectorTextSame('tr.CURRENCIES_ACCEPTED.after td+td', 'EUR');
        self::assertSelectorExists('tr.CURRENCIES_ACCEPTED.submitted-same.fixes-applied.changing');
    }

    /**
     * @throws JsonException
     */
    public function testSubmissionMatchingMultipleMakers(): void
    {
        $client = self::createClient();

        $entity1 = (new Artisan())
            ->setMakerId('MAKERID')
            ->setName('Some testing maker')
        ;
        $entity2 = (new Artisan())
            ->setMakerId('MAKERI2')
            ->setName('Testing maker')
        ;

        self::persistAndFlush($entity1, $entity2);

        $submission = (new Artisan())
            ->setMakerId('MAKERID')
            ->setName('Testing maker')
        ;

        $id = Submissions::submit($submission);

        $client->request('GET', "/mx/submissions/$id");

        self::assertSelectorTextSame('p', 'Matched multiple makers: Some testing maker (MAKERID), Testing maker (MAKERI2). Unable to continue.');

        // TODO: Consider some more safety measures?
    }

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
