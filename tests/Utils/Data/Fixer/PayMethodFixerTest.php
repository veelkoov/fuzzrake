<?php

declare(strict_types=1);

namespace App\Tests\Utils\Data\Fixer;

use App\Tests\TestUtils\DataDefinitions;
use App\Utils\Data\Fixer\PayMethodFixer;
use PHPUnit\Framework\TestCase;

class PayMethodFixerTest extends TestCase
{
    private static PayMethodFixer $subject;

    public static function setUpBeforeClass(): void
    {
        $strings = DataDefinitions::get('generic.yaml', 'strings');
        $payments = DataDefinitions::get('payments.yaml', 'paymentMethods');

        self::$subject = new PayMethodFixer($payments, $strings);
    }

    /**
     * @dataProvider fixDataProvider
     */
    public function testFix(string $expected, $actual): void
    {
        self::assertEquals($actual, self::$subject->fix($expected));
    }

    public function fixDataProvider(): array
    {
        return [
            [
                'Bank transfer, Paypal',
                "Bank transfers\nPayPal",
            ], [
                "Bank transfers (in Abcd: Defgh, Ijklm, NOPQ and any Rstuv)\nPaypal\nCash",
                "Bank transfers (in Abcd: Defgh, Ijklm, NOPQ and any Rstuv)\nPayPal\nCash",
            ],
        ];
    }
}
