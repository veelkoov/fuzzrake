<?php

declare(strict_types=1);

namespace App\Tests\Utils\Creator;

use App\Entity\Creator as CreatorE;
use App\Entity\CreatorOfferStatus;
use App\Tests\TestUtils\Cases\TestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\StrUtils;
use Psl\Vec;

/**
 * @small
 */
class SmartOfferStatusAccessorTest extends TestCase
{
    public function testGetAndSet(): void
    {
        $creator = Creator::wrap($entity = new CreatorE());

        $creator
            ->setOpenFor(['digigrades', 'plantigrades'])
            ->setClosedFor(['partials'])
        ;

        self::assertEquals([
            'False partials',
            'True digigrades',
            'True plantigrades',
        ], $this->getOfferStatusArray($entity));

        self::assertEquals(['digigrades', 'plantigrades'], $creator->getOpenFor());
        self::assertEquals(['partials'], $creator->getClosedFor());

        $creator
            ->setOpenFor(['digigrades']) // Removed item
            ->setClosedFor(['partials', 'plantigrades']) // Added the item from above
        ;

        self::assertEquals([
            'False partials',
            'False plantigrades', // Moved
            'True digigrades',
        ], $this->getOfferStatusArray($entity));

        self::assertEquals(['digigrades'], $creator->getOpenFor());
        self::assertEquals(['partials', 'plantigrades'], $creator->getClosedFor());

        $creator
            ->setOpenFor([]) // Cleared
        ;

        self::assertEquals([ // Open for nothing
            'False partials',
            'False plantigrades',
        ], $this->getOfferStatusArray($entity));

        self::assertEquals([], $creator->getOpenFor());
    }

    /**
     * @return string[]
     */
    private function getOfferStatusArray(CreatorE $creator): array
    {
        $result = Vec\map($creator->getOfferStatuses(), fn (CreatorOfferStatus $url) => StrUtils::asStr($url->getIsOpen()).' '.$url->getOffer());
        sort($result);

        return $result;
    }
}
