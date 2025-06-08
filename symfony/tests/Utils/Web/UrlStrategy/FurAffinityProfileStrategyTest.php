<?php

declare(strict_types=1);

namespace App\Tests\Utils\Web\UrlStrategy;

use App\Utils\Web\UrlStrategy\FurAffinityProfileStrategy;
use Override;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[Small]
class FurAffinityProfileStrategyTest extends TestCase
{
    private FurAffinityProfileStrategy $subject;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FurAffinityProfileStrategy();
    }

    public function testSimplifiedWorkingScenario(): void
    {
        $input = '<html><head><body><div id="page-userpage"><div class="userpage-profile">Expected description</div></div></body></head></html>';
        $result = $this->subject->filterContents($input);

        self::assertSame('Expected description', $result);
    }

    public function testFailedMatching(): void
    {
        $input = '<html><head><body><div id="page-userpage"><div class="wrong-class">Description</div></div></body></head></html>';
        $result = $this->subject->filterContents($input);

        self::assertSame($input, $result);
    }
}
