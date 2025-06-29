<?php

declare(strict_types=1);

namespace App\Tests\Utils\Web\Snapshots;

use App\Tests\TestUtils\Cases\Traits\ContainerTrait;
use App\Tests\TestUtils\Cases\Traits\FilesystemTrait;
use App\Utils\DateTime\UtcClock;
use App\Utils\Web\Snapshots\Snapshot;
use App\Utils\Web\Snapshots\SnapshotMetadata;
use App\Utils\Web\Snapshots\SnapshotsSerializer;
use PHPUnit\Framework\Attributes\Medium;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;

#[Medium] // Use serializer from the container, I don't want to try instantiating one now
class SnapshotsSerializerTest extends KernelTestCase
{
    use ContainerTrait;
    use FilesystemTrait;

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

        $subject->save($this->testsTempDir, $input);
        $result = $subject->load($this->testsTempDir);

        self::assertNotSame($input, $result);
        self::assertEquals($input, $result);
    }
}
