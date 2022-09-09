<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use App\Tests\TestUtils\Submissions;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

abstract class AbstractTestWithEM extends WebTestCaseWithEM
{
    protected function setUp(): void
    {
        parent::setUp();

        Submissions::emptyTestSubmissionsDir();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Submissions::emptyTestSubmissionsDir();
    }

    protected function performImport(KernelBrowser $client, bool $acceptAll, int $expectedImports): void
    {
        $crawler = $client->request('GET', '/mx/submissions/');

        $links = $crawler->filter('table a')->links();

        self::assertCount($expectedImports, $links);

        foreach ($links as $link) {
            $crawler = $client->request('GET', $link->getUri());

            $form = $crawler->selectButton('Update')->form([
                'submission[directives]' => $acceptAll ? 'accept' : '',
            ]);

            $client->submit($form);
        }
    }
}
