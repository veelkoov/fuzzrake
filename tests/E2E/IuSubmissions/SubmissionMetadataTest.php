<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\Data\Definitions\Ages;
use App\Repository\SubmissionRepository;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class SubmissionMetadataTest extends IuSubmissionsTestCase
{
    use IuFormTrait;

    public function testUpdateIsMarkedAsSuch(): void
    {
        self::haveACreatorUser();
        self::loginCreatorUser();
        $user = self::getCreatorUser();

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
        ;
        $creator->entity->setUser($user);
        self::persistAndFlush($creator);

        // Send an update for them
        self::$client->request('GET', '/user/iu_form/start');
        self::assertResponseStatusCodeIs(200);
        self::skipRules();
        $form = self::$client->getCrawler()->selectButton('Submit')->form();
        self::submitValid($form);
        self::assertIuSubmissionQueued();

        // The admin sees an update request
        self::loginAdminUser();
        self::$client->request('GET', '/mx/submissions/1/');
        self::assertResponseStatusCodeIs(200);
        self::assertSelectorCount(1, 'table tbody tr', 'Expected exactly one submission.');
        self::assertSelectorTextSame('table tbody td:nth-child(3)', 'TEST001');
        self::assertSelectorTextSame('table tbody tr td:nth-child(2) a span', 'Update');

        $submissions = self::getContainerService(SubmissionRepository::class)->findAll();
        self::assertCount(1, $submissions);
        self::assertTrue($submissions[0]->getIsUpdate());
        self::assertSameEntity($user, $submissions[0]->getOwner());
        self::assertSameEntity($creator->entity, $submissions[0]->getCreator());
    }

    public function testInclusionIsMarkedAsSuch(): void
    {
        self::haveACreatorUser();
        self::loginCreatorUser();
        $user = self::getCreatorUser();

        // Send an inclusion request for a new creator
        self::$client->request('GET', '/user/iu_form/start');
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
        ]);
        self::submitValid($form);
        self::assertIuSubmissionQueued();

        // The admin sees an update request
        self::loginAdminUser();
        self::$client->request('GET', '/mx/submissions/1/');
        self::assertResponseStatusCodeIs(200);
        self::assertSelectorCount(1, 'table tbody tr', 'Expected exactly one submission.');
        self::assertSelectorTextSame('table tbody td:nth-child(3)', 'TEST002');
        self::assertSelectorTextSame('table tbody tr td:nth-child(2) a span', 'New');

        $submissions = self::getContainerService(SubmissionRepository::class)->findAll();
        self::assertCount(1, $submissions);
        self::assertFalse($submissions[0]->getIsUpdate());
        self::assertSameEntity($user, $submissions[0]->getOwner());
        self::assertNull($submissions[0]->getCreator());
    }
}
