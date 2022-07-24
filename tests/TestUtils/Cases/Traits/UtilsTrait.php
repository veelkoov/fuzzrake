<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use App\Tests\TestUtils\Paths;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\Filesystem\Filesystem;

use function pattern;

trait UtilsTrait
{
    protected static function assertEqualsIgnoringWhitespace(string $expectedHtml, string $actualHtml): void
    {
        $pattern = pattern('\s+');

        $expectedHtml = trim($pattern->replace($expectedHtml)->all()->with(' '));
        $actualHtml = trim($pattern->replace($actualHtml)->all()->with(' '));

        self::assertEquals($expectedHtml, $actualHtml);
    }

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
            $client->followRedirect();

            return;
        }

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

        self::assertResponseStatusCodeSame(422);
    }

    protected function clearCache(): void
    {
        (new Filesystem())->remove(Paths::getCachePoolsDir());
    }
}
