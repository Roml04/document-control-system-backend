<?php

namespace App\Models;

use App\Policies\FilePolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UsePolicy(FilePolicy::class)]
class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
    ];

    public function version()
    {
        return $this->hasMany(Version::class);
    }
}
