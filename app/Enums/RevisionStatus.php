<?php

namespace App\Enums;

enum RevisionStatus: string
{
    case CoordinatorApproval = 'coordinator_approval';
    case SuperiorApproval = 'superior_approval';
    case OriginatorEdit = 'originator_edit';
    case Approved = 'approved';
    case Denied = 'denied';
}
