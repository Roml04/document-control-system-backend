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
          "uploadDate" => $this->upload_date ? Carbon::parse($this->upload_date)->format('Y-m-d h:iA') : null,
          "revisionDate" => $this->revision_date ? Carbon::parse($this->revision_date)->format('Y-m-d h:iA') : null,
          "approverId" => $this->approver_id,
          "approvedDate" => $this->approved_date ? Carbon::parse($this->approved_date)->format('Y-m-d h:iA') : null,
          "status" => $this->status,
          "fileName" => $this->file_name,
          "filePath" => $this->file_path,
          "fileId" => $this->file_id,
          "requestId" => $this->request_id,
          /**
           * edit_session_started_at, draft_saved_at, last_save_status
           */
          "createdAt" => $this->created_at ? $this->created_at->format('Y-m-d h:iA') : null,
          "updatedAt" => $this->updated_at ? $this->updated_at->format('Y-m-d h:iA') : null,

          "request" => $this->when($this->request !== null, fn() => [
            "id" => $this->request->id,
            "title" => $this->request->title,
            "status" => $this->request->status,
            "user" => $this->request->when($this->request->user !== null, fn() => [
              "firstName" => $this->request->user->first_name,
              "lastName" => $this->request->user->last_name,
            ]),
          ]),
          
          "approver" => $this->when($this->approver !== null, fn() => [
            "id" => $this->approver->id,
            "firstName" => $this->approver->first_name,
            "lastName" => $this->approver->last_name,
          ]),
        ];
    }
}
