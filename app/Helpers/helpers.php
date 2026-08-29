<?php

if(!function_exists('updateStatus')) {

  function getNextStatus(string $reqType, string $status) {

    if($status === "approved") {
      throw new InvalidArgumentException(("Status is already approved"));
    }

    if($status === "coordinator_approval" && $reqType === "upl") {
      return "superior_approval";
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