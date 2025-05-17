<?php

declare(strict_types=1);

namespace App\IuHandling\Submission;

use App\Data\Definitions\Fields\Fields;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\StrUtils;
use App\Utils\Traits\UtilityClass;
use App\ValueObject\Messages\EmailNotificationV1;

final class NotificationsGenerator
{
    use UtilityClass;

    public static function getMessage(Creator $data, string $jsonData): EmailNotificationV1
    {
        $names = StrUtils::creatorNamesSafeForCli($data);

        $message = <<<MESSAGE
            {$names}
            From: {$data->getCountry()}
            
            MESSAGE;

        foreach (Fields::urls() as $urlField) {
            $url = $data->get($urlField);

            if ('' !== $url) {
                $url = StrUtils::asStr($url);

                $message .= $urlField->value.': '.$url."\n";
            }
        }

        return new EmailNotificationV1(
            "IU submission: {$data->getName()}",
            $message,
            attachedJsonData: $jsonData,
        );
    }
}
