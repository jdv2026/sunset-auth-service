<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DbVersion extends Model 
{

	protected $table = 'db_versions';
    protected $fillable = [
        'db_version',
        'description',
        'migrations_id',
    ];
}
