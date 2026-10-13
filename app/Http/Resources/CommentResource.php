<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
          "content" => $this->content,
          "userId" => $this->user_id,
          "requestId" => $this->request_id,
          "createdAt" => $this->created_at,
          "updatedAt" => $this->updated_at,

          "user" => [
            "id" => $this->user->id,
            "firstName" => $this->user->first_name,
            "lastName" => $this->user->last_name,
            "role" => $this->user->role,
          ]
        ];
    }
}
