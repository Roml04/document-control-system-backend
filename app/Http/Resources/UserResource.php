<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
          "email" => $this->email,
          "firstName" => $this->first_name, 
          "lastName" => $this->last_name, 
          "role" => $this->role,
          "createdAt" => $this->created_at,
          "updatedAt" => $this->updated_at
        ];
    }
}
