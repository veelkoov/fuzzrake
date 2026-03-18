<?php

declare(strict_types=1);

namespace App\Data\Submission;

final class Filter
{
    public array $statuses = Status::ACTION_REQUIRED;
    public ?bool $update = null;
}
