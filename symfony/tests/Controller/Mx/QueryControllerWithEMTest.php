<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @medium
 */
class QueryControllerWithEMTest extends WebTestCaseWithEM
{
    private KernelBrowser $client;

    #[Override]
    protected function setUp(): void
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'testing',
        ]);
    }

    public function testNewCreator(): void
    {
        $this->client->request('GET', '/mx/query/');

        static::assertEquals(200, $this->client->getResponse()->getStatusCode());

        $this->client->submitForm('Run', [
            'query[ITEM_QUERY]' => 'test',
        ]);

        static::assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
