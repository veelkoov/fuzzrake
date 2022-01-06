<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Form;

abstract class DbEnabledWebTestCase extends WebTestCase
{
    use DbEnabledTestCaseTrait;

    protected static function createClient(array $options = [], array $server = []): KernelBrowser
    {
        $result = parent::createClient($options, $server);

        self::resetDB();

        return $result;
    }

    protected static function assertEqualsIgnoringWhitespace(string $expectedHtml, string $actualHtml): void
    {
        $pattern = pattern('\s+');

        $expectedHtml = trim($pattern->replace($expectedHtml)->all()->with(' '));
        $actualHtml = trim($pattern->replace($actualHtml)->all()->with(' '));

        self::assertEquals($expectedHtml, $actualHtml);
    }

    protected static function submitValidForm(KernelBrowser $client, string $buttonName, array $formData): void
    {
        self::submitValid($client, $client->getCrawler()->selectButton($buttonName)->form($formData));
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
            $fields[] = $field->getAttribute('name');
        }

        self::fail('Form validation failed for: '.implode(', ', array_unique($fields)));
    }

    protected static function submitInvalidForm(KernelBrowser $client, string $buttonName, array $formData): void
    {
        self::submitInvalid($client, $client->getCrawler()->selectButton($buttonName)->form($formData));
    }

    protected static function submitInvalid(KernelBrowser $client, Form $form): void
    {
        $client->submit($form);

        self::assertResponseStatusCodeSame(422);
    }
}
