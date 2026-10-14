<?php

namespace App\Models;

use App\Policies\VersionPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UsePolicy(VersionPolicy::class)]
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
        'approver_id',
        'approved_date',
        'status',
        'file_name',
        'file_path',
        'file_id',
        'request_id',
        'edit_session_started_at',
        'draft_saved_at',
        'last_save_status'
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function approver() {
        return $this->belongsTo(User::class, 'approver_id', 'id');
    }
}
