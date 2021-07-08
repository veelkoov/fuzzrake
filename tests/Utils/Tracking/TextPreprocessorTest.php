<?php

declare(strict_types=1);

namespace App\Tests\Utils\Tracking;

use App\Utils\Tracking\TextPreprocessor;
use PHPUnit\Framework\TestCase;

class TextPreprocessorTest extends TestCase
{
    public function testReplaceArtisanName(): void
    {
        self::assertEquals('An STUDIO_NAME work', TextPreprocessor::replaceArtisanName('Intergalactic House of Pancakes', 'An Intergalactic House of Pancakes work'));

        self::assertEquals('An STUDIO_NAME work', TextPreprocessor::replaceArtisanName('Intergalactic House of Pancakes', "An Intergalactic House of Pancake's work"));

        self::assertEquals("About STUDIO_NAME's work", TextPreprocessor::replaceArtisanName('Intergalactic Pancake', "About Intergalactic Pancake's work"));
    }

    public function testGuessFilterFromUrl(): void
    {
        self::assertEquals('guessFilter', TextPreprocessor::guessFilterFromUrl('AnyKindOfUrl#guessFilter'));
        self::assertEquals('', TextPreprocessor::guessFilterFromUrl('AnyKindOfUrl#'));
        self::assertEquals('', TextPreprocessor::guessFilterFromUrl('AnyKindOfUrl'));
    }
}
