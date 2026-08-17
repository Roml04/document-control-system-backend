<?php

namespace App\Enums;

enum VersionStatus: string
{
    case Pending = 'pending_approval';
    case Approved = 'approved';
}
