<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_title',
        'file_type',
        'originator',
        'department',
        'revision_number',
        'revision_details',
        'upload_date',
        'revision_date',
        'approver',
        'approved_date',
        'status',
        'file_name',
        'file_path',
        'file_id',
        'request_id',
        'edit_session_started_at',
        'draft_saved_at'
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function request()
    {
        return $this->belongsTo(Request::class);
    }
}
