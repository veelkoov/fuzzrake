<?php

declare(strict_types=1);

namespace App\Tests\Utils\Data;

use App\Entity\Artisan;
use App\Utils\Data\Manager;
use App\Utils\DataInputException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class ManagerTest extends TestCase
{
    public function testCommentWithoutNewline(): void
    {
        $manager = new Manager('// Test comment without trailing \n');

        self::assertNotNull($manager); // Already passed
    }

    /**
     * @throws DataInputException
     */
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
}
