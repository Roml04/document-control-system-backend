<?php

namespace App\Enums;

enum RequestType: string
{
    case Upload = 'upl';
    case Revision = 'rev';
    case Resub = 'resub';
}
