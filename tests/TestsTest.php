<?php

declare(strict_types=1);

namespace App\Tests;

use App\Tests\TestUtils\Paths;
use Composer\Pcre\Preg;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

#[Small]
class TestsTest extends TestCase
{
    private const array TEST_SUFFIXES = [
        'SmallTest.php' => '.php',
        'MediumTest.php' => '.php',
        'Test.php' => '.php',
    ];

    public static function byNamespaceDataProvider(): iterable
    {
        $dir = self::getByNamespaceTestsDirPath().'/';

        foreach (new Finder()->in($dir)->files() as $file) {
            yield [str_strip_prefix($file->getPathname(), $dir)];
        }
    }

    #[DataProvider('byNamespaceDataProvider')]
    public function testByNamespace(string $testPath): void
    {
        //        $fullTestPath = self::getByNamespaceTestsDirPath().'/'.$testPath."\n";
        $srcClassPath = Preg::replace('#(Small|Medium|_[a-zA-Z]+)?Test\.php$#', '.php', Paths::getSrcDirPath().'/'.$testPath);

        self::assertFileExists($srcClassPath);
    }

    private static function getByNamespaceTestsDirPath(): string
    {
        return Paths::getTestsDirPath().'/ByNamespace';
    }
}
