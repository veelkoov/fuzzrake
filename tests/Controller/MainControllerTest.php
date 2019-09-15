<?php

declare(strict_types=1);

namespace App\Tests\Controller;

class MainControllerTest extends DbEnabledWebTestCase
{
    public function testMain(): void
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertSelectorTextContains('h4', 'Fursuit makers database');
    }

    /**
     * @dataProvider redirectToIuFormDataProvider
     *
     * @param string $makerId
     * @param int    $responseCode
     */
    public function testRedirectToIuForm(string $makerId, int $responseCode): void
    {
        $client = static::createClient();
        self::addSimpleArtisan();

        $client->request('GET', '/redirect_iu_form/'.$makerId);
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function redirectToIuFormDataProvider(): array
    {
        return [
            ['TEST',    404],
            ['TEST002', 404],
            ['TEST001', 302],
        ];
    }
}
