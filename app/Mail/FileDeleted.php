<?php

namespace App\Mail;

use App\Models\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FileDeleted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
      public Request $request
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[DCS] Document Deletion — Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $submittedBy = $this->request->user->first_name . " " . $this->request->user->last_name;

        return new Content(
            view: 'mail.file-deleted',
            text: 'text.file-deleted',
            with: [
              'requestTitle' => $this->request->title,
              'fileTitle' => $this->request->version()->latest()->first()->file_title,
              'requestType' => formatRequestType($this->request->type),
              'requestId' => $this->request->id,
              'submittedBy' => $submittedBy,
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
