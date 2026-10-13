<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
          "id" => $this->id,
          "type" => $this->type,
          "title" => $this->title,
          "reason" => $this->reason,
          "status" => $this->status,
          "userId" => $this->user_id,
          "wasEdited" => $this->was_edited,
          "uploadDate" => $this->created_at->format('Y-m-d h:iA'),
          "updatedAt" => $this->updated_at->format('Y-m-d h:iA'),

          "user" => $this->whenLoaded('user', function() {
            return new UserResource($this->user);
          }),

          "version" => $this->whenLoaded('version', function() {
            return new VersionResource($this->version()->latest()->first());
          }),
                    
          "comments" => CommentResource::collection($this->comment->sortByDesc('created_at')->values()),
        ];
    }
}

