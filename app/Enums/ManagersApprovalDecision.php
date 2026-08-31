<?php

namespace App\Enums;

enum ManagersApprovalDecision: string
{
    case Pending = "pending";
    case Approved = "approved";
    case Denied = "denied";
}
