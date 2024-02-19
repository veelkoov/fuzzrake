<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @medium
 */
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

        self::assertResponseStatusCodeIs($this->client, 200);
        self::assertSelectorTextContains($checkedSelector, $expectedText);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public function legacyRedirectionDataProvider(): array
    {
        return [
            '/index.html'        => ['/index.html', '#main-page-intro h4', 'Fursuit makers database'],
            '/new.html'          => ['/new.html', 'h1', 'Recently added makers/studios'],
            '/events.html'       => ['/events.html', 'p', 'See all recently added makers'],
            '/info.html'         => ['/info.html', 'h1', 'general information'],
            '/tracking.html'     => ['/tracking.html', 'h1', 'Automated tracking and status updates'],
            '/maker_ids.html'    => ['/maker_ids.html', 'h1', 'Fursuit makers IDs'],
            '/donate.html'       => ['/donate.html', 'h1', 'TL;DR'],
            '/rules.html'        => ['/rules.html', 'h1', 'Guidelines for makers/studios'],
            '/rules'             => ['/rules', 'h1', 'Guidelines for makers/studios'],
            '/should_know.html'  => ['/should_know.html', 'h1', 'What you should know'],
            '/statistics.html'   => ['/statistics.html', 'h1', 'Statistics'],
            '/data_updates.html' => ['/data_updates.html', 'h1', 'Inclusion/update request'],
        ];
    }
}
