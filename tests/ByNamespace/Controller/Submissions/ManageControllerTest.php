<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller\Submissions;

use App\Data\Definitions\Features;
use App\Data\Definitions\ProductionModels;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\Cases\Traits\MocksTrait;
use App\Tests\TestUtils\UserCreator;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Override;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class ManageControllerTest extends FuzzrakeWebTestCase
{
    use MocksTrait;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::haveACreatorUser();
        self::haveAnAdminUser();
        self::loginAdminUser();
    }

    public function testAdditionIsProperlyRendered(): void
    {
        $submissionData = new Creator()
            // NOT fixed
            ->setCreatorId('TEST001')

            // Fixed
            ->setCountry('Finland')

            // Fixed
            ->setTwitterUrl('http://www.twitter.com/getfursuit')

            // NOT fixed
            ->setFeatures([Features::FOLLOW_ME_EYES])

            // Fixed
            ->setOtherFeatures(['Hidden pockets'])
        ;
        $submission = $this->getEntityForSubmission(self::getCreatorUser(), $submissionData, false);
        self::persistAndFlush($submission);

        self::$client->request('GET', "/submission/{$submission->getId()}/manage");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorNotExists('tr.MAKER_ID.before');
        self::assertSelectorTextSame('tr.MAKER_ID.submitted td+td+td', 'TEST001');
        self::assertSelectorTextSame('tr.MAKER_ID.after td+td+td', 'TEST001');
        self::assertSelectorExists('tr.MAKER_ID.submitted-different.not-fixed.changing');

        self::assertSelectorNotExists('tr.COUNTRY.before');
        self::assertSelectorTextSame('tr.COUNTRY.submitted td+td+td', 'Finland');
        self::assertSelectorTextSame('tr.COUNTRY.after td+td+td', 'FI');
        self::assertSelectorExists('tr.COUNTRY.submitted-different.fixes-applied.changing');

        self::assertSelectorNotExists('tr.URL_TWITTER.before');
        self::assertSelectorTextSame('tr.URL_TWITTER.submitted td+td+td', 'http://www.twitter.com/getfursuit');
        self::assertSelectorTextSame('tr.URL_TWITTER.after td+td+td', 'https://twitter.com/getfursuit');
        self::assertSelectorExists('tr.URL_TWITTER.submitted-different.fixes-applied.changing');

        self::assertSelectorNotExists('tr.FEATURES.before');
        self::assertSelectorTextSame('tr.FEATURES.submitted td+td+td', 'Follow-me eyes');
        self::assertSelectorTextSame('tr.FEATURES.after td+td+td', 'Follow-me eyes');
        self::assertSelectorExists('tr.FEATURES.submitted-different.not-fixed.changing');

        self::assertSelectorNotExists('tr.OTHER_FEATURES.before');
        self::assertSelectorTextSame('tr.OTHER_FEATURES.submitted td+td+td', 'Hidden pockets');
        self::assertSelectorTextSame('tr.OTHER_FEATURES.after td+td+td', 'Hidden pocket');
        self::assertSelectorExists('tr.OTHER_FEATURES.submitted-different.fixes-applied.changing');
    }

    public function testUpdateIsProperlyRendered(): void
    {
        $creator = new Creator(user: self::getCreatorUser())
            ->setCreatorId('TEST001')
            ->setName('Some testing creator')
            ->setCountry('FI')
            ->setFeatures([Features::FOLLOW_ME_EYES, Features::MOVABLE_JAW])
            ->setOtherFeatures(['Hidden pocket', 'Squeaker in nose'])
            ->setProductionModels([ProductionModels::STANDARD_COMMISSIONS])
            ->setOtherOrderTypes(['Arm sleeves'])
            ->setCurrenciesAccepted(['Euro'])
        ;

        self::persistAndFlush($creator);

        $submissionData = new Creator()
            // Submitted the same, NOT fixed, NOT changed
            ->setCreatorId('TEST001')

            // Submitted different, NOT fixed, changed
            ->setName('Changed name')

            // Submitted different, NOT fixed, changed / not tested
            ->setFormerly(['Some testing creator'])

            // Submitted different, fixed, NOT changed
            ->setCountry('Finland')

            // Submitted different, fixed, changed
            ->setTwitterUrl('http://www.twitter.com/getfursuit')

            // Submitted different, NOT fixed, changed
            ->setFeatures([Features::FOLLOW_ME_EYES])

            // Submitted different, fixed, changed
            ->setOtherFeatures(['Hidden pockets'])

            // Submitted the same, NOT fixed, NOT changed
            ->setProductionModels([ProductionModels::STANDARD_COMMISSIONS])

            // Submitted different, fixed, NOT changed
            ->setOtherOrderTypes(['Armsleeves'])

            // Submitted the same, fixed, changed
            ->setCurrenciesAccepted(['Euro'])
        ;

        $submission = $this->getEntityForSubmission(self::getCreatorUser(), $submissionData, true);
        self::persistAndFlush($submission);

        self::$client->request('GET', "/submission/{$submission->getId()}/manage");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorTextSame('tr.MAKER_ID.before td+td+td', 'TEST001');
        self::assertSelectorTextSame('tr.MAKER_ID.submitted td+td+td', 'TEST001');
        self::assertSelectorTextSame('tr.MAKER_ID.after td+td+td', 'TEST001');
        self::assertSelectorExists('tr.MAKER_ID.submitted-same.not-fixed.not-changing');

        self::assertSelectorTextSame('tr.NAME.before td+td+td', 'Some testing creator');
        self::assertSelectorTextSame('tr.NAME.submitted td+td+td', 'Changed name');
        self::assertSelectorTextSame('tr.NAME.after td+td+td', 'Changed name');
        self::assertSelectorExists('tr.NAME.submitted-different.not-fixed.changing');

        self::assertSelectorTextSame('tr.COUNTRY.before td+td+td', 'FI');
        self::assertSelectorTextSame('tr.COUNTRY.submitted td+td+td', 'Finland');
        self::assertSelectorTextSame('tr.COUNTRY.after td+td+td', 'FI');
        self::assertSelectorExists('tr.COUNTRY.submitted-different.fixes-applied.not-changing');

        self::assertSelectorTextSame('tr.URL_TWITTER.before td+td+td', '');
        self::assertSelectorTextSame('tr.URL_TWITTER.submitted td+td+td', 'http://www.twitter.com/getfursuit');
        self::assertSelectorTextSame('tr.URL_TWITTER.after td+td+td', 'https://twitter.com/getfursuit');
        self::assertSelectorExists('tr.URL_TWITTER.submitted-different.fixes-applied.changing');

        self::assertSelectorTextSame('tr.FEATURES.before td+td+td', 'Follow-me eyes Movable jaw');
        self::assertSelectorTextSame('tr.FEATURES.submitted td+td+td', 'Follow-me eyes');
        self::assertSelectorTextSame('tr.FEATURES.after td+td+td', 'Follow-me eyes');
        self::assertSelectorExists('tr.FEATURES.submitted-different.not-fixed.changing');

        self::assertSelectorTextSame('tr.OTHER_FEATURES.before td+td+td', 'Hidden pocket Squeaker in nose');
        self::assertSelectorTextSame('tr.OTHER_FEATURES.submitted td+td+td', 'Hidden pockets');
        self::assertSelectorTextSame('tr.OTHER_FEATURES.after td+td+td', 'Hidden pocket');
        self::assertSelectorExists('tr.OTHER_FEATURES.submitted-different.fixes-applied.changing');

        self::assertSelectorTextSame('tr.PRODUCTION_MODELS.before td+td+td', 'Standard commissions');
        self::assertSelectorTextSame('tr.PRODUCTION_MODELS.submitted td+td+td', 'Standard commissions');
        self::assertSelectorTextSame('tr.PRODUCTION_MODELS.after td+td+td', 'Standard commissions');
        self::assertSelectorExists('tr.PRODUCTION_MODELS.submitted-same.not-fixed.not-changing');

        self::assertSelectorTextSame('tr.OTHER_ORDER_TYPES.before td+td+td', 'Arm sleeves');
        self::assertSelectorTextSame('tr.OTHER_ORDER_TYPES.submitted td+td+td', 'Armsleeves');
        self::assertSelectorTextSame('tr.OTHER_ORDER_TYPES.after td+td+td', 'Arm sleeves');
        self::assertSelectorExists('tr.OTHER_ORDER_TYPES.submitted-different.fixes-applied.not-changing');

        self::assertSelectorTextSame('tr.CURRENCIES_ACCEPTED.before td+td+td', 'Euro');
        self::assertSelectorTextSame('tr.CURRENCIES_ACCEPTED.submitted td+td+td', 'Euro');
        self::assertSelectorTextSame('tr.CURRENCIES_ACCEPTED.after td+td+td', 'EUR');
        self::assertSelectorExists('tr.CURRENCIES_ACCEPTED.submitted-same.fixes-applied.changing');
    }

    public function testSubmissionMatchingMultipleCreators(): void
    {
        // grep-code-legacy-submissions-with-no-creator-reference

        $creator1 = new Creator(user: self::getCreatorUser())->setCreatorId('TEST001')->setName('Some testing creator')->setCity('Kuopio');
        $creator2 = new Creator()->setCreatorId('TEST002')->setName('Testing creator');

        self::persistAndFlush($creator1, $creator2);

        $submissionData = new Creator()->setCreatorId('TEST001')->setFormerCreatorIds(['TEST002'])
            ->setName('Testing creator')->setCity('Oulu');
        $submission = $this->getEntityForSubmission(self::getCreatorUser(), $submissionData, true);
        $submission->setCreator(null); // Simulate legacy submission
        $submission->setOwner(null); // Simulate legacy submission
        self::persistAndFlush($submission);

        self::$client->request('GET', "/submission/{$submission->getId()}/manage");
        self::assertResponseStatusCodeIs(200);

        // grep-code-matched-multiple-creators
        self::assertSelectorTextSame('p', 'Matched multiple creators: Some testing creator (TEST001), Testing creator (TEST002). Unable to continue.');
        self::assertSelectorTextSame('.invalid-feedback', 'Single creator must get selected.');

        $cityValueBeforeSelector = 'tr.CITY.before span';

        // With multiple creators matched, will be displayed as a new creator
        self::assertSelectorTextSame('tr.CITY.submitted span', 'Oulu');
        self::assertSelectorNotExists($cityValueBeforeSelector);

        self::$client->submitForm('Update', [
            'submission[directives]' => 'match-maker-id TEST001',
        ]);

        self::assertResponseStatusCodeIs(200);
        self::assertSelectorNotExists('.invalid-feedback');

        // With a single creator selected, display actual difference
        self::assertSelectorTextSame($cityValueBeforeSelector, 'Kuopio');
        self::assertSelectorTextSame('tr.CITY.submitted span', 'Oulu');
        self::assertSelectorTextSame('tr.CITY.after span', 'Oulu');
    }

    public function testShowingSimilarlyNamedCreators(): void
    {
        $creator1 = UserCreator::get()
            ->setCreatorId('TEST001')->setName('Catbert');
        $creator2 = UserCreator::get()
            ->setCreatorId('TEST002')->setName('Why')->setFormerly(['Dogbert & Catbert']);

        self::persistAndFlush($creator1, $creator2);

        $submissionData = new Creator(user: self::getCreatorUser())
            ->setCreatorId('TEST003')
            ->setName('Catbert')
        ;
        $submission = $this->getEntityForSubmission(self::getCreatorUser(), $submissionData, false);
        self::persistAndFlush($submission);

        self::$client->request('GET', "/submission/{$submission->getId()}/manage");
        self::assertResponseStatusCodeIs(200);

        self::assertAnySelectorTextContains('p', 'Creators named similarly:');
        self::assertAnySelectorTextContains('p > a[href="/#TEST001"]', 'Catbert');
        self::assertAnySelectorTextContains('p > a[href="/#TEST002"]', 'Why / Dogbert & Catbert');
    }

    public function testUpdatingExistingSubmissionWithoutImport(): void
    {
        $submissionData = new Creator()
            ->setCreatorId('TEST001')
            ->setName('Testing creator')
        ;
        $submission = $this->getEntityForSubmission(self::getCreatorUser(), $submissionData, false);
        $submission->setComment('Old comment')->setDirectives('Old directives');
        self::persistAndFlush($submission);

        self::$client->request('GET', "/submission/{$submission->getId()}/manage");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorTextSame('p', 'Adding a new creator.');
        self::assertSelectorTextSame('#manage_comment', 'Old comment');
        self::assertSelectorTextSame('#manage_status option[selected]', 'New');
        self::assertSelectorTextSame('#manage_directives', 'Old directives');

        self::$client->submitForm('Update', [
            'submission[comment]'    => 'New comment',
            'submission[directives]' => 'New directives',
            'submission[status]'     => 'OTHER',
        ]);

        self::assertResponseStatusCodeIs(200);

        // Reload to make sure saved is OK
        self::$client->request('GET', "/mx/submission/{$submission->getId()}");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorTextSame('p', 'Adding a new creator.');
        self::assertSelectorTextSame('#manage_comment', 'New comment');
        self::assertSelectorTextSame('#manage_directives', 'New directives');
        self::assertSelectorTextSame('#manage_status option[selected]', 'Other');

        self::assertEmpty(self::getCreatorRepository()->findAll(), 'A creator should not have been persisted.');
    }

    public function testImportDoesntWorkWithoutAccepting(): void
    {
        $submissionData = new Creator()
            ->setCreatorId('TEST001')
            ->setName('Testing creator')
        ;
        $submission = $this->getEntityForSubmission(self::getCreatorUser(), $submissionData, false);
        self::persistAndFlush($submission);

        self::$client->request('GET', "/submission/{$submission->getId()}/manage");
        self::assertResponseStatusCodeIs(200);

        self::$client->submitForm('Import', []);
        self::assertResponseStatusCodeIs(200);

        self::assertEmpty(self::getCreatorRepository()->findAll(), 'A creator should not have been persisted.');
    }

    public function testDirectivesWork(): void
    {
        $submissionData = new Creator()
            ->setCreatorId('TEST001')
            ->setName('Testing creator')
            ->setIntro('Some submitted intro information')
            ->setSpeciesDoes(['All species', 'Most experience in k9s'])
        ;
        $submission = $this->getEntityForSubmission(self::getCreatorUser(), $submissionData, false);
        $submission->setDirectives("set INTRO 'Some changed intro information'\nset SPECIES_DOES 'Most species'\nset SPECIES_COMMENT 'Most experience in canines'");
        self::persistAndFlush($submission);

        self::$client->request('GET', "/submission/{$submission->getId()}/manage");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorTextSame('tr.INTRO.submitted td+td+td', 'Some submitted intro information');
        self::assertSelectorTextSame('tr.INTRO.after td+td+td', 'Some changed intro information');

        self::assertSelectorTextSame('tr.SPECIES_DOES.submitted td+td+td', 'All species Most experience in k9s');
        self::assertSelectorTextSame('tr.SPECIES_DOES.after td+td+td', 'Most species');

        self::assertSelectorTextSame('tr.SPECIES_COMMENT.submitted td+td+td', '');
        self::assertSelectorTextSame('tr.SPECIES_COMMENT.after td+td+td', 'Most experience in canines');
    }

    public function testDirectivesUpdateIsImmediate(): void
    {
        $submissionData = new Creator()
            ->setCreatorId('TEST001')
            ->setName('Testing creator')
            ->setIntro('Some submitted intro information')
            ->setSpeciesDoes(['All species', 'Most experience in k9s'])
        ;
        $submission = $this->getEntityForSubmission(self::getCreatorUser(), $submissionData, false);
        self::persistAndFlush($submission);

        self::$client->request('GET', "/submission/{$submission->getId()}/manage");
        self::assertResponseStatusCodeIs(200);

        self::$client->submitForm('Import', [
            'submission[directives]' => 'invalid-directive',
        ]);

        self::assertResponseStatusCodeIs(200);
        self::assertSelectorTextSame('.invalid-feedback', "The directives have been ignored completely due to an error. Unknown command: 'invalid-directive'");
    }

    public function testInvalidDirectivesDontBreakPageLoad(): void
    {
        $submissionData = new Creator()
            ->setCreatorId('TEST001')
            ->setName('Testing creator')
        ;
        $submission = $this->getEntityForSubmission(self::getCreatorUser(), $submissionData, false);
        self::persistAndFlush($submission->setDirectives('Let me just put something random here'));

        self::$client->request('GET', "/submission/{$submission->getId()}/manage");
        self::assertResponseStatusCodeIs(200);
        self::assertSelectorTextContains('.invalid-feedback', 'The directives have been ignored completely due to an error.');
    }

    public function testMissingSubmissionReturns404(): void
    {
        self::$client->request('GET', '/submission/1/manage');

        self::assertResponseStatusCodeIs(404);
    }

    public function testUpdatingHiddenCreator(): void
    {
        $existingCreator = new Creator(user: self::getCreatorUser())
            ->setCreatorId('TEST001')
            ->setInactiveReason('Dunno')
        ;
        self::persistAndFlush($existingCreator);

        $submissionData = new Creator()
            ->setCreatorId('TEST001')
            ->setName('Testing creator')
        ;
        $submission = $this->getEntityForSubmission(self::getCreatorUser(), $submissionData, true);
        self::persistAndFlush($submission);

        self::$client->request('GET', "/submission/{$submission->getId()}/manage");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorExists('#creator-hidden-warning');
        self::assertSelectorTextSame('#creator-hidden-warning', 'Hidden');

        $existingCreator->setInactiveReason('');
        self::flush();

        self::$client->request('GET', "/submission/{$submission->getId()}/manage");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorNotExists('#creator-hidden-warning');
    }
}
