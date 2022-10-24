<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Import;

use App\DataDefinitions\ContactPermit;
use App\IuHandling\Import\UpdateContact;
use App\Tests\TestUtils\Cases\TestCase;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Contact;
use InvalidArgumentException;

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
        $result = $this->getUpdateContactAddress(null, null, Contact::E_MAIL, 'address@example.com');
        self::assertTrue($result->isEmail);
        self::assertEquals('E-MAIL', $result->method);
        self::assertEquals('address@example.com', $result->address);

        // New maker with Telegram
        $result = $this->getUpdateContactAddress(null, null, Contact::TELEGRAM, '@telegram');
        self::assertFalse($result->isEmail);
        self::assertEquals('TELEGRAM', $result->method);
        self::assertEquals('@telegram', $result->address);

        // Updated maker earlier with nothing, now with e-mail address
        $result = $this->getUpdateContactAddress('', '', Contact::E_MAIL, 'address@example.com');
        self::assertFalse($result->isEmail);
        self::assertEquals('', $result->method);
        self::assertEquals('', $result->address);

        // Updated maker earlier with nothing, now with Telegram
        $result = $this->getUpdateContactAddress('', '', Contact::TELEGRAM, '@telegram');
        self::assertFalse($result->isEmail);
        self::assertEquals('', $result->method);
        self::assertEquals('', $result->address);

        // Updated maker earlier with e-mail, now with e-mail address
        $result = $this->getUpdateContactAddress(Contact::E_MAIL, 'addresso@example.com', Contact::E_MAIL, 'addressn@example.com');
        self::assertTrue($result->isEmail);
        self::assertEquals('E-MAIL', $result->method);
        self::assertEquals('addresso@example.com', $result->address);

        // Updated maker earlier with e-mail, now with Telegram
        $result = $this->getUpdateContactAddress(Contact::E_MAIL, 'address@example.com', Contact::TELEGRAM, '@telegram');
        self::assertTrue($result->isEmail);
        self::assertEquals('E-MAIL', $result->method);
        self::assertEquals('address@example.com', $result->address);

        // Updated maker earlier with Telegram, now with e-mail
        $result = $this->getUpdateContactAddress(Contact::TELEGRAM, '@telegram', Contact::E_MAIL, 'address@example.com');
        self::assertFalse($result->isEmail);
        self::assertEquals('TELEGRAM', $result->method);
        self::assertEquals('@telegram', $result->address);

        // Updated maker earlier with Telegram, now with Telegram
        $result = $this->getUpdateContactAddress(Contact::TELEGRAM, '@telegram', Contact::E_MAIL, '@username');
        self::assertFalse($result->isEmail);
        self::assertEquals('TELEGRAM', $result->method);
        self::assertEquals('@telegram', $result->address);
    }

    private function getUpdateContactPermit(?ContactPermit $old, ContactPermit $new): UpdateContact
    {
        $oldA = null === $old ? new Artisan() : self::getPersistedArtisanMock()->setContactAllowed($old);
        $newA = Artisan::new()->setContactAllowed($new);

        return UpdateContact::from($oldA, $newA);
    }

    private function getUpdateContactAddress(?string $oldMethod, ?string $oldAddress, string $newMethod, string $newAddress): UpdateContact
    {
        if (null === $oldMethod && null === $oldAddress) {
            $oldA = new Artisan();
        } elseif (null !== $oldMethod && null !== $oldAddress) {
            $oldA = self::getPersistedArtisanMock()->setContactMethod($oldMethod)->setContactAddressPlain($oldAddress);
        } else {
            throw new InvalidArgumentException();
        }

        $newA = Artisan::new()->setContactMethod($newMethod)->setContactAddressPlain($newAddress);

        return UpdateContact::from($oldA, $newA);
    }
}
