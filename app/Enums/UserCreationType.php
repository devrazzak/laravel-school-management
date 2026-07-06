<?php

namespace App\Enums;

enum UserCreationType: string
{
    case SelfRegistered = 'self_registered';
    case AdminCreation = 'admin_creation';
}
