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

    /**
     * @return iterable<array{string}>
     */
    public static function allTestsRepoTopDirRelativeDataProvider(): iterable
    {
        foreach (new Finder()->in(Paths::getTestsDirPath())->files() as $file) {
            $filePath = str_strip_prefix($file->getPathname(), Paths::getRepoTopDirPath().'/');

            if (!Preg::isMatch('#^tests/(test_data/|TestUtils/|(bootstrap|console-application|object-manager)\.php$)#', $filePath)) {
                yield [$filePath];
            }
        }
    }

    /**
     * @return iterable<array{string}>
     */
    public static function byNamespaceTestsRepoTopDirRelativeDataProvider(): iterable
    {
        foreach (self::allTestsRepoTopDirRelativeDataProvider() as $filePathArray) {
            if (str_starts_with($filePathArray[0], 'tests/ByNamespace')) {
                yield $filePathArray;
            }
        }
    }

    #[DataProvider('byNamespaceTestsRepoTopDirRelativeDataProvider')]
    public function testByNamespacePlacement(string $testPath): void
    {
        $srcClassPath = str_strip_prefix($testPath, 'tests/ByNamespace/');
        $srcClassPath = Paths::getSrcDirPath().'/'.$srcClassPath;
        $srcClassPath = Preg::replace('#(Small|Medium|_[a-zA-Z]+)?Test\.php$#', '.php', $srcClassPath);

        self::assertFileExists($srcClassPath, "$testPath namespace/name does not match its class namespace/name.");
    }

    #[DataProvider('allTestsRepoTopDirRelativeDataProvider')]
    public function testHaveSizeAnnotation(string $testPath): void
    {
        $contents = self::$filesystem->readFile(Paths::getRepoTopDirPath().'/'.$testPath);

        self::assertTrue(
            Preg::isMatch('=\n#\[(Small|Medium|Large)]( +//[^\n]+)?\n=', $contents, $matches),
            "$testPath seems to be missing test size attribute.",
        );
    }
}
