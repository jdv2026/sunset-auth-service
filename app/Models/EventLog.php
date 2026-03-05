<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventLog extends Model
{

    use HasFactory;
    protected $fillable = [
        'action_type',
        'action_by',
        'action',
		'user_id',
		'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
