<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Features;
use App\Data\Definitions\ProductionModels;
use App\Entity\Submission;
use App\IuHandling\SubmissionService;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use JsonException;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use Random\RandomException;
use RuntimeException;
use Symfony\Component\Uid\Uuid;

#[Medium]
class SubmissionsControllerTest extends FuzzrakeWebTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::$client->setServerParameters([
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'testing',
        ]);
    }

    public function testPaginationWorksInSubmissions(): void
    {
        $this->generateRandomFakeSubmissions(24);

        $crawler = self::$client->request('GET', '/mx/submissions/1/');
        self::assertResponseStatusCodeIs(200);
        self::assertCount(24, $crawler->filter('table tbody tr'));
        self::assertCount(3, $crawler->filter('ul.pagination li.page-item'));

        $this->generateRandomFakeSubmissions(2);

        $crawler = self::$client->request('GET', '/mx/submissions/1/');
        self::assertResponseStatusCodeIs(200);
        self::assertCount(25, $crawler->filter('table tbody tr'));
        self::assertCount(4, $crawler->filter('ul.pagination li.page-item'));
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

        $submission = $this->createSubmission($submissionData);
        self::persistAndFlush($submission);

        self::$client->request('GET', "/mx/submission/{$submission->getStrId()}");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorNotExists('tr.MAKER_ID.before');
        self::assertSelectorTextSame('tr.MAKER_ID.submitted td+td', 'TEST001');
        self::assertSelectorTextSame('tr.MAKER_ID.after td+td', 'TEST001');
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

    public function testUpdateIsProperlyRendered(): void
    {
        $creator = new Creator()
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

        $submission = $this->createSubmission($submissionData);
        self::persistAndFlush($submission);

        self::$client->request('GET', "/mx/submission/{$submission->getStrId()}");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorTextSame('tr.MAKER_ID.before td+td', 'TEST001');
        self::assertSelectorTextSame('tr.MAKER_ID.submitted td+td', 'TEST001');
        self::assertSelectorTextSame('tr.MAKER_ID.after td+td', 'TEST001');
        self::assertSelectorExists('tr.MAKER_ID.submitted-same.not-fixed.not-changing');

        self::assertSelectorTextSame('tr.NAME.before td+td', 'Some testing creator');
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

    public function testSubmissionMatchingMultipleCreators(): void
    {
        $creator1 = new Creator()->setCreatorId('TEST001')->setName('Some testing creator')->setCity('Kuopio');
        $creator2 = new Creator()->setCreatorId('TEST002')->setName('Testing creator');

        self::persistAndFlush($creator1, $creator2);

        $submissionData = new Creator()->setCreatorId('TEST001')->setName('Testing creator')->setCity('Oulu');
        $submission = $this->createSubmission($submissionData);
        self::persistAndFlush($submission);

        self::$client->request('GET', "/mx/submission/{$submission->getStrId()}");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorTextSame('p', 'Matched multiple creators: Some testing creator (TEST001), Testing creator (TEST002). Unable to continue.');
        self::assertSelectorTextSame('.invalid-feedback', 'Single creator must get selected.');

        // With multiple creators matched, will be displayed as a new creator
        self::assertSelectorTextContains('p.text-body', 'Added CITY: "Oulu"');

        self::$client->submitForm('Import', [
            'submission[directives]' => 'match-maker-id TEST001',
        ]);

        self::assertResponseStatusCodeIs(200);
        self::assertSelectorNotExists('.invalid-feedback');

        // With a single creator selected, display actual difference
        self::assertSelectorTextContains('p.text-body', 'Changed CITY from "Kuopio" to "Oulu"');
    }

    public function testUpdatingExistingSubmissionWithoutImport(): void
    {
        $submissionData = new Creator()
            ->setCreatorId('TEST001')
            ->setName('Testing creator')
        ;

        $submission = $this->createSubmission($submissionData);
        $submission->setComment('Old comment')->setDirectives('Old directives');
        self::persistAndFlush($submission);

        self::$client->request('GET', "/mx/submission/{$submission->getStrId()}");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorTextSame('p', 'Adding a new creator.');
        self::assertSelectorTextSame('#submission_comment', 'Old comment');
        self::assertSelectorTextSame('#submission_comment', 'Old comment');
        self::assertSelectorTextSame('#submission_directives', 'Old directives');

        self::$client->submitForm('Save', [
            'submission[comment]'    => 'New comment',
            'submission[directives]' => 'New directives',
        ]);

        self::assertResponseStatusCodeIs(200);

        // Reload to make sure saved is OK
        self::$client->request('GET', "/mx/submission/{$submission->getStrId()}");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorTextSame('p', 'Adding a new creator.');
        self::assertSelectorTextSame('#submission_comment', 'New comment');
        self::assertSelectorTextSame('#submission_directives', 'New directives');

        self::assertEmpty(self::getCreatorRepository()->findAll(), 'A creator should not have been persisted.');
    }

    public function testImportDoesntWorkWithoutAccepting(): void
    {
        $submissionData = new Creator()
            ->setCreatorId('TEST001')
            ->setName('Testing creator')
        ;

        $submission = $this->createSubmission($submissionData);
        self::persistAndFlush($submission);

        self::$client->request('GET', "/mx/submission/{$submission->getStrId()}");
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

        $submission = $this->createSubmission($submissionData);
        $submission->setDirectives("set INTRO 'Some changed intro information'\nset SPECIES_DOES 'Most species'\nset SPECIES_COMMENT 'Most experience in canines'");
        self::persistAndFlush($submission);

        self::$client->request('GET', "/mx/submission/{$submission->getStrId()}");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorTextSame('tr.INTRO.submitted td+td', 'Some submitted intro information');
        self::assertSelectorTextSame('tr.INTRO.after td+td', 'Some changed intro information');

        self::assertSelectorTextSame('tr.SPECIES_DOES.submitted td+td', 'All species Most experience in k9s');
        self::assertSelectorTextSame('tr.SPECIES_DOES.after td+td', 'Most species');

        self::assertSelectorTextSame('tr.SPECIES_COMMENT.submitted td+td', '');
        self::assertSelectorTextSame('tr.SPECIES_COMMENT.after td+td', 'Most experience in canines');
    }

    public function testDirectivesUpdateIsImmediate(): void
    {
        $submissionData = new Creator()
            ->setCreatorId('TEST001')
            ->setName('Testing creator')
            ->setIntro('Some submitted intro information')
            ->setSpeciesDoes(['All species', 'Most experience in k9s'])
        ;

        $submission = $this->createSubmission($submissionData);
        self::persistAndFlush($submission);

        self::$client->request('GET', "/mx/submission/{$submission->getStrId()}");
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

        $submission = $this->createSubmission($submissionData);
        self::persistAndFlush($submission->setDirectives('Let me just put something random here'));

        self::$client->request('GET', "/mx/submission/{$submission->getStrId()}");
        self::assertResponseStatusCodeIs(200);
        self::assertSelectorTextContains('.invalid-feedback', 'The directives have been ignored completely due to an error.');
    }

    #[DataProvider('passwordHandlingAndAcceptingWorksDataProvider')]
    public function testPasswordHandlingAndAcceptingWorks(bool $new, bool $passwordSame, bool $accepted): void
    {
        if (!$new) {
            $creator = new Creator()
                ->setCreatorId('TEST001')
                ->setPassword('password')
            ;

            self::persistAndFlush($creator);
        }

        $submissionData = new Creator()
            ->setCreatorId('TEST001')
            ->setPassword($passwordSame ? 'password' : 'PASSPHRASE')
        ;
        $submission = $this->createSubmission($submissionData);

        if ($accepted) {
            $submission->setDirectives('accept');
        }

        self::persistAndFlush($submission);

        self::$client->request('GET', "/mx/submission/{$submission->getStrId()}");
        self::assertResponseStatusCodeIs(200);

        if ($new || $passwordSame || $accepted) {
            self::assertSelectorNotExists('.invalid-feedback');
        } else {
            self::assertSelectorTextSame('.invalid-feedback', 'Password does not match.');
        }
    }

    /**
     * @return array<string, array{0: bool, 1: bool}>
     */
    public static function passwordHandlingAndAcceptingWorksDataProvider(): array
    {
        return [
            'New creator, not accepted'                => [true,  true,  false],
            'New creator, accepted'                    => [true,  true,  true],
            'Updating, wrong password, not accepted'   => [false, false, false],
            'Updating, wrong password, accepted'       => [false, false, true],
            'Updating, correct password, not accepted' => [false, true,  false],
            'Updating, correct password, accepted'     => [false, true,  true],
        ];
    }

    public function testChangesDescriptionShowUp(): void
    {
        self::persistAndFlush(new Creator()->setCreatorId('TEST001')->setName('Old name'));

        $submission = $this->createSubmission(new Creator()->setCreatorId('TEST001')->setName('New name'));
        self::persistAndFlush($submission);

        self::$client->request('GET', "/mx/submission/{$submission->getStrId()}");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorTextContains('p.text-body', 'Changed NAME from "Old name" to "New name"');
    }

    #[DataProvider('contactInfoWorksDataProvider')]
    public function testContactInfoWorks(bool $allowed): void
    {
        $address = 'getfursu.it@example.com';
        $permit = $allowed ? ContactPermit::FEEDBACK : ContactPermit::NO;

        self::persistAndFlush(new Creator()->setCreatorId('TEST001')
            ->setName('Old name')
            ->setEmailAddress($address)
            ->setContactAllowed($permit)
        );
        $submission = $this->createSubmission(new Creator()->setCreatorId('TEST001')
            ->setName('New name')
            ->setEmailAddress($address)
            ->setContactAllowed($permit)
        );
        self::persistAndFlush($submission);

        self::$client->request('GET', "/mx/submission/{$submission->getStrId()}");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorExists('#contact-info-card .card-body.text-'.($allowed ? 'success' : 'danger'));
        self::assertSelectorTextSame('#contact-info-card h5.card-title', $allowed ? 'Allowed: Feedback' : 'Allowed: Never');

        self::assertSelectorCount($allowed ? 1 : 0, '#contact-info-card h5 + p a[href^="mailto:"]');
    }

    /**
     * @return array<string, array{bool}>
     */
    public static function contactInfoWorksDataProvider(): array
    {
        return [
            'Contact allowed'    => [true],
            'Contact disallowed' => [false],
        ];
    }

    public function testMissingSubmissionReturns404(): void
    {
        $this->createSubmission(new Creator()); // Only to have the submissions directory existing
        self::$client->request('GET', '/mx/submission/wrongId');

        self::assertResponseStatusCodeIs(404);
    }

    #[DataProvider('passwordIsRedactedDataProvider')]
    public function testPasswordIsRedacted(bool $isNew, bool $changePassword): void
    {
        if (!$isNew) {
            $creator = new Creator()->setCreatorId('TEST001')->setPassword('password___1234');

            self::persistAndFlush($creator);
        }

        $submittedPassword = $changePassword ? 'password___5678' : 'password___1234';
        $submission = $this->createSubmission(new Creator()->setCreatorId('TEST001')->setPassword($submittedPassword));
        self::persistAndFlush($submission);

        self::$client->request('GET', "/mx/submission/{$submission->getStrId()}");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorTextSame('tr.MAKER_ID td+td', 'TEST001');
        self::assertSelectorTextNotContains('body', 'password___');

        if (!$isNew) {
            self::assertSelectorTextSame('tr.PASSWORD.before td+td', '[redacted]');
        }
        self::assertSelectorTextSame('tr.PASSWORD.submitted td+td', '[redacted]');
        self::assertSelectorTextSame('tr.PASSWORD.after td+td', '[redacted]');
    }

    /**
     * @return array<string, array{bool, bool}>
     */
    public static function passwordIsRedactedDataProvider(): array
    {
        return [
            'New creator'                          => [true, false],
            'Updated creator, no password change'  => [true, false],
            'Updated creator, password is changed' => [true, true],
        ];
    }

    private function generateRandomFakeSubmissions(int $count): void
    {
        while (--$count >= 0) {
            $creator = new Creator();
            $creator->setName(Uuid::v4()->toRfc4122());

            self::persist($this->createSubmission($creator));
        }

        self::flush();
    }

    public function testHiddenCreator(): void
    {
        $entity = new Creator()->setCreatorId('TEST001')->setInactiveReason('Dunno');
        self::persistAndFlush($entity);

        $submission = $this->createSubmission($entity); // No need to modify
        self::persistAndFlush($submission);

        self::$client->request('GET', "/mx/submission/{$submission->getStrId()}");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorExists('#creator-hidden-warning');
        self::assertSelectorTextSame('#creator-hidden-warning', 'Hidden');

        $entity->setInactiveReason('');
        self::flush();

        self::$client->request('GET', "/mx/submission/{$submission->getStrId()}");
        self::assertResponseStatusCodeIs(200);

        self::assertSelectorNotExists('#creator-hidden-warning');
    }

    private function createSubmission(Creator $submissionData): Submission
    {
        try {
            return SubmissionService::getEntityForSubmission($submissionData);
        } catch (RandomException|JsonException $exception) {
            throw new RuntimeException(message: $exception->getMessage(), code: $exception->getCode(), previous: $exception);
        }
    }
}
