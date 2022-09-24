<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\DataDefinitions\ContactPermit;
use App\DataDefinitions\Features;
use App\DataDefinitions\ProductionModels;
use App\Entity\Submission;
use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use App\Tests\TestUtils\Submissions;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Contact;
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
    public function testUpdatingExistingSubmission(): void
    {
        $client = self::createClient();

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

        $client->request('GET', "/mx/submissions/$id");

        self::assertSelectorTextSame('#submission_comment', 'Old comment');
        self::assertSelectorTextSame('#submission_directives', 'Old directives');

        $client->submitForm('Update', [
            'submission[comment]'    => 'New comment',
            'submission[directives]' => 'New directives',
        ]);

        self::assertResponseStatusCodeSame(200);

        // Reload to make sure saved is OK
        $client->request('GET', "/mx/submissions/$id");

        self::assertSelectorTextSame('#submission_comment', 'New comment');
        self::assertSelectorTextSame('#submission_directives', 'New directives');
    }

    /**
     * @throws JsonException
     */
    public function testCreatingSubmission(): void
    {
        $client = self::createClient();

        $submissionData = (new Artisan())
            ->setMakerId('MAKERID')
            ->setName('Testing maker')
        ;

        $id = Submissions::submit($submissionData);

        $client->request('GET', "/mx/submissions/$id");

        self::assertSelectorTextSame('#submission_comment', '');
        self::assertSelectorTextSame('#submission_directives', '');

        $client->submitForm('Update', [
            'submission[comment]'    => 'Added comment',
            'submission[directives]' => 'Added directives',
        ]);

        self::assertResponseStatusCodeSame(200);

        // Reload to make sure saved is OK
        $client->request('GET', "/mx/submissions/$id");

        self::assertSelectorTextSame('#submission_comment', 'Added comment');
        self::assertSelectorTextSame('#submission_directives', 'Added directives');
    }

    /**
     * @throws JsonException
     */
    public function testDirectivesWork(): void
    {
        $client = self::createClient();

        $submissionData = (new Artisan())
            ->setMakerId('MAKERID')
            ->setName('Testing maker')
            ->setIntro('Some submitted intro information')
            ->setSpeciesDoes("All species\nMost experience in k9s")
        ;

        $id = Submissions::submit($submissionData);

        $submission = (new Submission())
            ->setStrId($id)
            ->setDirectives("set INTRO 'Some changed intro information'\nset SPECIES_DOES 'Most species'\nset SPECIES_COMMENT 'Most experience in canines'")
        ;

        $this->persistAndFlush($submission);

        $client->request('GET', "/mx/submissions/$id");

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
        $client = self::createClient();

        $submissionData = (new Artisan())
            ->setMakerId('MAKERID')
            ->setName('Testing maker')
            ->setIntro('Some submitted intro information')
            ->setSpeciesDoes("All species\nMost experience in k9s")
        ;

        $id = Submissions::submit($submissionData);

        $submission = (new Submission())
            ->setStrId($id)
        ;

        $this->persistAndFlush($submission);

        $client->request('GET', "/mx/submissions/$id");

        $client->submitForm('Update', [
            'submission[directives]' => 'invalid-directive',
        ]);

        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextSame('.invalid-feedback', "The directives have been ignored completely due to an error. Unknown command: 'invalid-directive'");
    }

    /**
     * @throws JsonException
     */
    public function testInvalidDirectivesDontBreakPage(): void
    {
        $client = self::createClient();

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

        $client->request('GET', "/mx/submissions/$id");
        self::assertResponseStatusCodeSame(200);
        self::assertSelectorTextContains('.invalid-feedback', 'The directives have been ignored completely due to an error.');
    }

    /**
     * @dataProvider passwordHandlingAndAcceptingWorksDataProvider
     *
     * @throws JsonException
     */
    public function testPasswordHandlingAndAcceptingWorks(bool $new, bool $passwordSame, bool $accepted): void
    {
        $client = self::createClient();

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

        $client->request('GET', "/mx/submissions/$id");

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
        $client = self::createClient();

        self::persistAndFlush(Artisan::new()->setMakerId('MAKERID')->setName('Old name'));
        $id = Submissions::submit(Artisan::new()->setMakerId('MAKERID')->setName('New name'));

        $client->request('GET', "/mx/submissions/$id");

        self::assertSelectorTextContains('p.text-body', 'Changed NAME from "Old name" to "New name"');
    }

    /**
     * @throws JsonException
     *
     * @dataProvider contactInfoWorksDataProvider
     */
    public function testContactInfoWorks(bool $email, bool $allowed): void
    {
        $client = self::createClient();

        $method = $email ? Contact::E_MAIL : Contact::TELEGRAM;
        $address = $email ? 'getfursu.it@example.com' : '@telegram';
        $permit = $allowed ? ContactPermit::FEEDBACK : ContactPermit::NO;

        self::persistAndFlush(Artisan::new()->setMakerId('MAKERID')
            ->setName('Old name')
            ->setContactMethod($method)
            ->setContactAddressPlain($address)
            ->setContactAllowed($permit)
        );
        $id = Submissions::submit(Artisan::new()->setMakerId('MAKERID')
            ->setName('New name')
            ->setContactMethod($method)
            ->setContactAddressPlain($address)
            ->setContactAllowed($permit)
        );

        $client->request('GET', "/mx/submissions/$id");

        self::assertSelectorExists('#contact-info-card .card-body.text-'.($allowed ? 'success' : 'danger'));
        self::assertSelectorTextSame('#contact-info-card h5.card-title', $allowed ? 'Allowed: Feedback' : 'Allowed: Never');

        self::assertSelectorTextContains('#contact-info-card h5 + p', "{$method}: {$address}");

        if ($email) {
            self::assertSelectorExists('#contact-info-card h5 + p a[href^="mailto:"]');
        } else {
            self::assertSelectorNotExists('#contact-info-card h5 + p a[href^="mailto:"]');
        }
    }

    /**
     * @return array<string, array{bool, bool}>
     */
    public function contactInfoWorksDataProvider(): array
    {
        return [
            'E-mail, contact allowed'      => [true,  true],
            'E-mail, contact disallowed'   => [true,  false],
            'Telegram, contact allowed'    => [false, true],
            'Telegram, contact disallowed' => [false, false],
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
}
