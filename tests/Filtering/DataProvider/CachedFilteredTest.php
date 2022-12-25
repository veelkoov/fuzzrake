<?php

declare(strict_types=1);

namespace App\Tests\Filtering\DataProvider;

use App\Filtering\Choices;
use App\Filtering\DataProvider\CachedFiltered;
use App\Filtering\DataProvider\Filtered;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * @small
 */
class CachedFilteredTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testGetPublicDataFor(): void
    {
        $choices1 = new Choices(['FI'], [], [], [], [], [], [], [], [], false, false, false, false, false);
        $result1 = [['FI']];
        $choices2 = new Choices(['CZ'], [], [], [], [], [], [], [], [], false, false, false, false, false);
        $result2 = [['CZ']];

        $providerMock = $this->createMock(Filtered::class);
        $providerMock->expects($this->exactly(2))->method('getPublicDataFor')->will($this->returnValueMap([
            [$choices1, $result1],
            [$choices2, $result2],
        ]));

        $subject = new CachedFiltered($providerMock, new ArrayAdapter());

        $this->assertEquals($result1, $subject->getPublicDataFor($choices1));
        $this->assertEquals($result2, $subject->getPublicDataFor($choices2));
        $this->assertEquals($result1, $subject->getPublicDataFor($choices1));
    }
}
