<?php

declare(strict_types=1);

namespace App\Utils\Notifications;

interface MessengerInterface
{
    public function send(Notification $notification): bool;
}
