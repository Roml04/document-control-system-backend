<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function revision() {
      return $this->hasMany(Revision::class);
    }

    public function originatedVersion() {
      /*
      * The first parameter specifies the related 
      * model. 
      * 
      * The second parameter specifies the column 
      * (foreign key) of the related model that
      * references the primary key of this model
      * 
      * A third parameter can also be used to
      * specify the primary key in this model 
      * that is being used as a reference by 
      * the related model.
      */
      return $this->hasMany(Version::class, 'originator');
    }
    
    public function approvedVersion() {
      return $this->hasMany(Version::class, 'approver');
    }
}
