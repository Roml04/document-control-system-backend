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
          "uploadDate" => $this->created_at->format('Y-m-d h:iA'),
          "updatedAt" => $this->updated_at->format('Y-m-d h:iA'),

          "user" => $this->whenLoaded('user', function() {
            return new UserResource($this->user);
          }),

          "version" => $this->whenLoaded('version', function() {
            return new VersionResource($this->version);
          }),

          "commenters" => $this->comment
            ->groupBy('user_id')
            ->sortByDesc(function($comment) {
              return $comment->max('created_at');
            })
            ->map(function ($comment) {
              $user = $comment->first()->user;
              return [
                "userId" => $user->id,
                "firstName" => $user->first_name,
                "lastName" => $user->last_name,
                "role" => $user->role,
                "comments" => CommentResource::collection($comment->values())
              ];
            })
            ->values(),
        ];
    }
}

