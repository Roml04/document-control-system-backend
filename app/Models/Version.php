<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    protected $fillable = [
      'originator', 
      'department', 
      'revision_number',
      'revision_details',
      'revision_date',
      'approver',
      'approved_date',
      'document_id',
      'file_path',
      'status',
      'revision_id'
    ];

    public function document() {
      return $this->belongsTo(Document::class);
    }

    public function originatorUser() {
      return $this->belongsTo(User::class, 'originator');
    }

    public function approverUser() {
      return $this->belongsTo(User::class, 'approver');
    }

    public function revision() {
      return $this->belongsTo(Revision::class);
    }
}
