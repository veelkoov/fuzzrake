<?php

declare(strict_types=1);

namespace App\Event;

use App\Data\Definitions\Fields\Field;
use App\Entity\CreatorUrl;
use App\ValueObject\Messages\TrackCreatorsV1;
use App\ValueObject\Messages\UpdateMiniaturesV1;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Veelkoov\Debris\Lists\IntList;
use Veelkoov\Debris\Sets\StringSet;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', lazy: false, entity: CreatorUrl::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', lazy: false, entity: CreatorUrl::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', lazy: false, entity: CreatorUrl::class)]
class CreatorUrlListener
{
    private readonly StringSet $messagesSentToCreators;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
        $this->messagesSentToCreators = new StringSet();
    }

    private function wasSent(string $messageClass, int $creatorId): bool
    {
        return $this->messagesSentToCreators->contains("$creatorId\t$messageClass");
    }

    private function markSent(string $messageClass, int $creatorId): void
    {
        $this->messagesSentToCreators->add("$creatorId\t$messageClass");
    }

    /**
     * @throws ExceptionInterface
     */
    public function postPersist(CreatorUrl $entity, PostPersistEventArgs $event): void
    {
        $this->urlFieldChanged($entity);
    }

    /**
     * @throws ExceptionInterface
     */
    public function preUpdate(CreatorUrl $entity, PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField('url')) {
            $this->urlFieldChanged($entity);
        }
    }

    /**
     * @throws ExceptionInterface
     */
    public function preRemove(CreatorUrl $entity, PreRemoveEventArgs $event): void
    {
        $this->urlFieldChanged($entity);
    }

    /**
     * @throws ExceptionInterface
     */
    private function urlFieldChanged(CreatorUrl $creatorUrl): void
    {
        if ($creatorUrl->getType() === Field::URL_PHOTOS->value) {
            $this->photosUrlChanges($creatorUrl);
        }

        if ($creatorUrl->getType() === Field::URL_COMMISSIONS->value) {
            $this->trackingUrlChanged($creatorUrl);
        }
    }

    /**
     * @throws ExceptionInterface
     */
    private function photosUrlChanges(CreatorUrl $creatorUrl): void
    {
        $creatorId = (int) $creatorUrl->getCreator()->getId();

        if (!$this->wasSent(UpdateMiniaturesV1::class, $creatorId)) {
            $this->messageBus->dispatch(new UpdateMiniaturesV1($creatorId));
            $this->markSent(UpdateMiniaturesV1::class, $creatorId);
        }
    }

    /**
     * @throws ExceptionInterface
     */
    private function trackingUrlChanged(CreatorUrl $creatorUrl): void
    {
        $creatorId = (int) $creatorUrl->getCreator()->getId();

        if (!$this->wasSent(TrackCreatorsV1::class, $creatorId)) {
            $this->messageBus->dispatch(new TrackCreatorsV1(IntList::of($creatorId)));
            $this->markSent(TrackCreatorsV1::class, $creatorId);
        }
    }
}
