<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Import;

use App\DataDefinitions\ContactPermit;
use App\IuHandling\Import\UpdateContact;
use App\Tests\TestUtils\Cases\TestCase;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

class UpdateContactTest extends TestCase
{
    public function testFromDescription(): void
    {
        // New artisan, NO
        $result = $this->getUpdateContact(null, ContactPermit::NO);
        self::assertFalse($result->isAllowed);
        self::assertEquals('NO', $result->description);

        // New artisan, FEEDBACK
        $result = $this->getUpdateContact(null, ContactPermit::FEEDBACK);
        self::assertTrue($result->isAllowed);
        self::assertEquals('FEEDBACK', $result->description);

        // Existing artisan, NO ---> FEEDBACK
        $result = $this->getUpdateContact(ContactPermit::NO, ContactPermit::FEEDBACK);
        self::assertFalse($result->isAllowed);
        self::assertEquals('NO → FEEDBACK', $result->description);

        // Existing artisan, FEEDBACK ---> NO
        $result = $this->getUpdateContact(ContactPermit::FEEDBACK, ContactPermit::NO);
        self::assertFalse($result->isAllowed);
        self::assertEquals('FEEDBACK → NO', $result->description);

        // Existing artisan, NO ---> NO
        $result = $this->getUpdateContact(ContactPermit::NO, ContactPermit::NO);
        self::assertFalse($result->isAllowed);
        self::assertEquals('NO', $result->description);

        // Existing artisan, ANNOUNCEMENTS ---> FEEDBACK
        $result = $this->getUpdateContact(ContactPermit::ANNOUNCEMENTS, ContactPermit::FEEDBACK);
        self::assertTrue($result->isAllowed);
        self::assertEquals('ANNOUNCEMENTS → FEEDBACK', $result->description);
    }

    // TODO: Test other parts of from()

    private function getUpdateContact(?ContactPermit $old, ContactPermit $new): UpdateContact
    {
        $oldA = null === $old ? new Artisan() : self::getPersistedArtisanMock()->setContactAllowed($old);
        $newA = Artisan::new()->setContactAllowed($new);

        return UpdateContact::from($oldA, $newA);
    }
}
