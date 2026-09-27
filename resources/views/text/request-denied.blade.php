REQUEST DENIED

Your document request has been denied during the approval process.

Request: {{ $requestTitle }}
Request ID: {{ $requestId }}
Current Status: {{ $requestStatus }}
@if ($comment)

COMMENT
{{ $comment->user->first_name . " " . $comment->user->last_name }}: {{ $comment->content }}
@endif

Please review the reason for the denial and take any necessary action regarding the request.