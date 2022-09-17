<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Entity\Artisan as ArtisanE;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\TestUtils\TestsBridge;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        TestsBridge::reset();
    }

    protected function getPersistedArtisanMock(): Artisan
    {
        $result = $this->getMockBuilder(ArtisanE::class)->onlyMethods(['getId'])->getMock();
        $result->method('getId')->willReturn(1);

        return Artisan::wrap($result);
    }
}
