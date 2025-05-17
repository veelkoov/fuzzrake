<?php

declare(strict_types=1);

namespace App\Event;

use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DisrespectfulLegacyScraper implements EventSubscriberInterface
{
    private const bool TYPEWRITERS_ARE_USED_ON_THE_INTERNET = false; // And BTW, screw 214
    private const string USELESS_CRAP_FOLLOWED_BY_A_NEWLINE = "\r\n";

    #[Override]
    public static function getSubscribedEvents(): array
    {
        // @phpstan-ignore-next-line - Rhetorical question
        return self::TYPEWRITERS_ARE_USED_ON_THE_INTERNET ? [] : [KernelEvents::REQUEST => 'removeUselessCrapFromRequest'];
    }

    public static function removeUselessCrapFromRequest(RequestEvent $requestEvent): void
    {
        $request = $requestEvent->getRequest();

        if ($request->isMethod(Request::METHOD_POST)) {
            $inputs = $request->request->all();
            self::removeUselessCrapFromArray($inputs);
            $request->request->replace($inputs);
        }
    }

    /**
     * @param array<mixed> $array
     */
    private static function removeUselessCrapFromArray(array &$array): void
    {
        foreach ($array as &$value) {
            if (is_string($value)) {
                $value = str_replace(self::USELESS_CRAP_FOLLOWED_BY_A_NEWLINE, "\n", $value);
            } elseif (is_array($value)) {
                self::removeUselessCrapFromArray($value);
            }
        }
    }
}
