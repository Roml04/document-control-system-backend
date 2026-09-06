<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VersionResource extends JsonResource
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
          "fileTitle" => $this->file_title,
          "fileType" => $this->file_type,
          "originator" => $this->originator,
          "department" => $this->department,
          "revisionNumber" => $this->revision_number,
          "revisionDetails" => $this->revision_details,
          "uploadDate" => Carbon::parse($this->upload_date)->format('Y-m-d h:iA'),
          "revisionDate" => $this->revision_date ? Carbon::parse($this->revision_date)->format('Y-m-d h:iA') : null,
          "approver" => $this->approver,
          "approvedDate" => $this->approved_date ? Carbon::parse($this->approved_date)->format('Y-m-d h:iA') : null,
          "fileName" => $this->file_name,
          "filePath" => $this->file_path,
          "fileId" => $this->file_id,
          "requestId" => $this->request_id,
          "createdAt" => $this->created_at ? $this->created_at->format('Y-m-d h:iA') : null,
          "updatedAt" => $this->updated_at ? $this->updated_at->format('Y-m-d h:iA') : null,
        ];
    }
}
