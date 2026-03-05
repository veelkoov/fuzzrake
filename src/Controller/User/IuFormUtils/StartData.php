<?php

declare(strict_types=1);

namespace App\Controller\User\IuFormUtils;

// No validation here. If you play with the scripts and something goes wrong, blame yourself.
class StartData
{
    public ?string $confirmAddingANewOne = null; // TODO: Remove
    public ?string $confirmUpdatingTheRightOne = null; // TODO: Remove
    public ?string $ensureStudioIsNotThereAlready = null; // TODO: Hmmmmmmmmmmmmm
    public ?string $confirmYouAreTheCreator = null; // TODO: Remove
    public ?string $confirmNoPendingUpdates = null;
    public ?string $decisionOverPreviousUpdates = null;
}
