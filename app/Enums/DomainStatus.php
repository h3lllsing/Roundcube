<?php

namespace App\Enums;

enum DomainStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Expired = 'expired';
}
