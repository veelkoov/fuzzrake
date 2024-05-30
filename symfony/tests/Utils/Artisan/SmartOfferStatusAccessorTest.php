<?php

declare(strict_types=1);

namespace App\Tests\Utils\Artisan;

use App\Entity\Artisan as ArtisanE;
use App\Entity\CreatorOfferStatus;
use App\Tests\TestUtils\Cases\TestCase;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StrUtils;
use Psl\Vec;

/**
 * @small
 */
class SmartOfferStatusAccessorTest extends TestCase
{
    public function testGetAndSet(): void
    {
        $artisan = Artisan::wrap($entity = new ArtisanE());

        $artisan
            ->setOpenFor(['digigrades', 'plantigrades'])
            ->setClosedFor(['partials'])
        ;

        self::assertEquals([
            'False partials',
            'True digigrades',
            'True plantigrades',
        ], $this->getOfferStatusArray($entity));

        self::assertEquals(['digigrades', 'plantigrades'], $artisan->getOpenFor());
        self::assertEquals(['partials'], $artisan->getClosedFor());

        $artisan
            ->setOpenFor(['digigrades']) // Removed item
            ->setClosedFor(['partials', 'plantigrades']) // Added the item from above
        ;

        self::assertEquals([
            'False partials',
            'False plantigrades', // Moved
            'True digigrades',
        ], $this->getOfferStatusArray($entity));

        self::assertEquals(['digigrades'], $artisan->getOpenFor());
        self::assertEquals(['partials', 'plantigrades'], $artisan->getClosedFor());

        $artisan
            ->setOpenFor([]) // Cleared
        ;

        self::assertEquals([ // Open for nothing
            'False partials',
            'False plantigrades',
        ], $this->getOfferStatusArray($entity));

        self::assertEquals([], $artisan->getOpenFor());
    }

    /**
     * @return string[]
     */
    private function getOfferStatusArray(ArtisanE $artisan): array
    {
        $result = Vec\map($artisan->getCommissions(), fn (CreatorOfferStatus $url) => StrUtils::asStr($url->getIsOpen()).' '.$url->getOffer());
        sort($result);

        return $result;
    }
}
