<?php

declare(strict_types=1);

namespace App\Service\Notifications;

use App\ValueObject\Notification;

interface MessengerInterface
{
    public function send(Notification $notification): bool;
}
