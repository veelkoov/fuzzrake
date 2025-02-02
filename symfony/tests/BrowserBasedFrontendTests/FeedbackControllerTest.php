<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Tests\BrowserBasedFrontendTests\Traits\MainPageTestsTrait;
use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;

/**
 * @see \App\Tests\Controller\FeedbackControllerTest
 *
 * @large
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
            ->setFormerMakerIds(['M000000']);

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
        self::persistAndFlush($artisan);
        $this->clearCache();

        $this->client->request('GET', '/index.php/');
        $this->skipCheckListAdultAllowNsfw(1);

        $this->openMakerCardByClickingOnTheirNameInTheTable($artisan->getName());
        $this->openDataOutdatedPopupFromTheMakerCard();

        $this->client->clickLink('submit the feedback form');

        self::assertCount(2, $this->client->getWindowHandles());
        $handle = $this->client->getWindowHandles()[1];
        self::assertIsString($handle);
        $this->client->switchTo()->window($handle);

        $this->client->waitForVisibility('h1', 10);
        self::assertSelectorTExtSame('h1', 'Feedback form');
        self::assertSelectorExists('//input[@id="feedback_maker" and @value="'.$expectedMakerId.'"]');
    }

    /**
     * @throws WebDriverException
     */
    public function testExplanationsShowingUpAndFormBlocksForSpecialOptions(): void
    {
        $this->client->request('GET', '/index.php/feedback');

        $crawler = $this->client->getCrawler();

        self::assertCount(8, $crawler->filter('input[name="feedback[subject]"]'));

        $buttonXpath = '//input[@type="submit"]';
        $noticeCssSel = '#feedback-subject-notice';

        // 1st option
        $this->client->findElement(WebDriverBy::cssSelector('input[value="Help me get a fursuit"]'))->click();
        self::waitUntilHides($buttonXpath);
        self::assertVisible($noticeCssSel);
        self::assertSelectorTextContains($noticeCssSel, 'getfursu.it maintainer does not assist individuals');

        // 3rd option
        $this->client->findElement(WebDriverBy::cssSelector('input[value="Maker\'s website/social account is no longer working"]'))->click();
        self::waitUntilShows($buttonXpath);
        self::assertInvisible($noticeCssSel);

        // 2nd option
        $this->client->findElement(WebDriverBy::cssSelector('input[value="Maker\'s commissions info (open/closed) is inaccurate"]'))->click();
        self::waitUntilHides($buttonXpath);
        self::assertVisible($noticeCssSel);
        self::assertSelectorTextContains($noticeCssSel, 'This cannot be adjusted manually.');

        // 5th option
        $this->client->findElement(WebDriverBy::cssSelector('input[value="Other information on this website needs attention (not related to a particular maker)"]'))->click();
        self::waitUntilShows($buttonXpath);
        self::assertInvisible($noticeCssSel);

        // 4th option
        $this->client->findElement(WebDriverBy::cssSelector('input[value="Other maker\'s information is (partially) outdated"]'))->click();
        self::waitUntilHides($buttonXpath);
        self::assertVisible($noticeCssSel);
        self::assertSelectorTextContains($noticeCssSel, 'All the information needs to be updated by the makers themselves.');

        // 6th option
        $this->client->findElement(WebDriverBy::cssSelector('input[value="Report a technical problem/bug with this website"]'))->click();
        self::waitUntilShows($buttonXpath);
        self::assertInvisible($noticeCssSel);

        // 7th option, no visual change
        $this->client->findElement(WebDriverBy::cssSelector('input[value="Suggest an improvement to this website"]'))->click();
        self::assertVisible($buttonXpath);
        self::assertInvisible($noticeCssSel);

        // 8th option, no visual change
        $this->client->findElement(WebDriverBy::cssSelector('input[value="Other (please provide adequate details and context)"]'))->click();
        self::assertVisible($buttonXpath);
        self::assertInvisible($noticeCssSel);
    }

    /**
     * @throws WebDriverException
     */
    public function testCaptchaWorksBySimpleSubmission(): void
    {
        $this->client->request('GET', '/index.php/feedback');

        $this->client->waitForVisibility('h1', 10);

        $this->client->getCrawler()->selectButton('Send')->form([
            'feedback[details]'       => 'Testing details',
            'feedback[subject]'       => 'Other (please provide adequate details and context)',
            'feedback[noContactBack]' => true,
        ]);

        $this->client->findElement(WebDriverBy::xpath('//input[@type="submit"]'))->click();
        $this->client->waitForVisibility('div.alert', 10);

        self::assertSelectorTextSame('h1', 'Feedback submitted');
        self::assertSelectorTextContains('div.alert', 'Feedback has been successfully submitted.');
    }
}
