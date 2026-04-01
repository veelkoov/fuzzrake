<?php

declare(strict_types=1);

namespace App\Security;

enum Role: string
{
    case ADMIN = 'ROLE_ADMIN';
    case CREATOR = 'ROLE_CREATOR';
    case REVIEWER = 'ROLE_REVIEWER';
    case VERIFIED = 'ROLE_VERIFIED';
}
