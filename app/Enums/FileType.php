<?php

namespace App\Enums;

enum FileType: string
{
    case Document = "document";
    case Checklist = "checklist";
    case Form = "form";
}
