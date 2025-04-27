<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use RuntimeException;
use Symfony\Component\DomCrawler\Form;

trait FormsTrait
{
    use AssertsTrait;

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
            $fields[] = $field->getAttribute('name'); // @phpstan-ignore-line DOMNode::getAttribute() is defined
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
}
