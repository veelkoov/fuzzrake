<?php

declare(strict_types=1);

namespace App\Tests\Utils\Web\WebpageSnapshot;

use App\Utils\DateTime\UtcClock;
use App\Utils\Web\WebpageSnapshot\Jar;
use App\Utils\Web\WebpageSnapshot\Snapshot;
use Exception;
use PHPUnit\Framework\TestCase;
use function Psl\Env\temp_dir;
use function Psl\Filesystem\delete_directory;
use Symfony\Component\Uid\Uuid;

class JarTest extends TestCase
{
    private string $dumpDirPath = '';

    protected function setUp(): void
    {
        $this->dumpDirPath = temp_dir().'/'.Uuid::v4()->toRfc4122();
    }

    protected function tearDown(): void
    {
        if ('' !== $this->dumpDirPath) {
            delete_directory($this->dumpDirPath, true);
        }
    }

    /**
     * @throws Exception
     */
    public function testDumpAndLoad(): void
    {
        $expected = new Snapshot(
            'contents', 'url',
            UtcClock::at('2022-07-05 12:34:56'),
            'owner name', 482,
            ['Content-type' => ['text/plain']], ['an error'],
        );

        $child1 = new Snapshot(
            'contents2', 'url2',
            UtcClock::at('2022-07-05 12:34:57'),
            'owner name2', 483,
            ['Content-type' => ['text/json']], ['an error2'],
        );

        $child2 = new Snapshot(
            'contents3', 'url3',
            UtcClock::at('2022-07-05 12:34:58'),
            'owner name3', 484,
            ['Content-type' => ['text/yaml']], ['an error3'],
        );

        $expected->addChild($child1);
        $expected->addChild($child2);

        Jar::dump($this->dumpDirPath, $expected);

        $actual = Jar::load($this->dumpDirPath);

        self::assertNotSame($expected, $actual);
        self::assertEquals($expected, $actual);

        self::assertCount(2, $actual->getChildren());

        self::assertNotSame($child1, $actual->getChildren()[0]);
        self::assertEquals($child1, $actual->getChildren()[0]);

        self::assertNotSame($child2, $actual->getChildren()[1]);
        self::assertEquals($child2, $actual->getChildren()[1]);
    }
}
