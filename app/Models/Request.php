<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'reason',
        'status',
        'user_id',
        'file_id',
    ];

    public function version()
    {
        return $this->hasOne(Version::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comment() 
    {
        return $this->hasMany(Comment::class);
    }

    public function managersApproval() {
      return $this->hasMany(ManagersApproval::class);
    }
}
