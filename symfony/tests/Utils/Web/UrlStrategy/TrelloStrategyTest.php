<?php

declare(strict_types=1);

namespace App\Tests\Utils\Web\UrlStrategy;

use App\Utils\Web\Url\FreeUrl;
use App\Utils\Web\UrlStrategy\TrelloStrategy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class TrelloStrategyTest extends TestCase
{
    private TrelloStrategy $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new TrelloStrategy();
    }

    #[DataProvider('coerceUrlDataProvider')]
    public function testCoerceUrl(string $input, string $expected): void
    {
        $result = $this->subject->getUrlForTracking(new FreeUrl($input))->getUrl();

        self::assertSame($expected, $result);
    }

    /**
     * @return array<array{string, string}>
     */
    public static function coerceUrlDataProvider(): array
    {
        return [
            [
                'https://trello.com/b/aBcDeFgHi/some-test-name',
                'https://trello.com/1/boards/aBcDeFgHi?fields=name%2Cdesc&cards=visible&card_fields=name%2Cdesc',
            ],
            [
                'https://trello.com/b/aBcDeFgHi',
                'https://trello.com/1/boards/aBcDeFgHi?fields=name%2Cdesc&cards=visible&card_fields=name%2Cdesc',
            ],
            [
                'https://trello.com/c/aBcDeFgHi/some-test-description',
                'https://trello.com/1/cards/aBcDeFgHi?fields=name%2Cdesc',
            ],
            [
                'https://trello.com/c/aBcDeFgHi',
                'https://trello.com/1/cards/aBcDeFgHi?fields=name%2Cdesc',
            ],
        ];
    }
}
