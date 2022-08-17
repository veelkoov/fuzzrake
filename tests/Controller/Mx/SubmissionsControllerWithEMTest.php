<?php

declare(strict_types=1);

namespace App\Tests\Controller\Mx;

use App\Tests\TestUtils\Cases\WebTestCaseWithEM;
use App\Tests\TestUtils\Submissions;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use JsonException;
use Symfony\Component\Uid\Uuid;

class SubmissionsControllerWithEMTest extends WebTestCaseWithEM
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

    /**
     * @throws JsonException
     */
    public function testLast20SubmissionsBeingShown(): void
    {
        $client = self::createClient();

        $this->generateRandomFakeSubmissions(19);

        $crawler = $client->request('GET', '/mx/submissions/');
        self::assertCount(19, $crawler->filter('table tbody tr'));

        $this->generateRandomFakeSubmissions(2);

        $crawler = $client->request('GET', '/mx/submissions/');
        self::assertCount(20, $crawler->filter('table tbody tr'));
    }

    /**
     * @throws JsonException
     */
    private function generateRandomFakeSubmissions(int $count): void
    {
        while (--$count >= 0) {
            $artisan = new Artisan();
            $artisan->setName(Uuid::v4()->toRfc4122());

            Submissions::submit($artisan);
        }
    }
}
