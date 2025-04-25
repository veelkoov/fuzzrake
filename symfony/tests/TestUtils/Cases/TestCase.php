<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Entity\Creator as CreatorE;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\TestUtils\TestsBridge;
use Override;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        TestsBridge::reset();
    }

    protected function getPersistedCreatorMock(): Creator
    {
        $result = $this->getMockBuilder(CreatorE::class)->onlyMethods(['getId'])->getMock();
        $result->method('getId')->willReturn(1);

        return Creator::wrap($result);
    }
}
