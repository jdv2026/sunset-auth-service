<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model 
{

    use HasFactory;

	protected $fillable = [
		'plan',
		'amount',
        'discount',
        'expiry_date',
		'toolBar',
        'created_by',
        'updated_by',
		'user_id',
		'invoice',
    ];

	public function user() 
	{
		return $this->belongsTo(User::class);
	}

}
