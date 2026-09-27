MANAGER DECISION

A manager has submitted a decision regarding the following document request.

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

Manager: {{ $manager }}

@if ($comment)
Comment: {{ $comment->content }}
@endif