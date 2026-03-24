<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Security\Password;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class SubmissionMetadataTest extends IuSubmissionsTestCase
{
    use IuFormTrait;

    public function testUpdateIsMarkedAsSuch(): void
    {
        // Having an existing creator
        $creator = new Creator()
            ->setCreatorId('TEST001')
            ->setName('Testing creator 1')
            ->setCountry('FI')
            ->setAges(Ages::ADULTS)
            ->setNsfwWebsite(false)
            ->setNsfwSocial(false)
            ->setDoesNsfw(false)
            ->setWorksWithMinors(false)
            ->setContactAllowed(ContactPermit::NO)
            ->setPassword('password-555');
        Password::encryptOn($creator);
        self::persistAndFlush($creator);

        // Send an update for them
        self::$client->request('GET', self::getIuFormUrlForCreatorId('TEST001'));
        self::assertResponseStatusCodeIs(200);
        self::skipRules();
        $form = self::$client->getCrawler()->selectButton('Submit')->form();
        $form->setValues([
            $this->getCaptchaFieldName('right') => 'right',
            'iu_form[password]' => 'password-555',
        ]);
        self::submitValid($form);
        self::assertIuSubmittedAnyResult();

        // The admin sees an update request
        self::loginAdminUser();
        self::$client->request('GET', '/mx/submissions/1/');
        self::assertResponseStatusCodeIs(200);
        self::assertSelectorCount(1, 'table tbody tr', 'Expected exactly one submission.');
        self::assertSelectorTextSame('table tbody td:nth-child(3)', 'TEST001');
        self::assertSelectorTextSame('table tbody tr td:nth-child(2) a span', 'Update');
    }

    public function testInclusionIsMarkedAsSuch(): void
    {
        // Send an inclusion request for a new creator
        self::$client->request('GET', self::getIuFormUrlForCreatorId(''));
        self::assertResponseStatusCodeIs(200);
        self::skipRules();
        $form = self::$client->getCrawler()->selectButton('Submit')->form();
        $form->setValues([
            'iu_form[creatorId]' => 'TEST002',
            'iu_form[name]' => 'Testing creator 2',
            'iu_form[country]' => 'FI',
            'iu_form[ages]' => 'ADULTS',
            'iu_form[nsfwWebsite]' => 'NO',
            'iu_form[nsfwSocial]' => 'NO',
            'iu_form[doesNsfw]' => 'NO',
            'iu_form[worksWithMinors]' => 'NO',
            'iu_form[contactAllowed]' => 'NO',
            $this->getCaptchaFieldName('right') => 'right',
            'iu_form[password]' => 'password-555',
        ]);
        self::submitValid($form);
        self::assertIuSubmittedAnyResult();

        // The admin sees an update request
        self::loginAdminUser();
        self::$client->request('GET', '/mx/submissions/1/');
        self::assertResponseStatusCodeIs(200);
        self::assertSelectorCount(1, 'table tbody tr', 'Expected exactly one submission.');
        self::assertSelectorTextSame('table tbody td:nth-child(3)', 'TEST002');
        self::assertSelectorTextSame('table tbody tr td:nth-child(2) a span', 'New');
    }
}
