<?php 

namespace App\Enums;

enum VersionStatus: string {
  case PendingApproval = 'pending_approval';
  case Approved = 'approved';
}