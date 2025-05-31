<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class PagesControllerTest extends FuzzrakeWebTestCase
{
    /**
     * @param array<string, string> $texts
     */
    #[DataProvider('pageDataProvider')]
    public function testPage(string $uri, array $texts): void
    {
        self::$client->request('GET', $uri);
        self::assertResponseStatusCodeIs(200);

        foreach ($texts as $selector => $text) {
            self::assertSelectorTextContains($selector, $text);
        }
    }

    /**
     * @return array<string, array{string, array<string, string>}>
     */
    public static function pageDataProvider(): array
    {
        return [
            'contact' => ['/contact', [
                'h1' => 'Contact',
            ]],

            'info' => ['/info', [
                'h3#contact' => 'Contact maintainer',
                'h3#data-updates' => 'How to add/update your studio/maker info',
            ]],

            'tracking' => ['/tracking', [
                'h1' => 'Automated tracking and status updates',
            ]],

            'maker-ids' => ['/maker-ids', [
                'h1' => 'Fursuit makers IDs',
            ]],

            'donate' => ['/donate', [
                'h2' => 'Please donate',
            ]],

            'guidelines' => ['/guidelines', [
                'h1' => 'Guidelines for makers/studios',
            ]],

            'should-know' => ['/should-know', [
                'h1' => 'What you should know',
            ]],
        ];
    }

    public function testCaptchaWorksAndEmailAddressAppears(): void
    {
        self::$client->request('GET', '/contact');

        // E-mail address link is not visible by default
        self::assertSelectorNotExists('a[href^="mailto:"]');

        // Solve the captcha
        $form = self::$client->getCrawler()->selectButton('Reveal email address')->form([
            $this->getCaptchaFieldName('right') => 'right',
        ]);
        self::$client->submit($form);

        // The link should now contain the e-mail address
        self::assertSelectorExists('a[href^="mailto:"]');
    }
}
