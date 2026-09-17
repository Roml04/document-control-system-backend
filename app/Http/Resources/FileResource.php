<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
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
          "title" => $this->title,
          "type" => $this->type,
          "createdAt" => $this->created_at,
          "updatedAt" => $this->updated_at,
          
          "latestVersion" => new VersionResource($this->version()->latest()->first()),
          "versions" => $this->whenLoaded('version', function() {
            return VersionResource::collection($this->version->where('status', 'published')->sortByDesc('approved_date'));
          })
        ];
    }
}
