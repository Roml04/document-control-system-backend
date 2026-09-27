REVIEW PENDING

A new request has been submitted and is now awaiting coordinator review.

Request: {{ $requestTitle }}
@switch($requestType)
@case("upload")
Uploaded File: {{ $fileTitle }}
@break
@case("revision")
Document Under Revision: {{ $fileTitle }}
@break
@case("resubmit")
{{-- FOR FUTURE PURPOSES --}}
@break
@case("delete")
{{-- FOR FUTURE PURPOSES --}}
@break
@default

@endswitch
Request Type: {{ $requestType }}
Request ID: {{ $requestId }}
Submitted By: {{ $submittedBy }}

Please review the request and take the appropriate action to proceed with the approval process.