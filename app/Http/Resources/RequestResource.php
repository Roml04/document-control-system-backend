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
          "createdAt" => $this->created_at,
          "updatedAt" => $this->updated_at,

          "user" => [
            "id" => $this->user->id,
            "email" => $this->user->email,
            "password" => $this->user->password,
            "firstName" => $this->user->first_name,
            "lastName" => $this->user->last_name,
            "role" => $this->user->role,
            "createdAt" => $this->user->created_at,
            "updatedAt" => $this->user->updated_at
          ],

          "version" => [
            "id" => $this->version->id,
            "fileTitle" => $this->version->file_title,
            "fileType" => $this->version->file_type,
            "originator" => $this->version->originator,
            "department" => $this->version->department,
            "revisionNumber" => $this->version->revision_number,
            "revisionDetails" => $this->version->revision_details,
            "uploadDate" => $this->version->upload_date,
            "revisionDate" => $this->version->revision_date,
            "approver" => $this->version->approver,
            "approvedDate" => $this->version->approved_date,
            "status" => $this->version->status,
            "fileName" => $this->version->file_name,
            "filePath" => $this->version->file_path,
            "fileId" => $this->version->file_id,
            "requestId" => $this->version->request_id,
            "createdAt" => $this->version->created_at,
            "updatedAt" => $this->version->updated_at
          ]
        ];
    }
}
