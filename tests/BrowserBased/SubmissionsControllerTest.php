<?php

declare(strict_types=1);

namespace App\Tests\BrowserBased;

use App\Tests\TestUtils\Cases\FuzzrakePantherTestCase;
use App\Tests\TestUtils\Cases\Traits\MocksTrait;
use App\Tests\TestUtils\UserCreator;
use App\Utils\Exceptions\UncheckedException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use PHPUnit\Framework\Attributes\Large;

#[Large]
class SubmissionsControllerTest extends FuzzrakePantherTestCase
{
    use MocksTrait;

    public function testSubmissionManagementSmokeTest(): void
    {
        self::haveAnAdminUser();

        $creator = UserCreator::get()->setCreatorId('TEST001')->setCity('Some old city');

        $submissionData = $creator->copy()->setCity('New city')->setInstagramUrl('www.instagram.com/getfursu.it');
        $submission = self::getEntityForSubmission($creator->getUser(), $submissionData, true);

        self::persistAndFlush($creator, $submission);

        self::loginAdminUser();
        self::$client->get($this->getManagePath($submission));

        self::assertDirectives(''); // We are starting with empty directives; sanity check

        // Test button fixing submitted field value
        self::$client->getCrawler()->filter('.URL_INSTAGRAM.submitted .fix-button')->click();
        self::assertDirectives("set URL_INSTAGRAM ◇www.instagram.com/getfursu.it◇\n");

        self::clearDirectives();

        // Test button fixing auto-fixed field value
        self::$client->getCrawler()->filter('.URL_INSTAGRAM.after .fix-button')->click();
        self::assertDirectives("set URL_INSTAGRAM ◇https://www.instagram.com/getfursu.it/◇\n");

        self::clearDirectives();

        // Test button clearing field value
        self::$client->getCrawler()->filter('.URL_INSTAGRAM.after .clear-button')->click();
        self::assertDirectives("clear URL_INSTAGRAM\n");

        self::clearDirectives();

        // Test appending new directives
        self::getDirectivesElement()->sendKeys("\n\ntesting");
        self::$client->getCrawler()->filter('.URL_INSTAGRAM.after .clear-button')->click();
        self::assertDirectives("testing\nclear URL_INSTAGRAM\n");
    }

    private static function assertDirectives(string $expected): void
    {
        self::assertSame($expected,
            self::$client->getCrawler()->filter('#manage_directives')->getAttribute('value'));
    }

    private static function clearDirectives(): void
    {
        self::getDirectivesElement()->clear();
    }

    private static function getDirectivesElement(): WebDriverElement
    {
        try {
            return self::$client->findElement(WebDriverBy::id('manage_directives'));
        } catch (NoSuchElementException $exception) {
            throw new UncheckedException($exception);
        }
    }
}
