<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case SUBMITTED = 'SUBMITTED';
    case INVOICED = 'INVOICED';
    case PAID = 'PAID';
    case EXPIRED = 'EXPIRED';
}
