<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller\Submissions;

use App\Data\Submission\Status;
use App\Entity\Submission;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\Cases\Traits\MocksTrait;
use App\Utils\Creator\SmartAccessDecorator;
use PHPUnit\Framework\Attributes\Medium;
use Symfony\Component\DomCrawler\Crawler;

#[Medium]
class ReviewController_smokeTest extends FuzzrakeWebTestCase
{
    use MocksTrait;

    public function testReviewControllerSmokeTest(): void
    {
        self::haveACreatorUser();
        self::haveReviewerUsers(2);
        self::haveAnAdminUser();

        $submission = self::getEntityForSubmission(self::getCreatorUser(), new SmartAccessDecorator(), false);
        self::persistAndFlush($submission);
        $submissionId = $submission->getId();
        unset($submission);
        $reviewPath = "/submission/{$submissionId}/review";

        $admin = self::getAdminUser();
        $reviewer1 = self::getReviewerUser(0);
        $reviewer2 = self::getReviewerUser(1);

        // Make sure reviewers cannot access the review which is not in the IN_REVIEW status
        self::loginUser($reviewer1);
        self::$client->request('GET', $reviewPath);
        self::assertResponseStatusCodeIs(403);

        // But an administrator can do that anytime
        self::loginUser($admin);
        self::$client->request('GET', $reviewPath);
        self::assertResponseStatusCodeIs(200);

        // Let the admin start 2 topics
        $newTopicFormCardSelector = '#new-topic-form';
        $form = self::$client->getCrawler()->filter($newTopicFormCardSelector)->selectButton('Post')->form([
            'new_topic[message]' => 'Topic 1 by admin text; topic 1 by admin text.',
        ]);
        self::submitValid($form);

        $form = self::$client->getCrawler()->filter($newTopicFormCardSelector)->selectButton('Post')->form([
            'new_topic[message]' => 'Topic 2 by admin text; topic 2 by admin text.',
        ]);
        self::submitValid($form);

        // Check number of topics, get their IDs
        $topicCards = self::$client->getCrawler()->filter('div.card.topic');
        self::assertCount(2, $topicCards);

        [$topic1CardSelector, $topic1PostId] = $this->selectorAndPostIdFrom($topicCards->eq(0), 'topic');
        [$topic2CardSelector, $topic2PostId] = $this->selectorAndPostIdFrom($topicCards->eq(1), 'topic');

        // Make sure dynamic forms are not mixed up
        self::assertSelectorExists("$newTopicFormCardSelector #new_topic_message");
        self::assertSelectorExists("$topic1CardSelector #topic_{$topic1PostId}_message");
        self::assertSelectorExists("$topic2CardSelector #topic_{$topic2PostId}_message");

        // Change submission status to IN_REVIEW
        self::getEM()->getRepository(Submission::class)->find($submissionId)?->setStatus(Status::IN_REVIEW);
        self::flush();

        // Reviewers should now see the submission
        self::loginUser($reviewer1);
        self::$client->request('GET', $reviewPath);
        self::assertResponseStatusCodeIs(200);

        // Reviewers should be able to create new topics too
        $form = self::$client->getCrawler()->filter($newTopicFormCardSelector)->selectButton('Post')->form([
            'new_topic[message]' => 'Topic 3 by reviewer 1 text; topic 3 by reviewer 1 text.',
        ]);
        self::submitValid($form);

        // Check ID of the new topic
        $topicCards = self::$client->getCrawler()->filter("div.card.topic:not($topic1CardSelector):not($topic2CardSelector)");
        self::assertCount(1, $topicCards);
        [$topic3CardSelector, $topic3PostId] = $this->selectorAndPostIdFrom($topicCards->eq(0), 'topic');

        // Respond to admins' 1
        $form = self::$client->getCrawler()->filter($topic1CardSelector)->selectButton('Respond')->form([
            "topic_{$topic1PostId}[message]" => 'Response 1 by reviewer 1 to topic 1.',
        ]);
        self::submitValid($form);

        // Respond to self
        $form = self::$client->getCrawler()->filter($topic3CardSelector)->selectButton('Respond')->form([
            "topic_{$topic3PostId}[message]" => 'Response 1 by reviewer 1 to topic 3.',
        ]);
        self::submitValid($form);

        // Post responses by reviewer 2
        self::loginUser($reviewer2);
        self::$client->request('GET', $reviewPath);
        self::assertResponseStatusCodeIs(200);

        // Respond to admins' 1
        $form = self::$client->getCrawler()->filter($topic1CardSelector)->selectButton('Respond')->form([
            "topic_{$topic1PostId}[message]" => 'Response 2 by reviewer 2 to topic 1.',
        ]);
        self::submitValid($form);

        // Respond to admins' 2
        $form = self::$client->getCrawler()->filter($topic2CardSelector)->selectButton('Respond')->form([
            "topic_{$topic2PostId}[message]" => 'Response 1 by reviewer 2 to topic 2.',
        ]);
        self::submitValid($form);

        // Respond to reviewer 1 topic 3
        $form = self::$client->getCrawler()->filter($topic3CardSelector)->selectButton('Respond')->form([
            "topic_{$topic3PostId}[message]" => 'Response 2 by reviewer 2 to topic 3.',
        ]);
        self::submitValid($form);

        // Verify generated contents

        $topics = self::$client->getCrawler()->filter('div.topic');
        self::assertCount(3, $topics);

        $responses = self::$client->getCrawler()->filter("$topic1CardSelector div.response");
        self::assertCount(2, $responses);
        [$topic1Response1Selector, $topic1Response1PostId] = $this->selectorAndPostIdFrom($responses->eq(0), 'response');
        [$topic1Response2Selector, $topic1Response2PostId] = $this->selectorAndPostIdFrom($responses->eq(1), 'response');

        $responses = self::$client->getCrawler()->filter("$topic2CardSelector div.response");
        self::assertCount(1, $responses);
        [$topic2Response1Selector, $topic1Response1PostId] = $this->selectorAndPostIdFrom($responses->eq(0), 'response');

        $responses = self::$client->getCrawler()->filter("$topic3CardSelector div.response");
        self::assertCount(2, $responses);
        [$topic3Response1Selector, $topic1Response1PostId] = $this->selectorAndPostIdFrom($responses->eq(0), 'response');
        [$topic3Response2Selector, $topic1Response2PostId] = $this->selectorAndPostIdFrom($responses->eq(1), 'response');
    }

    /**
     * @return array{string, int}
     */
    private function selectorAndPostIdFrom(Crawler $crawler, string $kind): array
    {
        self::assertCount(1, $crawler);

        $idAttr = $crawler->attr('id') ?? '';
        $idPrefix = $kind.'-';
        self::assertStringStartsWith($idPrefix, $idAttr);

        $postId = (int) str_strip_prefix($idAttr, $idPrefix);
        self::assertSame($idAttr, "$idPrefix$postId");

        return ["#$idAttr", $postId];
    }
}
