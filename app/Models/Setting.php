<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

    protected $fillable = [
		'theme_name',
		'theme_className',
        'orientation',
        'footer',
		'toolBar',
		'isDarkMode',
        'created_by',
        'updated_by',
		'footer_fixed',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
	
}
