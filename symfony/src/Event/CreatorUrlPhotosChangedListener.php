<?php

declare(strict_types=1);

namespace App\Event;

use App\Data\Definitions\Fields\Field;
use App\Entity\CreatorUrl;
use App\ValueObject\Messages\UpdateMiniaturesV1;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use LogicException;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Veelkoov\Debris\IntSet;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', lazy: false, entity: CreatorUrl::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', lazy: false, entity: CreatorUrl::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', lazy: false, entity: CreatorUrl::class)]
class CreatorUrlPhotosChangedListener
{
    private readonly IntSet $messageSentForCreatorIds;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
        $this->messageSentForCreatorIds = new IntSet();
    }

    /**
     * @throws ExceptionInterface
     */
    public function postPersist(CreatorUrl $entity, PostPersistEventArgs $event): void
    {
        $this->urlChanged($entity);
    }

    /**
     * @throws ExceptionInterface
     */
    public function preUpdate(CreatorUrl $entity, PreUpdateEventArgs $event): void
    {
        if ($event->hasChangedField('url')) {
            $this->urlChanged($entity);
        }
    }

    /**
     * @throws ExceptionInterface
     */
    public function preRemove(CreatorUrl $entity, PreRemoveEventArgs $event): void
    {
        $this->urlChanged($entity);
    }

    /**
     * @throws ExceptionInterface
     */
    private function urlChanged(CreatorUrl $creatorUrl): void
    {
        if ($creatorUrl->getType() !== Field::URL_PHOTOS->value) {
            return;
        }

        $creatorId = $creatorUrl->getCreator()->getId();
        if (null === $creatorId) {
            throw new LogicException('Creator ID is null.');
        }

        if ($this->messageSentForCreatorIds->contains($creatorId)) {
            return;
        }

        $this->messageBus->dispatch(new UpdateMiniaturesV1($creatorId));
        $this->messageSentForCreatorIds->add($creatorId);
    }
}
