<?php

declare(strict_types=1);

namespace App\Tests\E2E\IuSubmissions;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use Override;

abstract class IuSubmissionsTestCase extends FuzzrakeWebTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::haveAnAdminUser();
    }

    protected function performImports(int $expectedImports): void
    {
        self::loginAdminUser();
        $crawler = self::$client->request('GET', '/mx/submissions/1/');
        self::assertResponseIsSuccessful();

        $links = $crawler->filter('table a')->links();

        self::assertCount($expectedImports, $links);

        foreach ($links as $link) {
            $crawler = self::$client->request('GET', $link->getUri());
            self::assertResponseIsSuccessful();
            self::assertSelectorTextNotContains('p',
                'Matched multiple creators', // grep-code-matched-multiple-creators
                'A single creator must be matched.');

            $form = $crawler->selectButton('Import')->form([
                'submission[directives]' => 'accept',
            ]);

            self::submitValid($form);
        }
    }
}
