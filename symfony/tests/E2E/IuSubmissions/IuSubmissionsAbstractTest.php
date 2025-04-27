<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

abstract class IuSubmissionsAbstractTest extends FuzzrakeWebTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::$client->setServerParameters([
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'testing',
        ]);
    }

    protected function performImport(KernelBrowser $client, bool $acceptAll, int $expectedImports): void
    {
        $crawler = self::$client->request('GET', '/mx/submissions/1/');

        $links = $crawler->filter('table a')->links();

        self::assertCount($expectedImports, $links);

        foreach ($links as $link) {
            $crawler = self::$client->request('GET', $link->getUri());

            $form = $crawler->selectButton('Import')->form([
                'submission[directives]' => $acceptAll ? 'accept' : '',
            ]);

            self::$client->submit($form);
        }
    }
}
