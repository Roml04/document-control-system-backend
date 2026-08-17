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
    ];

    public function version()
    {
        return $this->hasOne(Version::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
