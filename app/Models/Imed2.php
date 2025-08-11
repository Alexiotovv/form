<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Imed2 extends Model
{
    protected $table = 'imed2';

    protected $fillable = [
        'ANNOMES',
        'CODIGO_EJE',
        'CODIGO_PRE',
        'FECHDEPO',
        'NRODEPO',
        'IMPDEPO',
    ];


}
