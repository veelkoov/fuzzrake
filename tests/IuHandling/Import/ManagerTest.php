<?php

declare(strict_types=1);

namespace App\Tests\IuHandling\Import;

use App\IuHandling\Import\Manager;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class ManagerTest extends TestCase
{
    public function testCommentWithoutNewlineAtTheEndOfTheBuffer(): void
    {
        self::expectNotToPerformAssertions();

        new Manager($this->createMock(LoggerInterface::class), '// Test comment without trailing \n');
    }

    public function testAcceptWithoutNewlineAtTheEndOfTheBuffer(): void
    {
        self::expectNotToPerformAssertions();

        new Manager($this->createMock(LoggerInterface::class), 'with MAKERID: accept');
    }

    public function testLoadingFromFile(): void
    {
        $filesystem = new Filesystem();
        $tmpFilePath = $filesystem->tempnam(sys_get_temp_dir(), 'import_manager');
        $filesystem->dumpFile($tmpFilePath, "with ABCDEFG:\n\tset URL_WEBSITE |https://example.com/|\n");

        $manager = new Manager($this->createMock(LoggerInterface::class), directivesFilePath: $tmpFilePath);

        $filesystem->remove($tmpFilePath);

        $artisan = (new Artisan())->setMakerId('ABCDEFG');
        $manager->correctArtisan($artisan);

        self::assertEquals('https://example.com/', $artisan->getWebsiteUrl());
    }

    public function testDetectingDelimiter(): void
    {
        $manager = new Manager($this->createMock(LoggerInterface::class), 'with ABCDEFG: replace NAME |abcdef| "fedcba"');

        $artisan = (new Artisan())->setMakerId('ABCDEFG')->setName('abcdef');

        $manager->correctArtisan($artisan);

        self::assertEquals('fedcba', $artisan->getName());
    }
}
