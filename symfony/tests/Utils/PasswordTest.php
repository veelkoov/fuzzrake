<?php

declare(strict_types=1);

namespace App\Tests\Utils;

use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Password;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class PasswordTest extends TestCase
{
    public function testPlaintextGetsEncrypted(): void
    {
        $creator = new Creator();
        $creator->setPassword('test-password-555');

        Password::encryptOn($creator);

        self::assertStringStartsWith('$2y$12$', $creator->getPassword()); // We'll know when PHP changes the default algo :P
    }
}
