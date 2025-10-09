<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DjangoConfig extends Model
{
        protected $fillable = [
        'url',
        'token',
        'password_zip',
    ];
}
