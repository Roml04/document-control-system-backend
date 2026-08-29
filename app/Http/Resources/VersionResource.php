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
          "uploadDate" => Carbon::parse($this->upload_date)->format('Y-m-d'),
          "revisionDate" => Carbon::parse($this->revision_date)->format('Y-m-d'),
          "approver" => $this->approver,
          "approvedDate" => Carbon::parse($this->approved_date)->format('Y-m-d'),
          "fileName" => $this->file_name,
          "filePath" => $this->file_path,
          "fileId" => $this->file_id,
          "requestId" => $this->request_id,
          "createdAt" => $this->created_at,
          "updatedAt" => $this->updated_at,
        ];
    }
}
