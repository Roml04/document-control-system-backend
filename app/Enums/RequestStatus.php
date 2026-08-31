<?php

namespace App\Enums;

enum RequestStatus: string
{
    case Coordinator = 'coordinator_approval'; 
    case Originator = 'originator_edit'; 
    case Superior = 'superior_approval'; 
    case Managers = 'managers_approval';
    case Approved = 'approved' ;
    case Denied = 'denied';
}
