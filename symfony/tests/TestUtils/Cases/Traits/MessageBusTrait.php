<?php

declare(strict_types=1);

namespace App\Tests\TestUtils\Cases\Traits;

use Veelkoov\Debris\Base\DList;
use Zenstruck\Messenger\Test\InteractsWithMessenger;
use Zenstruck\Messenger\Test\Transport\TransportEnvelopeCollection;

trait MessageBusTrait
{
    use InteractsWithMessenger;

    private function assertMessageBusQueueEmpty(): void
    {
        $this->getQueue()->assertEmpty();
    }

    private function getQueue(): TransportEnvelopeCollection
    {
        return $this->getTransport()->queue();
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $messageClass
     *
     * @return DList<T>
     */
    private function getQueued(string $messageClass): DList
    {
        return new DList($this->getQueue()->messages($messageClass));
    }

    private function clearQueue(): void
    {
        $this->getTransport()->reset();
    }

    private function getTransport(): \Zenstruck\Messenger\Test\Transport\TestTransport
    {
        return $this->transport('async-msg-queue');
    }
}
