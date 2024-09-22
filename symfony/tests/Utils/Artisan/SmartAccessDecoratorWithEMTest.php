<?php

declare(strict_types=1);

namespace App\Tests\Utils\Artisan;

use App\Entity\ArtisanValue;
use App\Tests\TestUtils\Cases\KernelTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

/**
 * @medium
 */
class SmartAccessDecoratorWithEMTest extends KernelTestCaseWithEM
{
    /**
     * This test assures that boolean field values are properly persisted. This is more like an end-to-end test,
     * because it validates stuff on the ORM entity level.
     */
    public function testBooleanValues(): void
    {
        self::bootKernel();
        $repo = self::getEM()->getRepository(ArtisanValue::class);

        $artisan = new Artisan();
        self::persistAndFlush($artisan);

        $all = $repo->findAll();
        self::assertCount(0, $all, 'At this point there should be no value entity.');

        $artisan->setNsfwWebsite(true);
        self::flush();

        $all = $repo->findAll();
        self::assertCount(1, $all,
            "There should be a single value entity, the one we've just created.");
        $nsfwWebsiteEntity = $all[0];

        self::assertEquals('NSFW_WEBSITE', $nsfwWebsiteEntity->getFieldName(),
            'At this point we should have a single NSFW_WEBSITE value entity.');
        self::assertEquals('True', $nsfwWebsiteEntity->getValue(),
            'The NSFW_WEBSITE value entity should have "True" value.');

        $artisan->setNsfwWebsite(null);
        $artisan->setNsfwSocial(false);
        self::flush();

        $all = $repo->findAll();
        self::assertCount(1, $all,
            'There should be still only one entity, the old one removed, but a new one introduced.');
        $nsfwSocialEntity = $all[0];

        self::assertEquals('NSFW_SOCIAL', $nsfwSocialEntity->getFieldName(),
            'At this point we should have a single NSFW_SOCIAL value entity.');
        self::assertEquals('False', $nsfwSocialEntity->getValue(),
            'NSFW_SOCIAL value entity should have "True" value.');

        self::assertNotEquals($nsfwWebsiteEntity->getId(), $nsfwSocialEntity->getId(),
            'Different entities should be used to hold different fields.');

        $artisan->setNsfwSocial(null);
        self::flush();

        $all = $repo->findAll();
        self::assertCount(0, $all, 'All value entitles should have been removed.');
    }
}
