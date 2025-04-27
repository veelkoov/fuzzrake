<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Data\Validator\Validator;
use App\Twig\AdminExtensions;
use PHPUnit\Framework\TestCase;

/**
 * @small
 */
class AdminExtensionsTest extends TestCase
{
    /**
     * @dataProvider linkUrlsDataProvider
     */
    public function testLinkUrls(string $input, string $expectedOutput): void
    {
        $validatorMock = $this->createMock(Validator::class);
        $subject = new AdminExtensions($validatorMock);

        self::assertEquals($expectedOutput, $subject->linkUrls($input));
    }

    /**
     * @return array<array{string, string}>
     */
    public function linkUrlsDataProvider(): array
    {
        return [
            [
                'just plain text',
                'just plain text',
            ], [
                'prefix http://getfursu.it/new suffix',
                'prefix <a href="http://getfursu.it/new" target="_blank">http://getfursu.it/new</a> suffix',
            ], [
                'prefix http://getfursu.it/new middle https://getfursu.it/info suffix',
                'prefix <a href="http://getfursu.it/new" target="_blank">http://getfursu.it/new</a> middle <a href="https://getfursu.it/info" target="_blank">https://getfursu.it/info</a> suffix',
            ],
        ];
    }
}
