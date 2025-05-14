<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Entity\Creator as CreatorE;
use App\Utils\Creator\SmartAccessDecorator as Creator;

trait MocksTrait
{
    protected function getPersistedCreatorMock(): Creator
    {
        $result = $this->getMockBuilder(CreatorE::class)->onlyMethods(['getId'])->getMock();
        $result->method('getId')->willReturn(1);

        return Creator::wrap($result);
    }
}
