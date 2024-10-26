<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Tests\TestUtils\Paths;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\Filesystem\Filesystem;

trait UtilsTrait
{
    use AssertsTrait;

    /**
     * @param array<string, string> $formData
     */
    protected static function submitValidForm(KernelBrowser $client, string $buttonName, array $formData): void
    {
        $button = $client->getCrawler()->selectButton($buttonName);

        if (0 === $button->count()) {
            throw new RuntimeException("Button '$buttonName' has not been found.");
        }

        self::submitValid($client, $button->form($formData));
    }

    protected static function submitValid(KernelBrowser $client, Form $form): void
    {
        $crawler = $client->submit($form);

        if ($client->getResponse()->isRedirect()) {
            // Not done above, so that we can do other assertions for failure case
            self::assertTrue($client->getResponse()->isRedirect());
            $client->followRedirect();

            return;
        }

        self::assertLessThan(500, $client->getResponse()->getStatusCode(), 'Server returned 5XX');

        $fields = [];
        foreach ($crawler->filter('input.is-invalid') as $field) {
            $fields[] = $field->getAttribute('name'); // @phpstan-ignore-line DOMNode::getAttribute() is defined
        }

        self::fail('Form validation failed for: '.implode(', ', array_unique($fields)));
    }

    /**
     * @param array<string, string> $formData
     */
    protected static function submitInvalidForm(KernelBrowser $client, string $buttonName, array $formData): void
    {
        $button = $client->getCrawler()->selectButton($buttonName);

        if (0 === $button->count()) {
            throw new RuntimeException("Button '$buttonName' has not been found.");
        }

        self::submitInvalid($client, $button->form($formData));
    }

    protected static function submitInvalid(KernelBrowser $client, Form $form): void
    {
        $client->submit($form);

        self::assertResponseStatusCodeIs($client, 422);
    }

    protected function clearCache(): void
    {
        (new Filesystem())->remove(Paths::getCachePoolsDir());
    }
}
