<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Utils\Traits\UtilityClass;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;

class LegacyDisrespector implements EventSubscriberInterface
{
    use UtilityClass;

    private const USELESS_CRAP_FOLLOWED_BY_A_NEWLINE = "\r\n"; // And BTW, screw 214

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'typewritersSuck',
        ];
    }

    public static function typewritersSuck(Request $request): void
    {
        $inputs = $request->request->all();
        self::removeUselessCrap($inputs);
        $request->request->replace($inputs);
    }

    private static function removeUselessCrap(array &$inputs): void
    {
        foreach ($inputs as $key => $value) {
            if (is_string($value)) {
                $inputs[$key] = str_replace(self::USELESS_CRAP_FOLLOWED_BY_A_NEWLINE, "\n", $value);
            } elseif (is_array($value)) {
                self::removeUselessCrap($inputs[$key]);
            }
        }
    }
}
