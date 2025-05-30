<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\Data\Definitions\Ages;
use App\Data\Definitions\ContactPermit;
use App\Tests\TestUtils\Cases\Traits\IuFormTrait;

/**
 * @medium
 */
class PhotosOrderingTest extends IuSubmissionsAbstractTest
{
    use IuFormTrait;

    public function testPhotosOrderIsRightAfterImport(): void
    {
        $creator = self::getCreator(
            name: 'Test creator',
            creatorId: 'TEST001',
            password: 'the-password',
            contactAllowed: ContactPermit::NO,
            ages: Ages::MIXED,
            nsfwWebsite: false,
            nsfwSocial: false,
            worksWithMinors: true,
        )
            ->setPhotoUrls(['photo A', 'photo B', 'photo C', 'photo D', 'photo E']);
        self::persistAndFlush($creator);
        self::clear();

        self::$client->request('GET', '/iu_form/start/TEST001');
        self::skipRules();

        self::assertSelectorTextSame('#iu_form_photoUrls', 'photo A photo B photo C photo D photo E');

        $form = self::$client->getCrawler()->selectButton('Submit')->form([
            'iu_form[photoUrls]' => "photo F\nphoto D\nphoto C\nphoto E\nphoto G",
            'iu_form[password]' => 'the-password',
            $this->getCaptchaFieldName('right') => 'right',
        ]);
        self::submitValid($form);

        self::assertIuSubmittedCorrectPassword();

        self::performImport(true, 1);
        self::flushAndClear();

        $creator = self::findCreatorByCreatorId('TEST001');
        self::assertSame(['photo F', 'photo D', 'photo C', 'photo E', 'photo G'], $creator->getPhotoUrls());
    }
}
