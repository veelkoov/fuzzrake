<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Data\Definitions\ContactPermit;
use App\Data\Definitions\Features;
use App\Data\Definitions\ProductionModels;
use App\Entity\Submission;
use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use App\Tests\TestUtils\Submissions;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use JsonException;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Uid\Uuid;

/**
 * @medium
 */
class SubmissionsControllerWithEMTest extends WebTestCaseWithEM
{
    private KernelBrowser $client;

    #[Override]
    protected function setUp(): void
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'testing',
        ]);

        Submissions::emptyTestSubmissionsDir();
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        Submissions::emptyTestSubmissionsDir();
    }

    /**
     * @throws JsonException
     */
    public function testPaginationWorksInSubmissions(): void
    {
        $this->generateRandomFakeSubmissions(24);

        $crawler = $this->client->request('GET', '/mx/submissions/1/');
        self::assertCount(24, $crawler->filter('table tbody tr'));
        self::assertCount(3, $crawler->filter('ul.pagination li.page-item'));

        $this->generateRandomFakeSubmissions(2);

        $crawler = $this->client->request('GET', '/mx/submissions/1/');
        self::assertCount(25, $crawler->filter('table tbody tr'));
        self::assertCount(4, $crawler->filter('ul.pagination li.page-item'));
    }

    /**
     * @throws JsonException
     */
    public function testAdditionIsProperlyRendered(): void
    {
        $submission = (new Artisan())
            // NOT fixed
            ->setMakerId('MAKERID')

            // Fixed
            ->setCountry('Finland')

            // Fixed
            ->setTwitterUrl('http://www.twitter.com/getfursuit')

            // NOT fixed
            ->setFeatures([Features::FOLLOW_ME_EYES])

            // Fixed
            ->setOtherFeatures(['Hidden pockets'])
        ;

        $id = Submissions::submit($submission);

        $this->client->request('GET', "/mx/submission/$id");

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
        $entity = (new Artisan())
            ->setMakerId('MAKERID')
            ->setName('Some testing maker')
            ->setCountry('FI')
            ->setFeatures([Features::FOLLOW_ME_EYES, Features::MOVABLE_JAW])
            ->setOtherFeatures(['Hidden pocket', 'Squeaker in nose'])
            ->setProductionModels([ProductionModels::STANDARD_COMMISSIONS])
            ->setOtherOrderTypes(['Arm sleeves'])
            ->setCurrenciesAccepted(['Euro'])
        ;

        self::persistAndFlush($entity);

        $submission = (new Artisan())
            // Submitted the same, NOT fixed, NOT changed
            ->setMakerId('MAKERID')

            // Submitted different, NOT fixed, changed
            ->setName('Changed name')

            // Submitted different, NOT fixed, changed / not tested
            ->setFormerly(['Some testing maker'])

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

        $id = Submissions::submit($submission);

        $this->client->request('GET', "/mx/submission/$id");

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
        $entity1 = Artisan::new()->setMakerId('MAKERID')->setName('Some testing maker')->setCity('Kuopio');
        $entity2 = Artisan::new()->setMakerId('MAKERI2')->setName('Testing maker');

        self::persistAndFlush($entity1, $entity2);

        $id = Submissions::submit(Artisan::new()->setMakerId('MAKERID')->setName('Testing maker')->setCity('Oulu'));

        $this->client->request('GET', "/mx/submission/$id");

        self::assertSelectorTextSame('p', 'Matched multiple makers: Some testing maker (MAKERID), Testing maker (MAKERI2). Unable to continue.');
        self::assertSelectorTextSame('.invalid-feedback', 'Single maker must get selected.');

        // With multiple makers matched, will be displayed as a new maker
        self::assertSelectorTextContains('p.text-body', 'Added CITY: "Oulu"');

        $this->client->submitForm('Import', [
            'submission[directives]' => 'match-maker-id MAKERID',
        ]);

        self::assertResponseStatusCodeIs($this->client, 200);
        self::assertSelectorNotExists('.invalid-feedback');

        // With a single maker selected, display actual difference
        self::assertSelectorTextContains('p.text-body', 'Changed CITY from "Kuopio" to "Oulu"');
    }

    /**
     * @throws JsonException
     */
    public function testUpdatingExistingSubmissionWithoutImport(): void
    {
        $submissionData = (new Artisan())
            ->setMakerId('MAKERID')
            ->setName('Testing maker')
        ;

        $id = Submissions::submit($submissionData);

        $submission = (new Submission())
            ->setStrId($id)
            ->setComment('Old comment')
            ->setDirectives('Old directives')
        ;

        $this->persistAndFlush($submission);

        $this->client->request('GET', "/mx/submission/$id");

        self::assertSelectorTextSame('p', 'Creating a new maker.');
        self::assertSelectorTextSame('#submission_comment', 'Old comment');
        self::assertSelectorTextSame('#submission_comment', 'Old comment');
        self::assertSelectorTextSame('#submission_directives', 'Old directives');

        $this->client->submitForm('Save', [
            'submission[comment]'    => 'New comment',
            'submission[directives]' => 'New directives',
        ]);

        self::assertResponseStatusCodeIs($this->client, 200);

        // Reload to make sure saved is OK
        $this->client->request('GET', "/mx/submission/$id");

        self::assertSelectorTextSame('p', 'Creating a new maker.');
        self::assertSelectorTextSame('#submission_comment', 'New comment');
        self::assertSelectorTextSame('#submission_directives', 'New directives');
    }

    /**
     * @throws JsonException
     */
    public function testCreatingSubmission(): void
    {
        $submissionData = (new Artisan())
            ->setMakerId('MAKERID')
            ->setName('Testing maker')
        ;

        $id = Submissions::submit($submissionData);

        $this->client->request('GET', "/mx/submission/$id");

        self::assertSelectorTextSame('#submission_comment', '');
        self::assertSelectorTextSame('#submission_directives', '');

        $this->client->submitForm('Import', [
            'submission[comment]'    => 'Added comment',
            'submission[directives]' => 'Added directives',
        ]);

        self::assertResponseStatusCodeIs($this->client, 200);

        // Reload to make sure saved is OK
        $this->client->request('GET', "/mx/submission/$id");

        self::assertSelectorTextSame('#submission_comment', 'Added comment');
        self::assertSelectorTextSame('#submission_directives', 'Added directives');
    }

    /**
     * @throws JsonException
     */
    public function testDirectivesWork(): void
    {
        $submissionData = (new Artisan())
            ->setMakerId('MAKERID')
            ->setName('Testing maker')
            ->setIntro('Some submitted intro information')
            ->setSpeciesDoes(['All species', 'Most experience in k9s'])
        ;

        $id = Submissions::submit($submissionData);

        $submission = (new Submission())
            ->setStrId($id)
            ->setDirectives("set INTRO 'Some changed intro information'\nset SPECIES_DOES 'Most species'\nset SPECIES_COMMENT 'Most experience in canines'")
        ;

        $this->persistAndFlush($submission);

        $this->client->request('GET', "/mx/submission/$id");

        self::assertSelectorTextSame('tr.INTRO.submitted td+td', 'Some submitted intro information');
        self::assertSelectorTextSame('tr.INTRO.after td+td', 'Some changed intro information');

        self::assertSelectorTextSame('tr.SPECIES_DOES.submitted td+td', 'All species Most experience in k9s');
        self::assertSelectorTextSame('tr.SPECIES_DOES.after td+td', 'Most species');

        self::assertSelectorTextSame('tr.SPECIES_COMMENT.submitted td+td', '');
        self::assertSelectorTextSame('tr.SPECIES_COMMENT.after td+td', 'Most experience in canines');
    }

    /**
     * @throws JsonException
     */
    public function testDirectivesUpdateIsImmediate(): void
    {
        $submissionData = (new Artisan())
            ->setMakerId('MAKERID')
            ->setName('Testing maker')
            ->setIntro('Some submitted intro information')
            ->setSpeciesDoes(['All species', 'Most experience in k9s'])
        ;

        $id = Submissions::submit($submissionData);

        $submission = (new Submission())
            ->setStrId($id)
        ;

        $this->persistAndFlush($submission);

        $this->client->request('GET', "/mx/submission/$id");

        $this->client->submitForm('Import', [
            'submission[directives]' => 'invalid-directive',
        ]);

        self::assertResponseStatusCodeIs($this->client, 200);
        self::assertSelectorTextSame('.invalid-feedback', "The directives have been ignored completely due to an error. Unknown command: 'invalid-directive'");
    }

    /**
     * @throws JsonException
     */
    public function testInvalidDirectivesDontBreakPage(): void
    {
        $submissionData = (new Artisan())
            ->setMakerId('MAKERID')
            ->setName('Testing maker')
        ;

        $id = Submissions::submit($submissionData);

        $submission = (new Submission())
            ->setStrId($id)
            ->setDirectives('Let me just put something random here')
        ;

        $this->persistAndFlush($submission);

        $this->client->request('GET', "/mx/submission/$id");
        self::assertResponseStatusCodeIs($this->client, 200);
        self::assertSelectorTextContains('.invalid-feedback', 'The directives have been ignored completely due to an error.');
    }

    /**
     * @dataProvider passwordHandlingAndAcceptingWorksDataProvider
     *
     * @throws JsonException
     */
    public function testPasswordHandlingAndAcceptingWorks(bool $new, bool $passwordSame, bool $accepted): void
    {
        if (!$new) {
            $entity = (new Artisan())
                ->setMakerId('MAKERID')
                ->setPassword('password')
            ;

            self::persistAndFlush($entity);
        }

        $submissionData = (new Artisan())
            ->setMakerId('MAKERID')
            ->setPassword($passwordSame ? 'password' : 'PASSPHRASE')
        ;

        $id = Submissions::submit($submissionData);

        if ($accepted) {
            self::persistAndFlush((new Submission())->setStrId($id)->setDirectives('accept'));
        }

        $this->client->request('GET', "/mx/submission/$id");

        if ($new || $passwordSame || $accepted) {
            self::assertSelectorNotExists('.invalid-feedback');
        } else {
            self::assertSelectorTextSame('.invalid-feedback', 'Password does not match.');
        }
    }

    /**
     * @return array<string, array{0: bool, 1: bool}>
     */
    public function passwordHandlingAndAcceptingWorksDataProvider(): array
    {
        return [
            'New artisan, not accepted'                => [true,  true,  false],
            'New artisan, accepted'                    => [true,  true,  true],
            'Updating, wrong password, not accepted'   => [false, false, false],
            'Updating, wrong password, accepted'       => [false, false, true],
            'Updating, correct password, not accepted' => [false, true,  false],
            'Updating, correct password, accepted'     => [false, true,  true],
        ];
    }

    /**
     * @throws JsonException
     */
    public function testChangesDescriptionShowUp(): void
    {
        self::persistAndFlush(Artisan::new()->setMakerId('MAKERID')->setName('Old name'));
        $id = Submissions::submit(Artisan::new()->setMakerId('MAKERID')->setName('New name'));

        $this->client->request('GET', "/mx/submission/$id");

        self::assertSelectorTextContains('p.text-body', 'Changed NAME from "Old name" to "New name"');
    }

    /**
     * @throws JsonException
     *
     * @dataProvider contactInfoWorksDataProvider
     */
    public function testContactInfoWorks(bool $allowed): void
    {
        $address = 'getfursu.it@example.com';
        $permit = $allowed ? ContactPermit::FEEDBACK : ContactPermit::NO;

        self::persistAndFlush(Artisan::new()->setMakerId('MAKERID')
            ->setName('Old name')
            ->setEmailAddress($address)
            ->setContactAllowed($permit)
        );
        $id = Submissions::submit(Artisan::new()->setMakerId('MAKERID')
            ->setName('New name')
            ->setEmailAddress($address)
            ->setContactAllowed($permit)
        );

        $this->client->request('GET', "/mx/submission/$id");

        self::assertSelectorExists('#contact-info-card .card-body.text-'.($allowed ? 'success' : 'danger'));
        self::assertSelectorTextSame('#contact-info-card h5.card-title', $allowed ? 'Allowed: Feedback' : 'Allowed: Never');

        self::assertSelectorCount($allowed ? 1 : 0, '#contact-info-card h5 + p a[href^="mailto:"]');
    }

    /**
     * @return array<string, array{bool}>
     */
    public function contactInfoWorksDataProvider(): array
    {
        return [
            'Contact allowed'    => [true],
            'Contact disallowed' => [false],
        ];
    }

    /**
     * @throws JsonException
     */
    public function testInvalidIdDoesntCauseError500(): void
    {
        Submissions::submit(Artisan::new()); // Only to have the submissions directory existing
        $this->client->request('GET', '/mx/submission/wrongId');

        self::assertResponseStatusCodeIs($this->client, 404);
    }

    /**
     * @dataProvider passwordIsRedactedDataProvider
     *
     * @throws JsonException
     */
    public function testPasswordIsRedacted(bool $isNew, bool $changePassword): void
    {
        if (!$isNew) {
            $entity = Artisan::new()->setMakerId('MAKERID')->setPassword('password___1234');

            self::persistAndFlush($entity);
        }

        $submittedPassword = $changePassword ? 'password___5678' : 'password___1234';
        $id = Submissions::submit(Artisan::new()->setMakerId('MAKERID')->setPassword($submittedPassword));

        $this->client->request('GET', "/mx/submission/$id");

        self::assertSelectorTextSame('tr.MAKER_ID td+td', 'MAKERID');
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
    public function passwordIsRedactedDataProvider(): array
    {
        return [
            'New maker'                          => [true, false],
            'Updated maker, no password change'  => [true, false],
            'Updated maker, password is changed' => [true, true],
        ];
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

    /**
     * @throws JsonException
     */
    public function testHiddenMarker(): void
    {
        $entity = Artisan::new()->setMakerId('MAKERID')->setInactiveReason('Dunno');
        self::persistAndFlush($entity);

        $id = Submissions::submit($entity); // No need to modify

        $this->client->request('GET', "/mx/submission/$id");

        self::assertSelectorExists('#creator-hidden-warning');
        self::assertSelectorTextSame('#creator-hidden-warning', 'Hidden');

        $entity->setInactiveReason('');
        self::flush();

        $this->client->request('GET', '/mx/submission/MAKERID');

        self::assertSelectorNotExists('#creator-hidden-warning');
    }
}
