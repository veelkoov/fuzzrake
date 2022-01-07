<?php

declare(strict_types=1);

namespace App\Utils\IuSubmissions;

use App\DataDefinitions\Fields\Fields;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\Notifications\Notification;
use App\Utils\StrUtils;
use App\Utils\Traits\UtilityClass;

final class NotificationsGenerator
{
    use UtilityClass;

    public static function getMessage(Artisan $data, bool $s3SendingOk): Notification
    {
        $optionalWarning = $s3SendingOk ? '' : "WARNING: S3 sending failed!\n\n";

        $names = StrUtils::artisanNamesSafeForCli($data);

        $message = <<<MESSAGE
            {$optionalWarning}{$names}
            From: {$data->getCountry()}
            
            MESSAGE;

        foreach (Fields::urls() as $url) {
            if (($val = $data->get($url))) {
                $message .= $url->name.': '.$val."\n";
            }
        }

        return new Notification(
            "IU submission: {$data->getName()}",
            $message,
        );
    }
}
