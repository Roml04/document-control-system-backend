<?php

if(!function_exists('getNextStatus')) {

  function getNextStatus(string $reqType, string $status) {

    if($status === "approved") {
      throw new InvalidArgumentException(("Status is already approved"));
    }

    if($status === "coordinator_approval" && ($reqType === "upl" || $reqType === "del")) {
      return "superior_approval";
    }

    if($status === "superior_approval" && ($reqType === "del")) {
      return "approved";
    }

    $statusArr = [
      "coordinator_approval", 
      "originator_edit", 
      "superior_approval", 
      "managers_approval", 
      "approved", 
    ];

    $index = array_search($status, $statusArr);

    if($index === false) {
      throw new InvalidArgumentException("Invalid status: {$status}");
    }

    return $statusArr[$index + 1];
  }

}

if(!function_exists('formatFileName')) {
  function formatFileName(string $userFirstName, string $userLastName, string $fileExtension) {
      return strtolower("$userFirstName$userLastName") . "-" . now()->format('YmdHsu') . "." . $fileExtension;
  }
}