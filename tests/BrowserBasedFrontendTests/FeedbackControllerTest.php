<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Tests\BrowserBasedFrontendTests\Traits\MainPageTestsTrait;
use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Facebook\WebDriver\Exception\WebDriverException;

/**
 * @see \App\Tests\Controller\FeedbackControllerTest
 */
class FeedbackControllerTest extends PantherTestCaseWithEM
{
    use MainPageTestsTrait;

    /**
     * @return array<array{string, Artisan}>
     */
    public function feedbackFromMakersCardCarriesMakerIdOverToTheFormDataProvider(): array
    {
        $official = Artisan::new()->setCountry('FI')->setName('Modern maker')
            ->setMakerId('FDBCKMR');

        $placeholder = Artisan::new()->setCountry('CZ')->setName('Early maker')
            ->setFormerMakerIds('M000000');

        return [
            'With an official maker ID' => ['FDBCKMR', $official],
            'With placeholder maker ID' => ['M000000', $placeholder],
        ];
    }

    /**
     * @throws WebDriverException
     *
     * @dataProvider feedbackFromMakersCardCarriesMakerIdOverToTheFormDataProvider
     */
    public function testFeedbackFromMakersCardCarriesMakerIdOverToTheForm(string $expectedMakerId, Artisan $artisan): void
    {
        $client = static::createPantherClient();
        self::persistAndFlush($artisan);
        $this->clearCache();

        $client->request('GET', '/');
        self::skipCheckListAdultAllowNsfw($client, 1);

        self::openMakerCardByClickingOnTheirNameInTheTable($client, $artisan->getName());
        self::openDataOutdatedPopup($client);

        $client->clickLink('submit the feedback form');

        self::assertCount(2, $client->getWindowHandles());
        $client->switchTo()->window($client->getWindowHandles()[1]);

        $client->waitForVisibility('h1', 10);
        self::assertSelectorTExtSame('h1', 'Feedback form');
        self::assertSelectorExists('//input[@id="feedback_maker" and @value="'.$expectedMakerId.'"]');
    }

    public function testExplanationsShowingUpAndFormBlocksForSpecialOptions(): void
    {
        // TODO
    }

    public function testSimpleFormSubmissionWithCaptcha(): void
    {
        // TODO
    }
}
