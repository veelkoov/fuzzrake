<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use App\Tests\TestUtils\Submissions;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

abstract class AbstractTestWithEM extends WebTestCaseWithEM
{
    protected KernelBrowser $client;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'testing',
        ]);
    }

    protected function performImport(KernelBrowser $client, bool $acceptAll, int $expectedImports): void
    {
        $crawler = $this->client->request('GET', '/mx/submissions/1/');

        $links = $crawler->filter('table a')->links();

        self::assertCount($expectedImports, $links);

        foreach ($links as $link) {
            $crawler = $this->client->request('GET', $link->getUri());

            $form = $crawler->selectButton('Import')->form([
                'submission[directives]' => $acceptAll ? 'accept' : '',
            ]);

            $this->client->submit($form);
        }
    }
}
