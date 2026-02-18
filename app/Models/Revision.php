<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    protected $fillable = [
      'title', 
      'reason', 
      'approval_stage', 
      'status', 
      'user_id', 
      'document_id'
    ];

    public function document() {
      return $this->belongsTo(Document::class);
    }

    public function user() {
      return $this->belongsTo(User::class);
    }
}
