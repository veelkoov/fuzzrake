<?php

declare(strict_types=1);

namespace App\Tests\BrowserBased;

use App\Tests\TestUtils\Cases\FuzzrakePantherTestCase;
use App\Tests\TestUtils\Cases\Traits\MainPageTestsTrait;
use App\Tests\TestUtils\UserCreator;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Large;

/**
 * @see \App\Tests\ByNamespace\Controller\FeedbackControllerTest
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
        $official = UserCreator::get()->setCountry('FI')->setName('Modern creator')
            ->setCreatorId('TEST001');

        $placeholder = UserCreator::get()->setCountry('CZ')->setName('Early creator')
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

        $this->loadMainPage(1, 1);
        $this->skipCheckListAdultAllowNsfw(1);

        $this->openCreatorCardByClickingOnTheHeader($creator->getLastCreatorId());
        self::$client->findElement(WebDriverBy::cssSelector("#$expectedCreatorId.creator-card .updates button"))->click();
        self::$client->clickLink('Submit the feedback form');

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
        self::assertSelectorTextContains($noticeCssSel, 'Data here is maintained and updated exclusively by the makers now.');

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
