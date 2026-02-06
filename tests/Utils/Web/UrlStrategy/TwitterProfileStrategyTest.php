<?php

declare(strict_types=1);

namespace App\Tests\Utils\Web\UrlStrategy;

use App\Utils\Web\UrlStrategy\TwitterProfileStrategy;
use Override;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class TwitterProfileStrategyTest extends TestCase
{
    private TwitterProfileStrategy $subject;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new TwitterProfileStrategy();
    }

    public function testSimplifiedWorkingScenario(): void
    {
        $input = '<html><head><meta content="TheTitle" property="og:title"/><meta content="TheDescription" property="og:description"/></head></html>';
        $result = $this->subject->filterContents($input);

        self::assertSame("TheTitle\nTheDescription", $result);
    }

    public function testEmptyInformation(): void
    {
        $input = '<html><head><meta content="" property="og:title"/><meta content="" property="og:description"/></head></html>';
        $result = $this->subject->filterContents($input);

        self::assertSame("\n", $result);
    }

    public function testUnparseableInput(): void
    {
        $input = '{"oops": "This is not a HTML"}';
        $result = $this->subject->filterContents($input);

        self::assertSame($input, $result);
    }

    public function testMissingExpectedHeadElement(): void
    {
        $input = '<html><head><meta content="TheDescription" property="og:description"/></head></html>';
        $result = $this->subject->filterContents($input);

        self::assertSame($input, $result);
    }
}
