<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{

    protected $fillable = [
        'label',
        'icon',
        'color_class',
        'created_by',
        'user_id',
		'notify_id',
		'read',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
	
}
