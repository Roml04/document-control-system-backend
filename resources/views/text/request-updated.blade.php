STATUS CHANGED

@if ($requestStatus === "originator_edit")
Your revision request has been approved and the document has now moved to the Originator Edit stage.

The document is now ready for you to make the necessary revisions before it proceeds to the next approval stage.
@else
Your {{ $requestType }} request has been approved at the current approval stage and has now moved forward to the next approval stage.
@endif

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
Request ID: {{ $requestId }}
Current Status: {{ $requestStatus }}
@if ($comment)

COMMENT
{{ $comment->user->first_name . " " . $comment->user->last_name }}: {{ $comment->content }}
@endif

No further action is required from you at this time. You will receive another notification once there is an update to your request.