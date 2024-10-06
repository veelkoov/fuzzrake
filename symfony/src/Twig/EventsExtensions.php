<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Event;
use InvalidArgumentException;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EventsExtensions extends AbstractExtension
{
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('event_description', $this->eventDescriptionFunction(...)),
        ];
    }

    public function eventDescriptionFunction(Event $event): string
    {
        if (Event::TYPE_DATA_UPDATED !== $event->getType()) {
            throw new InvalidArgumentException('Only '.Event::TYPE_DATA_UPDATED.' event type is supported by '.__FUNCTION__);
        }

        $n = $event->getNewMakersCount();
        $u = $event->getUpdatedMakersCount();
        $r = $event->getReportedUpdatedMakersCount();

        $result = '';

        if ($n) {
            $s = $n > 1 ? 's' : '';
            $result .= "$n new maker$s";
        }

        if ($n && $u) {
            $result .= ' and ';
        }

        if ($u) {
            $s = $u > 1 ? 's' : '';
            $result .= "$u updated maker$s";
        }

        if ($n || $u) {
            $s = $n + $u > 1 ? 's' : '';
            $result .= " based on received I/U request$s.";
        }

        if ($r) {
            $s = $r > 1 ? 's' : '';
            $result .= " $r maker$s updated after report$s sent by a visitor(s). Thank you for your contribution!";
        }

        return trim($result);
    }
}
