<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use Override;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @medium
 */
class CreatorUrlsControllerTest extends FuzzrakeWebTestCase
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

    public function testPageLoads(): void
    {
        $this->client->request('GET', '/mx/creator_urls/');

        static::assertResponseStatusCodeIs($this->client, 200);
    }
}
