<?php

declare(strict_types=1);

namespace App\Tests\Controller\IuForm;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use Override;
use Psl\Dict;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class EmailUpdateWithEMTest extends WebTestCaseWithEM
{
    use IuFormTrait;

    private const string CREATOR_ID = 'TESTMID';
    private KernelBrowser $client;

    #[Override]
    protected function setUp(): void
    {
        $this->client = self::createClient();
    }

    public function testEmailNotRequiredWhenNoContactAllowed(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::NO, '');
        $this->skipToTheLastIuFormPage();
        $this->succeedsSubmittingAfterSetting([]);
    }

    public function testGarbageEmailIgnoredWhenNoContactAllowed(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::NO, 'garbage');
        $this->skipToTheLastIuFormPage();
        $this->succeedsSubmittingAfterSetting([]);
    }

    public function testEmptyEmailRejected(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, '');
        $this->skipToTheLastIuFormPage();
        $this->failsSubmittingAfterSetting([]);
    }

    public function testGarbageEmailRejected(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, 'garbage');
        $this->skipToTheLastIuFormPage();
        $this->failsSubmittingAfterSetting([]);
    }

    public function testOldUnchangedValidEmailAccepted(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, 'contact@example.com');
        $this->skipToTheLastIuFormPage();
        $this->succeedsSubmittingAfterSetting([]);
    }

    public function testNewValidEmailAccepted(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, '');
        $this->skipToTheLastIuFormPage();
        $this->succeedsSubmittingAfterSetting([
            'emailAddressObfuscated' => 'contact@example.com',
        ]);
    }

    public function testFixedValidEmailAccepted(): void
    {
        $this->havingACreatorWithContactAndEmailSetAs(ContactPermit::CORRECTIONS, 'garbage');
        $this->skipToTheLastIuFormPage();
        $this->succeedsSubmittingAfterSetting([
            'emailAddressObfuscated' => 'contact@example.com',
        ]);
    }

    private function havingACreatorWithContactAndEmailSetAs(ContactPermit $contactPermit, string $email): void
    {
        self::persistAndFlush(
            self::getArtisan(makerId: self::CREATOR_ID, contactAllowed: $contactPermit, ages: Ages::ADULTS,
                nsfwWebsite: false, nsfwSocial: false, doesNsfw: false, worksWithMinors: false)
                ->updateEmailAddress($email)
        );
    }

    private function skipToTheLastIuFormPage(): void
    {
        $this->client->request('GET', '/iu_form/start/'.self::CREATOR_ID);
        self::skipRulesAndCaptcha($this->client);
        self::skipData($this->client, false);
    }

    /**
     * @param array<string, string> $values
     */
    private function succeedsSubmittingAfterSetting(array $values): void
    {
        self::submitValidForm($this->client, 'Submit', $this->extendFormValuesWith($values));
    }

    /**
     * @param array<string, string> $values
     */
    private function failsSubmittingAfterSetting(array $values): void
    {
        self::submitInvalidForm($this->client, 'Submit', $this->extendFormValuesWith($values));

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
            ],
        );
    }
}
