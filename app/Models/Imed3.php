<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Imed3 extends Model
{
    protected $table = 'imed3';

    protected $fillable = [
        'ANNOMES',
        'CODIGO_EJE',
        'CODIGO_PRE',
        'FECHGUIA',
        'NROGUIA',
        'IMPGUIA',
    ];

}
