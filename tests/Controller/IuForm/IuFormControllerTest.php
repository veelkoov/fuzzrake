<?php

declare(strict_types=1);

namespace App\Tests\Controller\IuForm;

use App\Data\Definitions\Ages;
use App\Entity\CreatorId;
use App\Entity\CreatorUrl;
use App\Entity\Submission;
use App\Tests\Controller\Traits\FormsChoicesValuesAndLabelsTestTrait;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use App\Tests\TestUtils\UserCreator;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class IuFormControllerTest extends FuzzrakeWebTestCase
{
    use IuFormTrait;
    use FormsChoicesValuesAndLabelsTestTrait;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::haveACreatorUser();
    }

    public function testSubmittingEmptyDoesnt500(): void
    {
        self::loginCreatorUser();
        self::$client->request('GET', '/user/iu_form/start');
        self::skipRules();

        $form = self::$client->getCrawler()->selectButton('Submit')->form();
        self::submitInvalid($form);
    }

    /**
     * @param list<array{value: string, label: string}> $choices
     */
    #[DataProvider('formsChoicesValuesAndLabelsDataProvider')]
    public function testFormsDisplayChoicesProperlyWithValuesAndLabels(array $choices): void
    {
        self::loginCreatorUser();
        self::$client->request('GET', '/user/iu_form/start');
        self::skipRules();
        $crawler = self::$client->getCrawler();

        foreach ($choices as $choice) {
            $label = $choice['label'];
            $value = $choice['value'];

            $inputXPath = "//input[@type = \"checkbox\"][@value = \"$value\"]";
            self::assertCount(1, $crawler->filterXPath($inputXPath), "Absent: $inputXPath");

            $labelXPath = "//label[text() = \"$label\"]";
            self::assertCount(1, $crawler->filterXPath($labelXPath), "Absent: $labelXPath");
        }
    }

    public function testOneCreatorCannotUseOtherCreatorsCreatorId(): void
    {
        $existingCreator = UserCreator::get()->setCreatorId('TEST001');
        $creatorToBeUpdated = new Creator(user: self::getCreatorUser())
            ->setCreatorId('TEST002')
            ->setName('Test creator 002')
            ->setCountry('FI')
            ->setAges(Ages::ADULTS)
            ->setNsfwWebsite(false)
            ->setNsfwSocial(false)
            ->setDoesNsfw(false)
            ->setWorksWithMinors(false)
        ;
        self::persistAndFlush($creatorToBeUpdated, $existingCreator);

        self::loginCreatorUser();
        self::$client->request('GET', '/user/iu_form/start');
        self::skipRules();

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[creatorId]' => 'TEST001',
        ]);
        self::submitInvalid($form);
        self::assertSelectorTextContains('#iu_form_creatorId_help + .invalid-feedback',
            'This maker ID has been already used by another maker.');

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[creatorId]' => 'TEST003',
        ]);
        self::submitValid($form);
    }

    public function testNewCreatorCannotUseOtherCreatorsCreatorId(): void
    {
        self::persistAndFlush(
            UserCreator::get()->setCreatorId('TEST001'),
        );

        self::loginCreatorUser();
        self::$client->request('GET', '/user/iu_form/start');
        self::skipRules();

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[creatorId]'       => 'TEST001',
            'iu_form[name]'            => 'test-maker-555',
            'iu_form[country]'         => 'Finland',
            'iu_form[ages]'            => 'MINORS',
            'iu_form[nsfwWebsite]'     => 'NO',
            'iu_form[nsfwSocial]'      => 'NO',
            'iu_form[worksWithMinors]' => 'NO',
        ]);
        self::submitInvalid($form);
        self::assertSelectorTextContains('#iu_form_creatorId_help + .invalid-feedback',
            'This maker ID has been already used by another maker.');

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[creatorId]' => 'TEST002',
        ]);
        self::submitValid($form);
    }

    public function testSubmittingOnlyAddsSubmissionWithNoOtherChanges(): void
    {
        $existingCreator = new Creator(user: self::getCreatorUser())
            ->setName('Unchanged name')
            ->setCountry('FI')
            ->setCreatorId('TEST001')
            ->setAges(Ages::MIXED)
            ->setNsfwWebsite(false)
            ->setNsfwSocial(false)
            ->setDoesNsfw(false)
            ->setWorksWithMinors(false)
        ;
        self::persistAndFlush($existingCreator);
        self::clear();
        $creatorId = $existingCreator->getId();
        unset($existingCreator);

        self::loginCreatorUser();
        self::$client->request('GET', '/user/iu_form/start');
        self::skipRules();

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[name]' => 'A new name',
            'iu_form[creatorId]' => 'TEST002',
            'iu_form[websiteUrl]' => 'new-website.example.com',
        ]);
        self::submitValid($form);

        // Validate that no changes to existing data has been done

        $creator = self::getCreatorRepository()->find($creatorId);
        self::assertNotNull($creator);
        self::assertSame('Unchanged name', $creator->getName());
        self::assertSame('TEST001', $creator->getCreatorId());

        $creatorIds = self::getEM()->getRepository(CreatorId::class)->findAll();
        self::assertCount(1, $creatorIds);
        self::assertSame('TEST001', $creatorIds[0]->getCreatorId());

        $urls = self::getEM()->getRepository(CreatorUrl::class)->findAll();
        self::assertCount(0, $urls, 'No URL should have been persisted.');

        // Validate that the submission has been created

        $submissions = self::getEM()->getRepository(Submission::class)->findAll();
        self::assertCount(1, $submissions);
        self::assertStringContainsString('A new name', $submissions[0]->getPayload());
        self::assertStringContainsString('new-website.example.com', $submissions[0]->getPayload());
    }
}
