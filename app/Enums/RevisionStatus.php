<?php

namespace App\Enums;

enum RevisionStatus: string {
  case Pending = 'pending';
  case Approved = 'approved';
  case Denied = 'denied';
}