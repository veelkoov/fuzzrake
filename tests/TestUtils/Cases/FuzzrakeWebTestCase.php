<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases;

use App\Data\Submission\Status;
use App\Entity\Post;
use App\Entity\Submission;
use App\Tests\TestUtils\Cases\Traits\AssertsTrait;
use App\Tests\TestUtils\Cases\Traits\CacheTrait;
use App\Tests\TestUtils\Cases\Traits\CaptchaTrait;
use App\Tests\TestUtils\Cases\Traits\EntityManagerTrait;
use App\Tests\TestUtils\Cases\Traits\UsersTrait;
use App\Utils\Enforce;
use DOMElement;
use LogicException;
use Override;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;

abstract class FuzzrakeWebTestCase extends WebTestCase
{
    use AssertsTrait;
    use CacheTrait;
    use CaptchaTrait;
    use EntityManagerTrait;
    use UsersTrait;

    protected static KernelBrowser $client;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        self::$client = static::createClient();
        self::resetDB();
    }

    /**
     * Error output of the default makes result analysis difficult because the whole response is compared instead of just the code.
     *
     * @see BrowserKitAssertionsTrait::assertResponseStatusCodeSame()
     */
    public static function assertResponseStatusCodeIs(int $expectedCode): void
    {
        self::assertSame($expectedCode, self::$client->getInternalResponse()->getStatusCode(), 'Unexpected HTTP response status code');
    }

    /**
     * @param array<string, string> $formData
     */
    protected static function submitValidForm(string $buttonName, array $formData): void
    {
        $button = self::$client->getCrawler()->selectButton($buttonName);

        if (0 === $button->count()) {
            throw new RuntimeException("Button '$buttonName' has not been found.");
        }

        self::submitValid($button->form($formData));
    }

    protected static function submitValid(Form $form): void
    {
        $crawler = self::$client->submit($form);

        if (self::$client->getResponse()->isRedirect()) {
            // Not done above, so that we can do other assertions for failure case
            self::assertTrue(self::$client->getResponse()->isRedirect());
            self::$client->followRedirect();

            return;
        }

        self::assertLessThan(500, self::$client->getResponse()->getStatusCode(), 'Server returned 5XX');

        $fields = [];
        foreach ($crawler->filter('input.is-invalid') as $field) {
            if (!$field instanceof DOMElement) {
                throw new LogicException("Unexpected node type marked as invalid input: $field->nodeType");
            }

            $fields[] = $field->getAttribute('name');
        }

        self::fail('Form validation failed for: '.implode(', ', array_unique($fields)));
    }

    /**
     * @param array<string, string> $formData
     */
    protected static function submitInvalidForm(string $buttonName, array $formData): void
    {
        $button = self::$client->getCrawler()->selectButton($buttonName);

        if (0 === $button->count()) {
            throw new RuntimeException("Button '$buttonName' has not been found.");
        }

        self::submitInvalid($button->form($formData));
    }

    protected static function submitInvalid(Form $form): void
    {
        self::$client->submit($form);

        self::assertResponseStatusCodeIs(422);
    }

    protected function getReviewPath(Submission|int|null $submissionId): string
    {
        $submissionId = Enforce::int($submissionId instanceof Submission ? $submissionId->getId() : $submissionId);

        return "/submission/$submissionId/review";
    }

    protected function getVotePath(Submission|int|null $submissionId, Post|int|null $postId, bool $positive): string
    {
        $submissionId = Enforce::int($submissionId instanceof Submission ? $submissionId->getId() : $submissionId);
        $postId = Enforce::int($postId instanceof Post ? $postId->getId() : $postId);
        $positive = (int) $positive;

        return "/submission/$submissionId/vote-post/$postId/$positive";
    }

    protected function changeSubmissionStatus(int $submissionId, Status $status): void
    {
        self::getEM()->getRepository(Submission::class)->find($submissionId)?->setStatus($status);
        self::flush();
    }
}
