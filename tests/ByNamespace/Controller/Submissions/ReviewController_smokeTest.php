<?php

declare(strict_types=1);

namespace App\Tests\ByNamespace\Controller\Submissions;

use App\Data\Submission\Status;
use App\Tests\TestUtils\Cases\FuzzrakeWebTestCase;
use App\Tests\TestUtils\Cases\Traits\MocksTrait;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\Enforce;
use PHPUnit\Framework\Attributes\Medium;
use Symfony\Component\DomCrawler\Crawler;

#[Medium]
class ReviewController_smokeTest extends FuzzrakeWebTestCase
{
    use MocksTrait;

    public const string NEW_TOPIC_FORM_SELECTOR = 'div.discussion #new-topic-form';
    public const string TOPIC_CARDS_SELECTOR = 'div.discussion div.card.topic';

    public function testReviewControllerSmokeTest(): void
    {
        self::haveAnAdminUser();
        self::haveACreatorUser();
        self::haveReviewerUsers(2);

        $reviewer1 = self::getReviewerUser(0);
        $reviewer2 = self::getReviewerUser(1);
        $submissionId = $this->setupSubmissionGetId();

        // Let the admin start 2 topics
        self::loginUser(self::getAdminUser());
        $this->requestReviewPage($submissionId);
        $this->startNewTopic('Topic 1 by admin text; topic 1 by admin text.');
        $this->startNewTopic('Topic 2 by admin text; topic 2 by admin text.');

        $this->validateContents($submissionId, [
            ['Topic 1 by admin text; topic 1 by admin text.', 0, 0, []],
            ['Topic 2 by admin text; topic 2 by admin text.', 0, 0, []],
        ]);

        $this->editTopic(2, 'Edited topic 2 by admin text; *edited* topic 2 by admin text.');

        $this->validateContents($submissionId, [
            ['Topic 1 by admin text; topic 1 by admin text.', 0, 0, []],
            ['Edited topic 2 by admin text; *edited* topic 2 by admin text.', 0, 0, []],
        ]);

        // Allow reviewers
        $this->changeSubmissionStatus($submissionId, Status::IN_REVIEW);

        // Reviewers should now see the submission
        self::loginUser($reviewer1);
        $this->requestReviewPage($submissionId);

        // Do some voting
        $this->voteTopic(1, true);
        $this->voteTopic(2, false);

        // Respond to admins' 1
        $this->respondToTopic(1, 'Response 1 by reviewer 1 to topic 1.');

        // Reviewers should be able to create new topics too
        $this->startNewTopic('Topic 3 by reviewer 1 text; topic 3 by reviewer 1 text.');

        // Respond to self
        $this->respondToTopic(3, 'Response 1 by reviewer 1 to topic 3.');

        $this->validateContents($submissionId, [
            ['Topic 1 by admin text; topic 1 by admin text.', 1, 0, [
                ['Response 1 by reviewer 1 to topic 1.', 0, 0],
            ]],
            ['Edited topic 2 by admin text; *edited* topic 2 by admin text.', 0, -1, []],
            ['Topic 3 by reviewer 1 text; topic 3 by reviewer 1 text.', 0, 0, [
                ['Response 1 by reviewer 1 to topic 3.', 0, 0],
            ]],
        ]);

        // Post responses by reviewer 2
        self::loginUser($reviewer2);
        $this->requestReviewPage($submissionId);

        // Respond to admins' 1
        $this->respondToTopic(1, 'Response 2 by reviewer 2 to topic 1.');

        // Respond to admins' 2
        $this->respondToTopic(2, 'Response 1 by reviewer 2 to topic 2.');

        // Respond to reviewer 1 topic 3
        $this->respondToTopic(3, 'Response 2 by reviewer 2 to topic 3.');
        $this->editResponse(3, 2, 'Response 2 by reviewer 2 to topic 3, after editing.');

        // Do some voting
        $this->voteTopic(1, true);
        $this->voteTopic(2, true);
        $this->voteTopic(3, false);
        $this->voteResponse(1, 1, false);
        $this->voteResponse(3, 1, false);

        $this->validateContents($submissionId, [
            ['Topic 1 by admin text; topic 1 by admin text.', 2, 0, [
                ['Response 1 by reviewer 1 to topic 1.', 0, -1],
                ['Response 2 by reviewer 2 to topic 1.', 0, 0],
            ]],
            ['Edited topic 2 by admin text; *edited* topic 2 by admin text.', 1, -1, [
                ['Response 1 by reviewer 2 to topic 2.', 0, 0],
            ]],
            ['Topic 3 by reviewer 1 text; topic 3 by reviewer 1 text.', 0, -1, [
                ['Response 1 by reviewer 1 to topic 3.', 0, -1],
                ['Response 2 by reviewer 2 to topic 3, after editing.', 0, 0],
            ]],
        ]);

        // Block reviewers
        $this->changeSubmissionStatus($submissionId, Status::OTHER);

        // But an administrator obviously can
        self::loginUser(self::getAdminUser());
        $this->requestReviewPage($submissionId);

        // Admin votes here as well
        $this->voteTopic(3, false);
        $this->voteResponse(1, 1, false);
        $this->voteResponse(1, 2, true);
        $this->voteResponse(3, 2, true);

        $this->validateContents($submissionId, [
            ['Topic 1 by admin text; topic 1 by admin text.', 2, 0, [
                ['Response 1 by reviewer 1 to topic 1.', 0, -2],
                ['Response 2 by reviewer 2 to topic 1.', 1, 0],
            ]],
            ['Edited topic 2 by admin text; *edited* topic 2 by admin text.', 1, -1, [
                ['Response 1 by reviewer 2 to topic 2.', 0, 0],
            ]],
            ['Topic 3 by reviewer 1 text; topic 3 by reviewer 1 text.', 0, -2, [
                ['Response 1 by reviewer 1 to topic 3.', 0, -1],
                ['Response 2 by reviewer 2 to topic 3, after editing.', 1, 0],
            ]],
        ]);
    }

    /**
     * @param list<array{string, int, int, list<array{string, int, int}>}> $contentsData
     */
    private function validateContents(int $submissionId, array $contentsData): void
    {
        // Verify number of topics
        $expectedTopicCount = count($contentsData);
        $topicCards = self::$client->getCrawler()->filter(self::TOPIC_CARDS_SELECTOR);
        self::assertCount($expectedTopicCount, $topicCards);

        foreach ($contentsData as $topicIndex => $topicData) {
            $expectedTopicText = $topicData[0];
            $expectedUpvotes = $topicData[1];
            $expectedDownvotes = -$topicData[2];

            $topicCard = $topicCards->eq($topicIndex);
            $topicPostId = $this->postIdFrom($topicCard); // For form and links validation

            // Verify topic text
            $topicText = $topicCard->filter('.topic-text')->text();
            self::assertSame($expectedTopicText, $topicText);

            // Verify topic votes
            $this->verifyVotes($topicCard->filter('.topic-header'), $submissionId, $topicPostId,
                $expectedUpvotes, $expectedDownvotes);

            // Verify number of responses
            $expectedResponseCount = count($topicData[3]);
            $responseDivs = $topicCard->filter('div.response');
            self::assertCount($expectedResponseCount, $responseDivs);

            foreach ($topicData[3] as $responseIndex => $responseData) {
                $expectedResponseText = $responseData[0];
                $expectedUpvotes = $responseData[1];
                $expectedDownvotes = -$responseData[2];

                $responseDiv = $responseDivs->eq($responseIndex);
                $responsePostId = $this->postIdFrom($responseDiv); // For links validation

                // Verify response text
                $responseText = $responseDiv->filter('.response-text')->text();
                self::assertSame($expectedResponseText, $responseText);

                // Verify response votes
                $this->verifyVotes($responseDiv->filter('.response-header'), $submissionId, $responsePostId,
                    $expectedUpvotes, $expectedDownvotes);
            }

            // Make sure the response form for this topic is on this card and the message field ID is right
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

    private function voteTopic(int $topicNumber, bool $positive): void
    {
        $this->vote($this->getTopicHeader($topicNumber), $positive);
    }

    private function voteResponse(int $topicNumber, int $responseNumber, bool $positive): void
    {
        $this->vote($this->getResponseHeader($topicNumber, $responseNumber), $positive);
    }

    private function vote(Crawler $crawler, bool $positive): void
    {
        self::$client->click($crawler->filter($positive ? '.upvotes a' : '.downvotes a')->link());
        self::$client->followRedirect();
    }

    private function respondToTopic(int $topicNumber, string $responseText): void
    {
        $topicCard = $this->getTopicCard($topicNumber);
        $topicPostId = $this->postIdFrom($topicCard);

        $form = $topicCard->selectButton('Send')->form([
            "topic_{$topicPostId}[message]" => $responseText,
        ]);
        self::submitValid($form);
    }

    private function editTopic(int $topicNumber, string $newMessage): void
    {
        self::$client->click($this->getTopicHeader($topicNumber)->filter('a.edit-post')->link());

        self::submitValidForm('Update', [
            'post[message]' => $newMessage,
        ]);
    }

    private function editResponse(int $topicNumber, int $responseNumber, string $newMessage): void
    {
        self::$client->click($this->getResponseHeader($topicNumber, $responseNumber)->filter('a.edit-post')->link());

        self::submitValidForm('Update', [
            'post[message]' => $newMessage,
        ]);
    }

    private function postIdFrom(Crawler $crawler): int // grep-code-post-id-anchor
    {
        self::assertCount(1, $crawler);

        $idAttr = $crawler->attr('id') ?? '';
        $postId = (int) str_strip_prefix($idAttr, 'post-');
        self::assertSame($idAttr, "post-$postId");

        return $postId;
    }

    private function requestReviewPage(int $submissionId): void
    {
        self::$client->request('GET', $this->getReviewPath($submissionId));
        self::assertResponseStatusCodeIs(200);
    }

    private function setupSubmissionGetId(): int
    {
        $submission = self::getEntityForSubmission(self::getCreatorUser(), new Creator(), false);
        self::persistAndFlush($submission);

        return Enforce::int($submission->getId());
    }

    private function getTopicCard(int $topicNumber): Crawler
    {
        return self::$client->getCrawler()->filter(self::TOPIC_CARDS_SELECTOR)->eq($topicNumber - 1);
    }

    private function verifyVotes(Crawler $header, int $submissionId, int $postId, int $expectedUpvotes, int $expectedDownvotes): void
    {
        $expectedUpvotes = 0 === $expectedUpvotes ? '' : "+$expectedUpvotes";
        $expectedDownvotes = 0 === $expectedDownvotes ? '' : "-$expectedDownvotes";
        self::assertSame($expectedUpvotes, $header->filter('.upvotes')->text());
        self::assertSame($expectedDownvotes, $header->filter('.downvotes')->text());

        $votingHrefs = $header->filter('.upvotes a, .downvotes a')->extract(['href']);
        $expectedOptionalVotingHrefs = [[], [
            $this->getVotePath($submissionId, $postId, true),
            $this->getVotePath($submissionId, $postId, false),
        ]];

        self::assertContains($votingHrefs, $expectedOptionalVotingHrefs);
    }

    private function getTopicHeader(int $topicNumber): Crawler
    {
        return $this->getTopicCard($topicNumber)->filter('.topic-header');
    }

    private function getResponseHeader(int $topicNumber, int $responseNumber): Crawler
    {
        return $this->getTopicCard($topicNumber)->filter('div.response')
            ->eq($responseNumber - 1)->filter('.response-header');
    }
}
