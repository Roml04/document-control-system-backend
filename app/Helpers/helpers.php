<?php

if(!function_exists('getNextStatus')) {

  function getNextStatus(string $reqType, string $status, ?bool $wasEdited) {

    if($status === "approved") {
      throw new InvalidArgumentException(("Status is already approved"));
    }

    if($reqType === "rev" && $status === "coordinator_approval" && $wasEdited) {
      return "superior_approval";
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
      return strtolower("$userFirstName$userLastName") . "-" . now()->format('YmdHisu') . "." . $fileExtension;
  }
}

if(!function_exists('formatRequestType')) {
  function formatRequestType(string $status) {
    switch($status) {
      case "upl":
        return "upload";

      case "rev":
        return "revision";

      case "resub":
        return "resubmit";

      case "del":
        return "delete";

      default:
        throw new \InvalidArgumentException("Unknown status: $status");
    }
  }
}