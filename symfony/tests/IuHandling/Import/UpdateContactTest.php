<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Import;

use App\Data\Definitions\ContactPermit;
use App\IuHandling\Import\UpdateContact;
use App\Tests\TestUtils\Cases\TestCase;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;

/**
 * @small
 */
class UpdateContactTest extends TestCase
{
    public function testPermissionAndDescription(): void
    {
        // New artisan, NO
        $result = $this->getUpdateContactPermit(null, ContactPermit::NO);
        self::assertFalse($result->isAllowed);
        self::assertEquals('Never', $result->description);

        // New artisan, FEEDBACK
        $result = $this->getUpdateContactPermit(null, ContactPermit::FEEDBACK);
        self::assertTrue($result->isAllowed);
        self::assertEquals('Feedback', $result->description);

        // Existing artisan, NO ---> FEEDBACK
        $result = $this->getUpdateContactPermit(ContactPermit::NO, ContactPermit::FEEDBACK);
        self::assertFalse($result->isAllowed);
        self::assertEquals('Never → Feedback', $result->description);

        // Existing artisan, FEEDBACK ---> NO
        $result = $this->getUpdateContactPermit(ContactPermit::FEEDBACK, ContactPermit::NO);
        self::assertFalse($result->isAllowed);
        self::assertEquals('Feedback → Never', $result->description);

        // Existing artisan, NO ---> NO
        $result = $this->getUpdateContactPermit(ContactPermit::NO, ContactPermit::NO);
        self::assertFalse($result->isAllowed);
        self::assertEquals('Never', $result->description);

        // Existing artisan, ANNOUNCEMENTS ---> FEEDBACK
        $result = $this->getUpdateContactPermit(ContactPermit::ANNOUNCEMENTS, ContactPermit::FEEDBACK);
        self::assertTrue($result->isAllowed);
        self::assertEquals('Announcements → Feedback', $result->description);
    }

    public function testMethodAndAddress(): void
    {
        // New maker with e-mail address
        $result = $this->getUpdateContactAddress(null, 'address@example.com');
        self::assertEquals('address@example.com', $result->address);

        // New maker with Telegram
        $result = $this->getUpdateContactAddress(null, '@telegram');
        self::assertEquals('@telegram', $result->address);

        // Updated maker earlier with nothing, now with e-mail address
        $result = $this->getUpdateContactAddress('', 'address@example.com');
        self::assertEquals('', $result->address);

        // Updated maker earlier with nothing, now with Telegram
        $result = $this->getUpdateContactAddress('', '@telegram');
        self::assertEquals('', $result->address);

        // Updated maker earlier with e-mail, now with e-mail address
        $result = $this->getUpdateContactAddress('addresso@example.com', 'addressn@example.com');
        self::assertEquals('addresso@example.com', $result->address);

        // Updated maker earlier with e-mail, now with Telegram
        $result = $this->getUpdateContactAddress('address@example.com', '@telegram');
        self::assertEquals('address@example.com', $result->address);

        // Updated maker earlier with Telegram, now with e-mail
        $result = $this->getUpdateContactAddress('@telegram', 'address@example.com');
        self::assertEquals('@telegram', $result->address);

        // Updated maker earlier with Telegram, now with Telegram
        $result = $this->getUpdateContactAddress('@telegram', '@username');
        self::assertEquals('@telegram', $result->address);
    }

    private function getUpdateContactPermit(?ContactPermit $old, ContactPermit $new): UpdateContact
    {
        $oldA = null === $old ? new Artisan() : self::getPersistedArtisanMock()->setContactAllowed($old);
        $newA = Artisan::new()->setContactAllowed($new);

        return UpdateContact::from($oldA, $newA);
    }

    private function getUpdateContactAddress(?string $oldAddress, string $newAddress): UpdateContact
    {
        $oldA = null === $oldAddress ? new Artisan() : self::getPersistedArtisanMock()->setEmailAddress($oldAddress);
        $newA = Artisan::new()->setEmailAddress($newAddress);

        return UpdateContact::from($oldA, $newA);
    }
}
