<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller\Submissions;

use App\Data\Submission\Status;
use App\Entity\Post;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\Cases\Traits\MocksTrait;
use App\Tests\TestUtils\UserCreator;
use App\Utils\Enforce;
use PHPUnit\Framework\Attributes\Medium;

#[Medium]
class ReviewControllerTest extends FuzzrakeWebTestCase
{
    use MocksTrait;

    public function testReviewsAccess(): void
    {
        self::haveAnAdminUser();
        self::haveReviewerUsers();

        $creator = UserCreator::get();
        $user = $creator->entity->getUser();

        $submissionNew = $this->getEntityForSubmission($user, $creator, false);
        $submissionInReview = $this->getEntityForSubmission($user, $creator, false)->setStatus(Status::IN_REVIEW);
        self::persistAndFlush($user, $submissionNew, $submissionInReview);

        // Administrator can access reviews no matter what status the submission is
        self::loginAdminUser();
        self::$client->request('GET', $this->getReviewPath($submissionNew));
        self::assertResponseStatusCodeIs(200);
        self::$client->request('GET', $this->getReviewPath($submissionInReview));
        self::assertResponseStatusCodeIs(200);

        // Reviewer can access only reviews of submission in IN_REVIEW state
        self::loginReviewerUser();
        self::$client->request('GET', $this->getReviewPath($submissionNew));
        self::assertResponseStatusCodeIs(403);
        self::$client->request('GET', $this->getReviewPath($submissionInReview));
        self::assertResponseStatusCodeIs(200);
    }

    public function testVotingAccess(): void
    {
        self::haveAnAdminUser();
        self::haveReviewerUsers();

        $creator = UserCreator::get();
        $user = $creator->entity->getUser();

        $submission = $this->getEntityForSubmission($user, $creator, false);
        $adminPost = new Post(self::getAdminUser(), $submission);
        $reviewerPost = new Post(self::getReviewerUser(), $submission);
        self::persistAndFlush($user, $submission, $adminPost, $reviewerPost);

        self::loginAdminUser();
        self::$client->request('GET', $this->getVotePath($submission, $adminPost, true));
        self::assertResponseStatusCodeIs(403); // Cannot vote own posts
        self::$client->request('GET', $this->getVotePath($submission, $reviewerPost, true));
        self::assertResponseStatusCodeIs(302); // Can vote no matter of status of the submission

        self::loginReviewerUser();
        self::$client->request('GET', $this->getVotePath($submission, $adminPost, true));
        self::assertResponseStatusCodeIs(403); // Cannot vote - not IN_REVIEW
        self::$client->request('GET', $this->getVotePath($submission, $reviewerPost, true));
        self::assertResponseStatusCodeIs(403); // Cannot vote - not IN_REVIEW

        $this->changeSubmissionStatus(Enforce::int($submission->getId()), Status::IN_REVIEW);

        // Logged in as reviewer
        self::$client->request('GET', $this->getVotePath($submission, $adminPost, true));
        self::assertResponseStatusCodeIs(302);
        self::$client->request('GET', $this->getVotePath($submission, $reviewerPost, true));
        self::assertResponseStatusCodeIs(403); // Cannot vote own posts

        self::loginAdminUser();
        self::$client->request('GET', $this->getVotePath($submission, $adminPost, true));
        self::assertResponseStatusCodeIs(403); // Cannot vote own posts
        self::$client->request('GET', $this->getVotePath($submission, $reviewerPost, true));
        self::assertResponseStatusCodeIs(302);
    }

    public function testEditingAccess(): void
    {
        self::haveAnAdminUser();
        self::haveReviewerUsers();

        $creator = UserCreator::get();
        $user = $creator->entity->getUser();

        $submission = $this->getEntityForSubmission($user, $creator, false);
        $adminPost = new Post(self::getAdminUser(), $submission);
        $reviewerPost = new Post(self::getReviewerUser(), $submission);
        self::persistAndFlush($user, $submission, $adminPost, $reviewerPost);

        self::loginAdminUser();
        self::$client->request('GET', $this->getPostEditPath($adminPost));
        self::assertResponseStatusCodeIs(200); // Admin can edit own posts no matter of status of the submission
        self::$client->request('GET', $this->getPostEditPath($reviewerPost));
        self::assertResponseStatusCodeIs(403); // Admin cannot edit else's posts

        self::loginReviewerUser();
        self::$client->request('GET', $this->getPostEditPath($adminPost));
        self::assertResponseStatusCodeIs(403); // Reviewer cannot edit else's posts + submission not IN_REVIEW
        self::$client->request('GET', $this->getPostEditPath($reviewerPost));
        self::assertResponseStatusCodeIs(403); // Reviewer cannot edit own post - not IN_REVIEW

        $this->changeSubmissionStatus(Enforce::int($submission->getId()), Status::IN_REVIEW);

        // Logged in as reviewer
        self::$client->request('GET', $this->getPostEditPath($adminPost));
        self::assertResponseStatusCodeIs(403); // Reviewer cannot edit else's posts
        self::$client->request('GET', $this->getPostEditPath($reviewerPost));
        self::assertResponseStatusCodeIs(200);

        self::loginAdminUser();
        self::$client->request('GET', $this->getPostEditPath($adminPost));
        self::assertResponseStatusCodeIs(200);
        self::$client->request('GET', $this->getPostEditPath($reviewerPost));
        self::assertResponseStatusCodeIs(403); // Admin cannot edit else's posts
    }

    public function testPaginationWorksInSubmissions(): void
    {
        self::haveAnAdminUser();
        self::loginAdminUser();

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
