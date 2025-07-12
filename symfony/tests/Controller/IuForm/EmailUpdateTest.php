<?php

declare(strict_types=1);

namespace App\Tests\Controller\IuForm;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use PHPUnit\Framework\Attributes\Medium;
use Veelkoov\Debris\Maps\StringToString;

#[Medium]
class EmailUpdateTest extends FuzzrakeWebTestCase
{
    use IuFormTrait;

    private const string CREATOR_ID = 'TESTMID';

    public function testEmailNotRequiredWhenNoContactAllowed(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::NO, '');
        $this->skipToTheDataIuFormPage();
        $this->succeedsSubmittingAfterSetting(new StringToString());
    }

    public function testGarbageEmailIgnoredWhenNoContactAllowed(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::NO, 'garbage');
        $this->skipToTheDataIuFormPage();
        $this->succeedsSubmittingAfterSetting(new StringToString());
    }

    public function testEmptyEmailRejected(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, '');
        $this->skipToTheDataIuFormPage();
        $this->failsSubmittingAfterSetting(new StringToString());
    }

    public function testGarbageEmailRejected(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, 'garbage');
        $this->skipToTheDataIuFormPage();
        $this->failsSubmittingAfterSetting(new StringToString());
    }

    public function testOldUnchangedValidEmailAccepted(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, 'contact@example.com');
        $this->skipToTheDataIuFormPage();
        $this->succeedsSubmittingAfterSetting(new StringToString());
    }

    public function testOldUnchangedGarbageEmailRejected(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, 'garbage');
        $this->skipToTheDataIuFormPage();
        $this->failsSubmittingAfterSetting(new StringToString([
            'emailAddress' => 'garbage',
        ]));
    }

    public function testNewValidEmailAccepted(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, '');
        $this->skipToTheDataIuFormPage();
        $this->succeedsSubmittingAfterSetting(new StringToString([
            'emailAddress' => 'contact@example.com',
        ]));
    }

    public function testFixedValidEmailAccepted(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, 'garbage');
        $this->skipToTheDataIuFormPage();
        $this->succeedsSubmittingAfterSetting(new StringToString([
            'emailAddress' => 'contact@example.com',
        ]));
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

    private function succeedsSubmittingAfterSetting(StringToString $values): void
    {
        self::submitValidForm('Submit', $this->extendFormValuesWith($values)->toArray());
    }

    private function failsSubmittingAfterSetting(StringToString $values): void
    {
        self::submitInvalidForm('Submit', $this->extendFormValuesWith($values)->toArray());

        self::assertFieldErrorValidEmailAddressRequired();
    }

    private function extendFormValuesWith(StringToString $values): StringToString
    {
        return $values
            ->mapKeysInto(static fn (string $key): string => "iu_form[$key]", new StringToString()) // grep-code-debris-needs-improvements
            ->setAll([
                'iu_form[password]' => 'abcd1234',
                'iu_form[changePassword]' => '1', // Just allow the submission
                'iu_form[verificationAcknowledgement]' => '1', // Whatever the contact permit is
                $this->getCaptchaFieldName('right') => 'right',
            ]);
    }
}
