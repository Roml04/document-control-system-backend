<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{
    protected $fillable = ['title', 'reason', 'user_id', 'document_id'];
}
