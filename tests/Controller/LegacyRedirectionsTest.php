<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class LegacyRedirectionsTest extends WebTestCaseWithEM
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = self::createClient();
        $this->client->followRedirects();
    }

    /**
     * @dataProvider legacyRedirectionDataProvider
     */
    public function testLegacyRedirection(string $oldUri, string $checkedSelector, string $expectedText): void
    {
        $this->client->request('GET', $oldUri);

        static::assertResponseStatusCodeSame(200);
        static::assertSelectorTextContains($checkedSelector, $expectedText);
    }

    public function legacyRedirectionDataProvider(): array
    {
        return [
            '/index.html'       => ['/index.html', 'h4', 'Fursuit makers database'],
            '/new.html'         => ['/new.html', 'h1', 'Recently added makers/studios'],
            '/events.html'      => ['/events.html', 'p', 'See all recently added makers'],
            '/info.html'        => ['/info.html', 'h1', 'Information'],
            '/tracking.html'    => ['/tracking.html', 'h1', 'Automatic tracking and status updates'],
            '/maker_ids.html'   => ['/maker_ids.html', 'h1', 'What is a "maker ID"?'],
            '/donate.html'      => ['/donate.html', 'h1', 'TL;DR'],
            '/rules.html'       => ['/rules.html', 'h1', 'Rules for makers/studios'],
            '/should_know.html' => ['/should_know.html', 'h1', '"I want a fursuit NOW!"'],
            '/statistics.html'  => ['/statistics.html', 'h1', 'Commission status'],
        ];
    }
}
