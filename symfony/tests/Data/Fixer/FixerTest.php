<?php

declare(strict_types=1);

namespace App\Tests\Data\Fixer;

use App\Data\Definitions\Fields\Field;
use App\Data\Fixer\Fixer;
use App\Tests\TestUtils\Cases\Traits\ContainerTrait;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[Medium]
class FixerTest extends KernelTestCase // Using real kernel to test autowiring set up as well
{
    use ContainerTrait;

    private readonly Fixer $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->subject = self::getContainerService(Fixer::class);
    }

    /**
     * @param list<string>|string $input
     * @param list<string>|string $expected
     */
    #[DataProvider('getFixedDataProvider')]
    public function testGetFixed(Field $field, array|string $input, array|string $expected): void
    {
        $creator = new Creator();
        $creator->set($field, $input);

        $this->subject->fix($creator, $field);

        self::assertEquals($expected, $creator->get($field));
    }

    /**
     * @return list<array{Field, list<string>|string, list<string>|string}>
     */
    public static function getFixedDataProvider(): array
    {
        return [
            [Field::NAME, ' The name ', 'The name'],

            // N/A must always be removed, especially for FORMERLY due to the risk of matching two totally unrelated creators
            [Field::FORMERLY, ['N/A'], []],
            [Field::FORMERLY, ['n/a'], []],

            [
                Field::FORMERLY,
                [' the old name ', 'an older name and stuff', 'something/anything'],
                ['the old name', 'an older name and stuff', 'something/anything'],
            ],
            [
                Field::FORMERLY,
                ['the old name / older name'],
                ['the old name / older name'],
            ],
            [
                Field::FORMERLY,
                [' the old name ', 'an older name and stuff', 'something/anything'],
                ['the old name', 'an older name and stuff', 'something/anything'],
            ],
            [Field::SINCE, '2021-02-15', '2021-02'],
            [Field::SINCE, '9999-99-99', '9999-99'],
            [Field::PAYMENT_METHODS, ['  ', 'A', "\t"], ['A']], // Any list should be cleaned up
            [Field::PAYMENT_METHODS, ['Bank transfer, Paypal'], ['Bank transfers', 'PayPal']],
            [
                Field::PAYMENT_METHODS,
                ['Bank transfers (in Abcd: Defgh, Ijklm, NOPQ and any Rstuv), Paypal and Cash'],
                ['Bank transfers (in Abcd: Defgh, Ijklm, NOPQ and any Rstuv)', 'PayPal', 'Cash'],
            ],
            [Field::CURRENCIES_ACCEPTED, ['Euro, Usd'], ['EUR', 'USD']],
            [
                Field::LANGUAGES,
                ['English and a little bit of Finnish, Estonian (with Google translate)'],
                ['English', 'Finnish (limited)', 'Estonian (with a translator)'],
            ],
            [Field::FEATURES, ['Follow-me eyes', 'Attached tail'], ['Attached tail', 'Follow-me eyes']],
            [Field::ORDER_TYPES, ['Aaaaa'], ['Aaaaa']],
            // FIXME: https://github.com/veelkoov/fuzzrake/issues/305
            // [Field::PAYMENT_PLANS, ['100% upfront'], ['None']],
            [Field::PAYMENT_PLANS, ['30% upfront, rest in 100 Eur/mth until fully paid'], ['30% upfront, rest in 100 Eur/mth until fully paid']],
            [Field::URL_MINIATURES, ['https://example.com/'], ['https://example.com/']],
            [Field::URL_COMMISSIONS, ['https://example.com/'], ['https://example.com/']],
            [Field::SPECIES_DOES, ['Dogs and cats'], ['Dogs and cats']],
            [
                Field::URL_COMMISSIONS,
                ['https://www.getfursu.it/', 'http://www.tumblr.com/getfursuit/'],
                ['https://www.getfursu.it/', 'https://www.tumblr.com/getfursuit/'],
            ],
            [Field::NOTES, 'ABCD', 'ABCD'],
            [Field::STATE, ' ID', 'Idaho'],
            [
                Field::OTHER_FEATURES,
                ['armsleeves', 'Posable tounges'],
                ['Arm sleeves', 'Poseable tongue'],
            ],
        ];
    }
}
