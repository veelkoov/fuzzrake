<?php

declare(strict_types=1);

namespace App\Tests\Controller\IuForm;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use Psl\Dict;

/**
 * @medium
 */
class EmailUpdateTest extends FuzzrakeWebTestCase
{
    use IuFormTrait;

    private const string CREATOR_ID = 'TESTMID';

    public function testEmailNotRequiredWhenNoContactAllowed(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::NO, '');
        $this->skipToTheDataIuFormPage();
        $this->succeedsSubmittingAfterSetting([]);
    }

    public function testGarbageEmailIgnoredWhenNoContactAllowed(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::NO, 'garbage');
        $this->skipToTheDataIuFormPage();
        $this->succeedsSubmittingAfterSetting([]);
    }

    public function testEmptyEmailRejected(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, '');
        $this->skipToTheDataIuFormPage();
        $this->failsSubmittingAfterSetting([]);
    }

    public function testGarbageEmailRejected(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, 'garbage');
        $this->skipToTheDataIuFormPage();
        $this->failsSubmittingAfterSetting([]);
    }

    public function testOldUnchangedValidEmailAccepted(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, 'contact@example.com');
        $this->skipToTheDataIuFormPage();
        $this->succeedsSubmittingAfterSetting([]);
    }

    public function testOldUnchangedGarbageEmailRejected(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, 'garbage');
        $this->skipToTheDataIuFormPage();
        $this->failsSubmittingAfterSetting([
            'emailAddress' => 'garbage',
        ]);
    }

    public function testNewValidEmailAccepted(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, '');
        $this->skipToTheDataIuFormPage();
        $this->succeedsSubmittingAfterSetting([
            'emailAddress' => 'contact@example.com',
        ]);
    }

    public function testFixedValidEmailAccepted(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, 'garbage');
        $this->skipToTheDataIuFormPage();
        $this->succeedsSubmittingAfterSetting([
            'emailAddress' => 'contact@example.com',
        ]);
    }

    private function havingACreatorWithContactAndEmailSetAs(ContactPermit $contactPermit, string $email): void
    {
        self::persistAndFlush(
            self::getCreator(creatorId: self::CREATOR_ID, contactAllowed: $contactPermit, ages: Ages::ADULTS,
                nsfwWebsite: false, nsfwSocial: false, doesNsfw: false, worksWithMinors: false)
                ->setEmailAddress($email)
        );
    }

    private function skipToTheDataIuFormPage(): void
    {
        self::$client->request('GET', '/iu_form/start/'.self::CREATOR_ID);
        self::skipRules();
    }

    /**
     * @param array<string, string> $values
     */
    private function succeedsSubmittingAfterSetting(array $values): void
    {
        self::submitValidForm('Submit', $this->extendFormValuesWith($values));
    }

    /**
     * @param array<string, string> $values
     */
    private function failsSubmittingAfterSetting(array $values): void
    {
        self::submitInvalidForm('Submit', $this->extendFormValuesWith($values));

        self::assertFieldErrorValidEmailAddressRequired();
    }

    /**
     * @param array<string, string> $values
     *
     * @return array<string, string>
     */
    private function extendFormValuesWith(array $values): array
    {
        return Dict\merge(
            Dict\map_keys($values, fn (string $key): string => "iu_form[$key]"),
            [
                'iu_form[password]' => 'abcd1234',
                'iu_form[changePassword]' => '1', // Just allow the submission
                'iu_form[verificationAcknowledgement]' => '1', // Whatever the contact permit is
                $this->getCaptchaFieldName('right') => 'right',
            ],
        );
    }
}
