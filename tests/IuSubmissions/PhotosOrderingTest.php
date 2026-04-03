<?php

declare(strict_types=1);

namespace App\Tests\IuSubmissions;

use App\Data\Definitions\Ages;
use App\Tests\TestUtils\Cases\IuSubmissionsTestCase;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class PhotosOrderingTest extends IuSubmissionsTestCase
{
    use IuFormTrait;

    public function testPhotosOrderIsRightAfterImport(): void
    {
        self::haveACreatorUser();

        self::persistAndFlush(
            new Creator(user: self::getCreatorUser())
                ->setName('Test creator')
                ->setCountry('FI')
                ->setCreatorId('TEST001')
                ->setAges(Ages::MIXED)
                ->setNsfwWebsite(false)
                ->setNsfwSocial(false)
                ->setWorksWithMinors(true)
                ->setPhotoUrls(['photo A', 'photo B', 'photo C', 'photo D', 'photo E'])
        );
        self::clear();

        self::loginCreatorUser();
        self::$client->request('GET', '/user/iu_form/start');
        self::skipRules();

        self::assertSelectorTextSame('#iu_form_photoUrls', 'photo A photo B photo C photo D photo E');

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[photoUrls]' => "photo F\nphoto D\nphoto C\nphoto E\nphoto G",
        ]);
        self::submitValid($form);

        self::assertIuSubmissionQueued();

        self::performImports(1);
        self::flushAndClear();

        $creator = self::findCreatorByCreatorId('TEST001');
        self::assertSame(['photo F', 'photo D', 'photo C', 'photo E', 'photo G'], $creator->getPhotoUrls());
    }
}
