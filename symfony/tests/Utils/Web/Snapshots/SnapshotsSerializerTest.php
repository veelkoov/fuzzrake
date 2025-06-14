<?php

declare(strict_types=1);

namespace App\Tests\Utils\Web\Snapshots;

use App\Tests\TestUtils\Cases\FuzzrakeKernelTestCase;
use App\Utils\DateTime\UtcClock;
use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Snapshots\SnapshotMetadata;
use App\Utils\Web\Snapshots\SnapshotsSerializer;
use PHPUnit\Framework\Attributes\Medium;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;

#[Medium] // Use serializer from the container, I don't want to try instantiating one now
class SnapshotsSerializerTest extends FuzzrakeKernelTestCase
{
    public function testSavingAndLoading(): void
    {
        $subject = new SnapshotsSerializer(self::getContainerService(SerializerInterface::class));

        $input = new Snapshot('testing contents', new SnapshotMetadata(
            'testing URL',
            UtcClock::now(),
            555,
            ['Header1' => ['H1V1', 'H1V2'], 'Header2' => ['H2V1']],
            ['Error1', 'Error2', 'Error3'],
        ));

        $snapshotDirPath = $this->getTempDirForTestsUnsafe();

        $subject->save($snapshotDirPath, $input);
        $result = $subject->load($snapshotDirPath);

        self::assertNotSame($input, $result);
        self::assertEquals($input, $result);
    }

    private function getTempDirForTestsUnsafe(): string
    {
        $filesystem = new Filesystem();

        $result = $filesystem->tempnam(sys_get_temp_dir(), 'fuzzrake-tests-');
        $filesystem->remove($result);
        // Race condition; ignore; use this method only in tests.
        $filesystem->mkdir($result, 0700);

        return $result;
    }
}
