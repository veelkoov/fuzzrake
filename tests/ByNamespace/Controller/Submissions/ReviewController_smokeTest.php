<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller\Submissions;

use App\Data\Submission\Status;
use App\Entity\Submission;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\Cases\Traits\MocksTrait;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use PHPUnit\Framework\Attributes\Medium;
use Symfony\Component\DomCrawler\Crawler;

#[Medium]
class ReviewController_smokeTest extends FuzzrakeWebTestCase
{
    use MocksTrait;

    public const string NEW_TOPIC_FORM_SELECTOR = 'div.discussion #new-topic-form';
    public const string TOPIC_CARDS_SELECTOR = 'div.discussion div.card.topic';

    /**
     * Tests:
     * - Reviewer cannot access non-IN_REVIEW submissions
     * - Everyone can post and reply
     * - You can't vote unless IN_REVIEW and not your post
     * - Posting, reading
     */
    public function testReviewControllerSmokeTest(): void
    {
        self::haveACreatorUser();
        self::haveReviewerUsers(2);
        self::haveAnAdminUser();

        $submission = self::getEntityForSubmission(self::getCreatorUser(), new Creator(), false);
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
        $this->startNewTopic('Topic 1 by admin text; topic 1 by admin text.');
        $this->startNewTopic('Topic 2 by admin text; topic 2 by admin text.');

        $this->validateContents([
            ['Topic 1 by admin text; topic 1 by admin text.', []],
            ['Topic 2 by admin text; topic 2 by admin text.', []],
        ]);

        // Change submission status to IN_REVIEW
        self::getEM()->getRepository(Submission::class)->find($submissionId)?->setStatus(Status::IN_REVIEW);
        self::flush();

        // Reviewers should now see the submission
        self::loginUser($reviewer1);
        self::$client->request('GET', $reviewPath);
        self::assertResponseStatusCodeIs(200);

        // Reviewers should be able to create new topics too
        $this->startNewTopic('Topic 3 by reviewer 1 text; topic 3 by reviewer 1 text.');

        $this->validateContents([
            ['Topic 1 by admin text; topic 1 by admin text.', []],
            ['Topic 2 by admin text; topic 2 by admin text.', []],
            ['Topic 3 by reviewer 1 text; topic 3 by reviewer 1 text.', []],
        ]);

        // Respond to admins' 1
        $this->respondToTopic(1, 'Response 1 by reviewer 1 to topic 1.');

        // Respond to self
        $this->respondToTopic(3, 'Response 1 by reviewer 1 to topic 3.');

        $this->validateContents([
            ['Topic 1 by admin text; topic 1 by admin text.', [
                'Response 1 by reviewer 1 to topic 1.',
            ]],
            ['Topic 2 by admin text; topic 2 by admin text.', []],
            ['Topic 3 by reviewer 1 text; topic 3 by reviewer 1 text.', [
                'Response 1 by reviewer 1 to topic 3.',
            ]],
        ]);

        // Post responses by reviewer 2
        self::loginUser($reviewer2);
        self::$client->request('GET', $reviewPath);
        self::assertResponseStatusCodeIs(200);

        // Respond to admins' 1
        $this->respondToTopic(1, 'Response 2 by reviewer 2 to topic 1.');

        // Respond to admins' 2
        $this->respondToTopic(2, 'Response 1 by reviewer 2 to topic 2.');

        // Respond to reviewer 1 topic 3
        $this->respondToTopic(3, 'Response 2 by reviewer 2 to topic 3.');

        $this->validateContents([
            ['Topic 1 by admin text; topic 1 by admin text.', [
                'Response 1 by reviewer 1 to topic 1.',
                'Response 2 by reviewer 2 to topic 1.',
            ]],
            ['Topic 2 by admin text; topic 2 by admin text.', [
                'Response 1 by reviewer 2 to topic 2.',
            ]],
            ['Topic 3 by reviewer 1 text; topic 3 by reviewer 1 text.', [
                'Response 1 by reviewer 1 to topic 3.',
                'Response 2 by reviewer 2 to topic 3.',
            ]],
        ]);
    }

    /**
     * @param list<array{string, list<string>}> $contentsData
     */
    private function validateContents(array $contentsData): void
    {
        $expectedTopicCount = count($contentsData);
        $topicCards = self::$client->getCrawler()->filter(self::TOPIC_CARDS_SELECTOR);
        self::assertCount($expectedTopicCount, $topicCards);

        foreach ($contentsData as $topicIndex => $topicData) {
            $topicCard = $topicCards->eq($topicIndex);

            $expectedTopicText = $topicData[0];
            $topicText = $topicCard->filter('.topic-text')->text();
            self::assertSame($expectedTopicText, $topicText);

            $expectedResponseCount = count($topicData[1]);
            $responseDivs = $topicCard->filter('div.response');
            self::assertCount($expectedResponseCount, $responseDivs);

            foreach ($topicData[1] as $responseIndex => $expectedResponseText) {
                $responseText = $responseDivs->eq($responseIndex)->filter('.response-text')->text();
                self::assertSame($expectedResponseText, $responseText);
            }

            // Make sure the response form for this topic is on this card and the message field ID is right
            $topicPostId = $this->postIdFrom($topicCard, 'topic');
            self::assertCount(1, $topicCard->filter("#topic_{$topicPostId}_message"));
        }

        // Make sure the new topic form exists and the field ID is right
        self::assertSelectorExists(self::NEW_TOPIC_FORM_SELECTOR.' #new_topic_message');
    }

    private function startNewTopic(string $topicText): void
    {
        $form = self::$client->getCrawler()->filter(self::NEW_TOPIC_FORM_SELECTOR)->selectButton('Post')->form([
            'new_topic[message]' => $topicText,
        ]);
        self::submitValid($form);
    }

    private function respondToTopic(int $topicNumber, string $responseText): void
    {
        $topicCard = self::$client->getCrawler()->filter(self::TOPIC_CARDS_SELECTOR)->eq($topicNumber - 1);
        $topicPostId = $this->postIdFrom($topicCard, 'topic');

        $form = $topicCard->selectButton('Respond')->form([
            "topic_{$topicPostId}[message]" => $responseText,
        ]);
        self::submitValid($form);
    }

    private function postIdFrom(Crawler $crawler, string $kind): int
    {
        self::assertCount(1, $crawler);

        $idAttr = $crawler->attr('id') ?? '';
        $idPrefix = $kind.'-';
        self::assertStringStartsWith($idPrefix, $idAttr);

        $postId = (int) str_strip_prefix($idAttr, $idPrefix);
        self::assertSame($idAttr, "$idPrefix$postId");

        return $postId;
    }
}
