<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Tests\BrowserBasedFrontendTests\Traits\MainPageTestsTrait;
use App\Tests\TestUtils\Cases\FuzzrakePantherTestCase;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Large;

/**
 * @see \App\Tests\Controller\FeedbackControllerTest
 */
#[Large]
class FeedbackControllerTest extends FuzzrakePantherTestCase
{
    use MainPageTestsTrait;

    /**
     * @return array<array{string, Creator}>
     */
    public static function feedbackFromCreatorCardCarriesCreatorIdOverToTheFormDataProvider(): array
    {
        $official = new Creator()->setCountry('FI')->setName('Modern creator')
            ->setCreatorId('TEST001');

        $placeholder = new Creator()->setCountry('CZ')->setName('Early creator')
            ->setFormerCreatorIds(['M000000']);

        return [
            'With an official creator ID' => ['TEST001', $official],
            'With placeholder creator ID' => ['M000000', $placeholder],
        ];
    }

    /**
     * @throws WebDriverException
     */
    #[DataProvider('feedbackFromCreatorCardCarriesCreatorIdOverToTheFormDataProvider')]
    public function testFeedbackFromCreatorCardCarriesCreatorIdOverToTheForm(string $expectedCreatorId, Creator $creator): void
    {
        self::persistAndFlush($creator);
        $this->clearCache();

        self::$client->request('GET', '/index.php/');
        $this->skipCheckListAdultAllowNsfw(1);

        $this->openCreatorCardByClickingOnTheirNameInTheTable($creator->getName());
        $this->openDataOutdatedPopupFromTheCreatorCard();

        self::$client->clickLink('submit the feedback form');

        self::assertCount(2, self::$client->getWindowHandles());
        $handle = self::$client->getWindowHandles()[1];
        self::assertIsString($handle);
        self::$client->switchTo()->window($handle);

        self::$client->waitForVisibility('h1', 10);
        self::assertSelectorTextSame('h1', 'Feedback form');
        self::assertSelectorExists('//input[@id="feedback_creator" and @value="'.$expectedCreatorId.'"]');
    }

    /**
     * @throws WebDriverException
     */
    public function testExplanationsShowingUpAndFormBlocksForSpecialOptions(): void
    {
        self::$client->request('GET', '/index.php/feedback');

        $crawler = self::$client->getCrawler();

        self::assertCount(8, $crawler->filter('input[name="feedback[subject]"]'));

        $buttonXpath = '//input[@type="submit"]';
        $noticeCssSel = '#feedback-subject-notice';

        // 1st option
        self::$client->findElement(WebDriverBy::cssSelector('input[value="Help me get a fursuit"]'))->click();
        self::waitUntilHides($buttonXpath);
        self::assertVisible($noticeCssSel);
        self::assertSelectorTextContains($noticeCssSel, 'getfursu.it maintainer does not assist individuals');

        // 3rd option
        self::$client->findElement(WebDriverBy::cssSelector('input[value="Maker\'s website/social account is no longer working"]'))->click();
        self::waitUntilShows($buttonXpath);
        self::assertInvisible($noticeCssSel);

        // 2nd option
        self::$client->findElement(WebDriverBy::cssSelector('input[value="Maker\'s commissions info (open/closed) is inaccurate"]'))->click();
        self::waitUntilHides($buttonXpath);
        self::assertVisible($noticeCssSel);
        self::assertSelectorTextContains($noticeCssSel, 'This cannot be adjusted manually.');

        // 5th option
        self::$client->findElement(WebDriverBy::cssSelector('input[value="Other information on this website needs attention (not related to a particular maker)"]'))->click();
        self::waitUntilShows($buttonXpath);
        self::assertInvisible($noticeCssSel);

        // 4th option
        self::$client->findElement(WebDriverBy::cssSelector('input[value="Other maker\'s information is (partially) outdated"]'))->click();
        self::waitUntilHides($buttonXpath);
        self::assertVisible($noticeCssSel);
        self::assertSelectorTextContains($noticeCssSel, 'All the information needs to be updated by the makers themselves.');

        // 6th option
        self::$client->findElement(WebDriverBy::cssSelector('input[value="Report a technical problem/bug with this website"]'))->click();
        self::waitUntilShows($buttonXpath);
        self::assertInvisible($noticeCssSel);

        // 7th option, no visual change
        self::$client->findElement(WebDriverBy::cssSelector('input[value="Suggest an improvement to this website"]'))->click();
        self::assertVisible($buttonXpath);
        self::assertInvisible($noticeCssSel);

        // 8th option, no visual change
        self::$client->findElement(WebDriverBy::cssSelector('input[value="Other (please provide adequate details and context)"]'))->click();
        self::assertVisible($buttonXpath);
        self::assertInvisible($noticeCssSel);
    }
}
