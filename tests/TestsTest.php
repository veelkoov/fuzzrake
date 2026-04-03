<?php

declare(strict_types=1);

namespace App\Tests;

use App\Tests\TestUtils\Paths;
use Composer\Pcre\Preg;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

#[Small]
class TestsTest extends TestCase
{
    private static Filesystem $filesystem;

    #[Override]
    public static function setUpBeforeClass(): void
    {
        self::$filesystem = new Filesystem();
    }

    public static function byNamespaceDataProvider(): iterable
    {
        foreach (new Finder()->in(Paths::getTestsDirPath().'/ByNamespace/')->files() as $file) {
            yield [str_strip_prefix($file->getPathname(), Paths::getRepoTopDirPath().'/')];
        }
    }

    public static function allTestsDataProvider(): iterable
    {
        foreach (new Finder()->in(Paths::getTestsDirPath())->files() as $file) {
            $filePath = str_strip_prefix($file->getPathname(), Paths::getRepoTopDirPath().'/');

            if (!Preg::isMatch('#^tests/(console-application\.php$|object-manager\.php$|bootstrap\.php$|test_data/|TestUtils/)#', $filePath)) {
                yield [$filePath];
            }
        }
    }

    #[DataProvider('byNamespaceDataProvider')]
    public function testByNamespacePlacement(string $testPath): void
    {
        $srcClassPath = str_strip_prefix($testPath, 'tests/ByNamespace');
        $srcClassPath = Paths::getSrcDirPath().'/'.$srcClassPath;
        $srcClassPath = Preg::replace('#(Small|Medium|_[a-zA-Z]+)?Test\.php$#', '.php', $srcClassPath);

        self::assertFileExists($srcClassPath);
    }

    #[DataProvider('allTestsDataProvider')]
    public function testHaveSizeAnnotation(string $testPath): void
    {
        $contents = self::$filesystem->readFile(Paths::getRepoTopDirPath().'/'.$testPath);

        self::assertMatchesRegularExpression('/\n#\[Small|Medium|Large]\n/', $contents);
    }
}
