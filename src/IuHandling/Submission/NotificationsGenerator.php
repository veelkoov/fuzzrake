<?php

declare(strict_types=1);

namespace App\IuHandling\Submission;

use App\DataDefinitions\Fields\Fields;
use App\Utils\Artisan\SmartAccessDecorator as Artisan;
use App\Utils\StrUtils;
use App\Utils\Traits\UtilityClass;
use App\ValueObject\Notification;

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
            if ($val = $data->get($url)) {
                $val = StrUtils::asStr($val);

                $message .= $url->name.': '.$val."\n";
            }
        }

        return new Notification(
            "IU submission: {$data->getName()}",
            $message,
        );
    }
}
