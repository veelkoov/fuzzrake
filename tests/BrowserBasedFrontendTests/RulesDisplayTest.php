<?php

declare(strict_types=1);

namespace App\Tests\BrowserBasedFrontendTests;

use App\Tests\TestUtils\Cases\PantherTestCaseWithEM;
use Exception;
use Facebook\WebDriver\WebDriverBy;

/**
 * @large
 */
class RulesDisplayTest extends PantherTestCaseWithEM
{
    /**
     * Assures 7e6b8cca69c8c1abf36c01a36a27f69750d66bba will not happen in the future.
     *
     * @dataProvider togglingWorksDataProvider
     *
     * @throws Exception
     */
    public function testTogglingWorks(string $path, bool $skipIuForm): void
    {
        $client = self::createPantherClient();

        $crawler = $client->request('GET', $path);

        if ($skipIuForm) {
            // Initial animation
            self::waitUntilHides('#iu_form_ensureStudioIsNotThereAlready_0');

            $client->findElement(WebDriverBy::id('iu_form_confirmAddingANewOne_0'))->click();
            self::waitUntilShows('#iu_form_ensureStudioIsNotThereAlready_0');

            $client->findElement(WebDriverBy::id('iu_form_ensureStudioIsNotThereAlready_0'))->click();
            self::waitUntilShows('#iu_form_confirmYouAreTheMaker_0');

            $client->findElement(WebDriverBy::id('iu_form_confirmYouAreTheMaker_0'))->click();
            self::waitUntilShows('#iu_form_confirmNoPendingUpdates_0');

            $client->findElement(WebDriverBy::id('iu_form_confirmNoPendingUpdates_0'))->click();
            self::waitUntilShows('#rulesAndContinueButton');
        } else {
            // Initial animation
            self::waitUntilHides('#rulesChangelog');
        }

        $rules = ['pf', 'im', 'nl', 'wip', 'en', 'cs'];
        self::assertCount(count($rules), $crawler->filterXPath('//button[@type="button" and text()="More info"]'));

        foreach ($rules as $rule) {
            $button = $crawler->filterXPath('//button[@type="button" and text()="More info" and @data-bs-target="#rule-'.$rule.'"]');

            $button->click();
            self::assertVisible("#rule-$rule");
        }

        foreach ($rules as $rule) {
            $button = $crawler->filterXPath('//button[@type="button" and text()="More info" and @data-bs-target="#rule-'.$rule.'"]');
            $button->click();
            self::assertInvisible("#rule-$rule");
        }
    }

    /**
     * @return array<string, array{0: string, 1: bool}>
     */
    public function togglingWorksDataProvider(): array
    {
        return [
            '/rules'         => ['/rules',         false],
            '/iu_form/start' => ['/iu_form/start', true],
        ];
    }
}
