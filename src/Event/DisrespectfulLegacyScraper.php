<?php

declare(strict_types=1);

namespace App\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class DisrespectfulLegacyScraper implements EventSubscriberInterface
{
    private const TYPEWRITERS_ARE_USED_ON_THE_INTERNET = false; // And BTW, screw 214
    private const USELESS_CRAP_FOLLOWED_BY_A_NEWLINE = "\r\n";

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

    private static function removeUselessCrapFromArray(array &$array): void
    {
        foreach ($array as $key => $value) {
            if (is_string($value)) {
                $array[$key] = str_replace(self::USELESS_CRAP_FOLLOWED_BY_A_NEWLINE, "\n", $value);
            } elseif (is_array($value)) {
                self::removeUselessCrapFromArray($array[$key]);
            }
        }
    }
}
