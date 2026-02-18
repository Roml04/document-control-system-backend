<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
      'type', 
      'originator', 
      'department', 
      'revision_number',
      'revision_details', 
      'revision_date', 
      'approver',
      'approved_date',
    ];

    public function version() {
      return $this->hasMany(Version::class);
    }
}
