<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Tests\BrowserBasedFrontendTests\Traits\MainPageTestsTrait;
use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use App\Utils\Creator\SmartAccessDecorator as Creator;
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
     * @return array<array{string, Creator}>
     */
    public function feedbackFromCreatorCardCarriesCreatorIdOverToTheFormDataProvider(): array
    {
        $official = Creator::new()->setCountry('FI')->setName('Modern creator')
            ->setCreatorId('TEST001');

        $placeholder = Creator::new()->setCountry('CZ')->setName('Early creator')
            ->setFormerCreatorIds(['M000000']);

        return [
            'With an official creator ID' => ['TEST001', $official],
            'With placeholder creator ID' => ['M000000', $placeholder],
        ];
    }

    /**
     * @throws WebDriverException
     *
     * @dataProvider feedbackFromCreatorCardCarriesCreatorIdOverToTheFormDataProvider
     */
    public function testFeedbackFromCreatorCardCarriesCreatorIdOverToTheForm(string $expectedCreatorId, Creator $creator): void
    {
        self::persistAndFlush($creator);
        $this->clearCache();

        $this->client->request('GET', '/index.php/');
        $this->skipCheckListAdultAllowNsfw(1);

        $this->openCreatorCardByClickingOnTheirNameInTheTable($creator->getName());
        $this->openDataOutdatedPopupFromTheCreatorCard();

        $this->client->clickLink('submit the feedback form');

        self::assertCount(2, $this->client->getWindowHandles());
        $handle = $this->client->getWindowHandles()[1];
        self::assertIsString($handle);
        $this->client->switchTo()->window($handle);

        $this->client->waitForVisibility('h1', 10);
        self::assertSelectorTExtSame('h1', 'Feedback form');
        self::assertSelectorExists('//input[@id="feedback_creator" and @value="'.$expectedCreatorId.'"]');
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
}
