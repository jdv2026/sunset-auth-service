<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject 
{

    use HasFactory, Notifiable;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role' => $this->role,
        ];
    }
	
    public function eventLogs()
    {
        return $this->hasMany(EventLog::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class, 'updated_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'created_by');
    }

    protected $fillable = [
        'first_name',
        'last_name',
        'age',
		'height',
		'address',
		'profile_picture',
		'phone',
		'username',
		'password',
		'status',
		'type',
		'created_by',
		'updated_by',
		'dob',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'attempts_expiry' => 'datetime',
            'password' => 'hashed',
        ];
    }

}
