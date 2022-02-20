<?php

declare(strict_types=1);

namespace App\Tests\Utils\Data;

use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Data\Manager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class ManagerTest extends TestCase
{
    public function testCommentWithoutNewlineAtTheEndOfTheBuffer(): void
    {
        self::expectNotToPerformAssertions();

        new Manager('// Test comment without trailing \n');
    }

    public function testAcceptWithoutNewlineAtTheEndOfTheBuffer(): void
    {
        self::expectNotToPerformAssertions();

        new Manager('with MAKERID: accept');
    }

    public function testLoadingFromFile(): void
    {
        $filesystem = new Filesystem();
        $tmpFilePath = $filesystem->tempnam(sys_get_temp_dir(), 'import_manager');
        $filesystem->dumpFile($tmpFilePath, "with ABCDEFG:\n\tset URL_WEBSITE |https://example.com/|\n");

        $manager = Manager::createFromFile($tmpFilePath);

        $filesystem->remove($tmpFilePath);

        $artisan = (new Artisan())->setMakerId('ABCDEFG');
        $manager->correctArtisan($artisan);

        self::assertEquals('https://example.com/', $artisan->getWebsiteUrl());
    }

    public function testDetectingDelimiter(): void
    {
        $manager = new Manager('with ABCDEFG: replace NAME |abcdef| "fedcba"');

        $artisan = (new Artisan())->setMakerId('ABCDEFG')->setName('abcdef');

        $manager->correctArtisan($artisan);

        self::assertEquals('fedcba', $artisan->getName());
    }
}
