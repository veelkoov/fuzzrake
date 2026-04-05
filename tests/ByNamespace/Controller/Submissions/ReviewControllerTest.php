<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller\Submissions;

use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\Cases\Traits\MocksTrait;
use App\Tests\TestUtils\UserCreator;
use Override;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class ReviewControllerTest extends FuzzrakeWebTestCase
{
    use MocksTrait;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::haveACreatorUser();
        self::haveAnAdminUser();
        self::loginAdminUser();
    }

    public function testPaginationWorksInSubmissions(): void
    {
        $this->generateRandomFakeInclusionSubmissions(24);

        $crawler = self::$client->request('GET', '/submissions/1/');
        self::assertResponseStatusCodeIs(200);
        self::assertCount(24, $crawler->filter('table tbody tr'));
        self::assertCount(3, $crawler->filter('ul.pagination li.page-item'));

        $this->generateRandomFakeInclusionSubmissions(2);

        $crawler = self::$client->request('GET', '/submissions/1/');
        self::assertResponseStatusCodeIs(200);
        self::assertCount(25, $crawler->filter('table tbody tr'));
        self::assertCount(4, $crawler->filter('ul.pagination li.page-item'));
    }

    private function generateRandomFakeInclusionSubmissions(int $count): void
    {
        while (--$count >= 0) {
            $creator = UserCreator::get();
            $user = $creator->entity->getUser();

            self::persist($user, $this->getEntityForSubmission($user, $creator, false));
        }

        self::flush();
    }
}
