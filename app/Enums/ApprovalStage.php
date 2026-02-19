<?php

namespace App\Enums;

enum ApprovalStage: string {
  case CoordinatorApproval = 'coordinator_approval';
  case SuperiorApproval = 'superior_approval';
}