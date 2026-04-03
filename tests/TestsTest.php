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
    public static function byNamespaceDataProvider(): iterable
    {
        foreach (new Finder()->in(Paths::getTestsDirPath().'/ByNamespace/')->files() as $file) {
            yield [str_strip_prefix($file->getPathname(), Paths::getRepoTopDirPath().'/')];
        }
    }

    #[DataProvider('byNamespaceDataProvider')]
    public function testByNamespace(string $testPath): void
    {
        $srcClassPath = str_strip_prefix($testPath, 'tests/ByNamespace');
        $srcClassPath = Paths::getSrcDirPath().'/'.$srcClassPath;
        $srcClassPath = Preg::replace('#(Small|Medium|_[a-zA-Z]+)?Test\.php$#', '.php', $srcClassPath);

        self::assertFileExists($srcClassPath);
    }
}
