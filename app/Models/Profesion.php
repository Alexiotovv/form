<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profesion extends Model
{
    protected $table = 'profesiones';

    public function registros()
    {
        return $this->hasMany(Registro::class);
    }
}
