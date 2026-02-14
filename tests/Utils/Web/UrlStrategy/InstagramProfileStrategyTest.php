<?php

declare(strict_types=1);

namespace App\Tests\Utils\Web\UrlStrategy;

use App\Utils\Web\Url\FreeUrl;
use App\Utils\Web\UrlStrategy\InstagramProfileStrategy;
use Override;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class InstagramProfileStrategyTest extends TestCase
{
    private InstagramProfileStrategy $subject;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new InstagramProfileStrategy();
    }

    public function testCoerceUrl(): void
    {
        $expected = 'https://www.instagram.com/getfursu.it/profilecard/';
        $input = new FreeUrl('https://www.instagram.com/getfursu.it/', '');
        $result = $this->subject->getUrlForTracking($input)->getUrl();

        self::assertSame($expected, $result);
    }

    public function testSimplifiedWorkingScenario(): void
    {
        $input = '<html><head><meta content="Expected description" property="description"/></head></html>';
        $result = $this->subject->filterContents($input);

        self::assertSame('Expected description', $result);
    }

    public function testEmptyDescription(): void
    {
        $input = '<html><head><meta content="" property="description"/></head></html>';
        $result = $this->subject->filterContents($input);

        self::assertSame('', $result);
    }

    public function testUnparseableInput(): void
    {
        $input = '{"oops": "This is not a HTML"}';
        $result = $this->subject->filterContents($input);

        self::assertSame($input, $result);
    }

    public function testMissingExpectedField(): void
    {
        $input = '<html><head><meta content="TheDescription" property="not-description"/></head></html>';
        $result = $this->subject->filterContents($input);

        self::assertSame($input, $result);
    }
}
