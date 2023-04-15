<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @medium
 */
class PagesControllerTest extends WebTestCase
{
    /**
     * @dataProvider pageDataProvider
     *
     * @param array<string, string> $texts
     */
    public function testPage(string $uri, array $texts): void
    {
        $client = self::createClient();

        $client->request('GET', $uri);

        self::assertEquals(200, $client->getResponse()->getStatusCode());

        foreach ($texts as $selector => $text) {
            self::assertSelectorTextContains($selector, $text);
        }
    }

    /**
     * @return array<string, array{string, array<string, string>}>
     */
    public function pageDataProvider(): array
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

            'rules' => ['/rules', [
                'h1' => 'Rules for makers/studios',
            ]],

            'should-know' => ['/should-know', [
                'h1' => 'What you should know',
            ]],
        ];
    }
}
