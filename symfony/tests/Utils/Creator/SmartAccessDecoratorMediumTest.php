<?php

declare(strict_types=1);

namespace App\Tests\Utils\Creator;

use App\Entity\CreatorValue;
use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class SmartAccessDecoratorMediumTest extends FuzzrakeKernelTestCase
{
    /**
     * This test assures that boolean field values are properly persisted. This is more like an end-to-end test,
     * because it validates stuff on the ORM entity level.
     */
    public function testBooleanValues(): void
    {
        $repo = self::getEM()->getRepository(CreatorValue::class);

        $creator = new Creator();
        self::persistAndFlush($creator);

        $all = $repo->findAll();
        self::assertCount(0, $all, 'At this point there should be no value entity.');

        $creator->setNsfwWebsite(true);
        self::flush();

        $all = $repo->findAll();
        self::assertCount(1, $all,
            "There should be a single value entity, the one we've just created.");
        $nsfwWebsiteEntity = $all[0];

        self::assertSame('NSFW_WEBSITE', $nsfwWebsiteEntity->getFieldName(),
            'At this point we should have a single NSFW_WEBSITE value entity.');
        self::assertSame('True', $nsfwWebsiteEntity->getValue(),
            'The NSFW_WEBSITE value entity should have "True" value.');

        $creator->setNsfwWebsite(null);
        $creator->setNsfwSocial(false);
        self::flush();

        $all = $repo->findAll();
        self::assertCount(1, $all,
            'There should be still only one entity, the old one removed, but a new one introduced.');
        $nsfwSocialEntity = $all[0];

        self::assertSame('NSFW_SOCIAL', $nsfwSocialEntity->getFieldName(),
            'At this point we should have a single NSFW_SOCIAL value entity.');
        self::assertSame('False', $nsfwSocialEntity->getValue(),
            'NSFW_SOCIAL value entity should have "True" value.');

        self::assertNotSame($nsfwWebsiteEntity->getId(), $nsfwSocialEntity->getId(),
            'Different entities should be used to hold different fields.');

        $creator->setNsfwSocial(null);
        self::flush();

        $all = $repo->findAll();
        self::assertCount(0, $all, 'All value entitles should have been removed.');
    }
}
