<?php

namespace App\Enums;

enum UserRole: string
{
    case Guest = 'guest';
    case Originator = 'originator';
    case Coordinator = 'coordinator';
    case Superior = 'superior';
    case Manager = 'manager';
    case SysAdmin = 'sysadmin';
}
