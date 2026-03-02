<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
      'name',
    ];

    public function version() {
      return $this->hasMany(Version::class);
    }

    public function revision() {
      return $this->hasMany(Revision::class);
    }
}
