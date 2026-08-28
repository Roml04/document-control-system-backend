<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
      "content",
      "user_id",
      "request_id"
    ];

    public function user() {
      return $this->belongsTo(User::class);
    }

    public function request() {
      return $this->belongsTo(Request::class);
    }
}