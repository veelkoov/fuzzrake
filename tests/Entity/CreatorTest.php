<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Creator;
use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use PHPUnit\Framework\Attributes\Small;

#[Small]
class CreatorTest extends FuzzrakeTestCase
{
    public function testSettingCreatorIdAddsToTheEmptyCollection(): void
    {
        $creator = new Creator();
        $creator->setCreatorId('TEST001');

        $this->validateCreatorIds($creator, 'TEST001', [], ['TEST001']);
    }

    public function testSettingCreatorIdAddsToTheCollection(): void
    {
        $creator = new Creator();
        $creator->setFormerCreatorIds(['TEST002', 'TEST003']);
        $creator->setCreatorId('TEST001');

        $this->validateCreatorIds($creator, 'TEST001', ['TEST002', 'TEST003'], ['TEST001', 'TEST002', 'TEST003']);
    }

    public function testSettingFormerCreatorIdsWorksWithNoCreatorIdSet(): void
    {
        $creator = new Creator();
        $creator->setFormerCreatorIds(['TEST003', 'TEST004']);

        $this->validateCreatorIds($creator, '', ['TEST003', 'TEST004'], ['TEST003', 'TEST004']);
    }

    public function testSettingFormerCreatorIdsRemovesObsoleteCreatorIdsLeavingAlreadyPresent(): void
    {
        $creator = new Creator();
        $creator->setFormerCreatorIds(['TEST005', 'TEST006']);

        $creator->setFormerCreatorIds(['TEST006', 'TEST007']);

        $this->validateCreatorIds($creator, '', ['TEST006', 'TEST007'], ['TEST006', 'TEST007']);
    }

    public function testSettingFormerCreatorIdsDoesntAffectCreatorId(): void
    {
        $creator = new Creator();
        $creator->setCreatorId('TEST003');
        $creator->setFormerCreatorIds(['TEST008', 'TEST009', 'TEST003']);

        $this->validateCreatorIds($creator, 'TEST003', ['TEST008', 'TEST009'], ['TEST003', 'TEST008', 'TEST009']);
    }

    /**
     * @param list<string> $formerCreatorIds
     * @param list<string> $allCreatorIds
     */
    private function validateCreatorIds(Creator $creator, string $creatorId, array $formerCreatorIds, array $allCreatorIds): void
    {
        self::assertSame($creatorId, $creator->getCreatorId());

        self::assertSameItems($formerCreatorIds, $creator->getFormerCreatorIds());
        self::assertSameItems($allCreatorIds, $creator->getAllCreatorIds());
    }
}
