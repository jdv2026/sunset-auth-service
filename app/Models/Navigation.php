<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Navigation extends Model
{

    protected $fillable = [
        'logo',
        'name',
        'link',
		'header',
    ];
	
}
