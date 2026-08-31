<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagersApproval extends Model
{
    protected $fillable = ['manager_id', 'request_id', 'decision', 'decided_at'];

    public function user() {
      return $this->belongsTo(User::class);
    }

    public function request() {
      return $this->belongsTo(Request::class);
    }
}
