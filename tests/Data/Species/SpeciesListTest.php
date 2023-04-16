<?php

declare(strict_types=1);

namespace App\Tests\Data\Species;

use App\Data\Species\Exceptions\MissingSpecieException;
use App\Data\Species\Specie;
use App\Data\Species\SpeciesList;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class SpeciesListTest extends TestCase
{
    public function testGetByName(): void
    {
        $specie = new Specie('A', false, 0);

        $subject = new SpeciesList(['A' => $specie]);

        self::assertEquals($specie, $subject->getByName('A'));

        $exception = null;
        try {
            $subject->getByName('B');
        } catch (Exception $exception) {
        }
        self::assertInstanceOf(MissingSpecieException::class, $exception);
    }
}
