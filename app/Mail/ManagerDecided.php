<?php

namespace App\Mail;

use App\Enums\UserRole;
use App\Models\Request;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ManagerDecided extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
      public Request $request,
      public int $userId
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[DCS] Request Update - Decision Received',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $comment = $this->request->comment()->whereHas('user', function ($query) { 
          return $query->where(['id' => $this->userId]);
        })->latest()->first();

        $user = User::findOrFail($this->userId);

        return new Content(
            view: 'mail.manager-decided',
            text: 'text.manager-decided',
            with: [
              'requestTitle' => $this->request->title,
              'fileTitle' => $this->request->version()->latest()->first()->file_title,
              'requestType' => formatRequestType($this->request->type),
              'requestId' => $this->request->id,
              'manager' => $user->first_name . " " . $user->last_name,
              'comment' => $comment,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
