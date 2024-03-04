<?php

declare(strict_types=1);

namespace App\Controller\IuForm\Utils;

// No validation here. If you play with the scripts and something goes wrong, blame yourself.
class StartData
{
    public ?string $confirmAddingANewOne = null;
    public ?string $confirmUpdatingTheRightOne = null;
    public ?string $ensureStudioIsNotThereAlready = null;
    public ?string $confirmYouAreTheMaker = null;
    public ?string $confirmNoPendingUpdates = null;
    public ?string $decisionOverPreviousUpdates = null;
}
