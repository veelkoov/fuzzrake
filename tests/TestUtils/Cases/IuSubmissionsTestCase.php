<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

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
        $crawler = self::$client->request('GET', '/submissions/1/');
        self::assertResponseIsSuccessful();

        $links = $crawler->filter('table a:nth-child(1)')->links();

        self::assertCount($expectedImports, $links, 'Expected submissions not matched.');

        foreach ($links as $link) {
            self::$client->request('GET', $link->getUri());
            self::assertResponseIsSuccessful();

            self::submitValidForm('Import', [
                'manage[directives]' => 'accept',
            ]);
        }
    }
}
