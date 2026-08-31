<?php

namespace App\Enums;

enum VersionStatus: string
{
    case Pending = 'pending';
    case Published = 'published';
    case Rejected = 'rejected';
}
