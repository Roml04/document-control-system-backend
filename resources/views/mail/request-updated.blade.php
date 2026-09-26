<head>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <style>
    html, body {
      margin: 0;
      padding: 0;
    }
    h1,
    h2,
    h3,
    p {
        font-family: 'Inter';
        margin: 0;
        padding-top: 8px;
        padding-bottom: 8px;
    }
  </style>
</head>
<body style="background-color: #edf0f5">
  <div style="gap: 1.5rem;">
    <div style="background-color: black; padding: 1.5rem; margin-bottom: 1.5rem">
      <h1 style="color: white">DCS Mailbox</h1>
    </div>
    <div style="padding-left: 2rem; padding-right: 2rem; padding-bottom: 2rem;">
      <div style="padding: 1.5rem; background-color: white">
        <div style="border-bottom: 1px solid black; padding-bottom: 0.5rem;">
          <h1>Status Changed</h1>
        </div>
        <div style="padding-top: 0.5rem;">
          @if ($requestStatus === "originator_edit")
            <p>Your revision request has been approved and the document has now moved to the <span style="font-weight: 600;">Originator Edit</span> stage.</p>
            <p>The document is now ready for you to make the necessary revisions before it proceeds to the next approval stage.</p>
          @else
            <p>Your {{ $requestType }} request has been approved at the current approval stage and has now moved forward to the next approval stage.</p>
          @endif
          
          <div style="padding-top: 8px; padding-bottom: 8px;">
            <p style="padding: 0;"><span style="font-weight: 600;">Request</span>: {{ $requestTitle }}</p>
            @switch($requestType)
                @case("upload")                    
                    <p style="padding: 0;"><span style="font-weight: 600;">Uploaded File</span>: {{ $fileTitle }} </p>              
                    @break
                @case("revision")
                    <p style="padding: 0;"><span style="font-weight: 600;">Document Under Revision</span>: {{ $fileTitle }} </p>              
                    @break
                @case("resubmit")
                    {{-- FOR FUTURE PURPOSES --}}
                    @break
                
                @case("delete")    
                    {{-- FOR FUTURE PURPOSES --}}
                    @break
                @default
                    
            @endswitch
            <p style="padding: 0;"><span style="font-weight: 600;">Request ID</span>: {{ $requestId }} </p>
            <p style="padding: 0;"><span style="font-weight: 600;">Current Status</span>: {{ $requestStatus }}</p>
            @if ($comment)
              <div style="padding-top: 0.5rem; padding-bottom: 0.5rem;">
                <h3>Comment</h3>
                <p style="border-left: 1px solid lightgray; padding-left: 1rem;"><span style="font-weight: 600">{{ $comment->user->first_name . " " . $comment->user->last_name }}</span>: {{ $comment->content }}</p>
              </div>
            @endif
          </div>
          <p>No further action is required from you at this time. You will receive another notification once there is an update to your request.</p>
        </div>
      </div>
    </div>
  </div>
</body>