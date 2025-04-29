<?php

declare(strict_types=1);

namespace App\Tests\TestUtils;

use App\Command\SubmissionsMigration\SubmissionData;
use App\IuHandling\Submission\SubmissionService;
use App\Utils\Creator\SmartAccessDecorator as Creator;
use App\Utils\DateTime\UtcClock;
use App\Utils\Json;

class Submissions
{
    public static function from(Creator $creator): SubmissionData
    {
        /**
         * @var array<string, psJsonFieldValue> $data
         */
        $data = Json::decode(SubmissionService::asJson($creator));

        return new SubmissionData(UtcClock::now(), 'MOCK SUBMISSION', $data);
    }
}
