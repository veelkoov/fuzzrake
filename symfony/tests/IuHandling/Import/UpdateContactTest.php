<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Import;

use App\Data\Definitions\ContactPermit;
use App\IuHandling\Import\UpdateContact;
use App\Tests\TestUtils\Cases\FuzzrakeTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use PHPUnit\Framework\Attributes\Small;

#[Small]
class UpdateContactTest extends FuzzrakeTestCase
{
    public function testPermissionAndDescription(): void
    {
        // New creator, NO
        $result = $this->getUpdateContactPermit(null, ContactPermit::NO);
        self::assertFalse($result->isAllowed);
        self::assertSame('Never', $result->description);

        // New creator, FEEDBACK
        $result = $this->getUpdateContactPermit(null, ContactPermit::FEEDBACK);
        self::assertTrue($result->isAllowed);
        self::assertSame('Feedback', $result->description);

        // Existing creator, NO ---> FEEDBACK
        $result = $this->getUpdateContactPermit(ContactPermit::NO, ContactPermit::FEEDBACK);
        self::assertFalse($result->isAllowed);
        self::assertSame('Never → Feedback', $result->description);

        // Existing creator, FEEDBACK ---> NO
        $result = $this->getUpdateContactPermit(ContactPermit::FEEDBACK, ContactPermit::NO);
        self::assertFalse($result->isAllowed);
        self::assertSame('Feedback → Never', $result->description);

        // Existing creator, NO ---> NO
        $result = $this->getUpdateContactPermit(ContactPermit::NO, ContactPermit::NO);
        self::assertFalse($result->isAllowed);
        self::assertSame('Never', $result->description);

        // Existing creator, ANNOUNCEMENTS ---> FEEDBACK
        $result = $this->getUpdateContactPermit(ContactPermit::ANNOUNCEMENTS, ContactPermit::FEEDBACK);
        self::assertTrue($result->isAllowed);
        self::assertSame('Announcements → Feedback', $result->description);
    }

    public function testAddress(): void
    {
        // Added creator with email address
        $result = $this->getUpdateContact(null, 'address@example.com');
        self::assertSame('address@example.com', $result->address);

        // Creator update: added email
        $result = $this->getUpdateContact('', 'address@example.com');
        self::assertSame('', $result->address);

        // Creator update: changed email
        $result = $this->getUpdateContact('addresso@example.com', 'addressn@example.com');
        self::assertSame('addresso@example.com', $result->address);

        // Creator update: removed email
        $result = $this->getUpdateContact('addresso@example.com', '');
        self::assertSame('', $result->address);
    }

    private function getUpdateContactPermit(?ContactPermit $old, ContactPermit $new): UpdateContact
    {
        $oldA = null === $old ? new Creator() : self::getPersistedCreatorMock()->setContactAllowed($old);
        $newA = Creator::new()->setContactAllowed($new);

        return UpdateContact::from($oldA, $newA);
    }

    private function getUpdateContact(?string $oldAddress, string $newAddress): UpdateContact
    {
        $oldA = null === $oldAddress ? new Creator() : self::getPersistedCreatorMock()->setEmailAddress($oldAddress)
            ->setContactAllowed('' === $oldAddress ? ContactPermit::NO : ContactPermit::CORRECTIONS);
        $newA = Creator::new()->setEmailAddress($newAddress)
            ->setContactAllowed('' === $newAddress ? ContactPermit::NO : ContactPermit::CORRECTIONS);

        return UpdateContact::from($oldA, $newA);
    }
}
