<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    protected $fillable = [
        'nombres',
        'apellidos',
        'correo',
        'telefono',
        'profesion_id',
        'establecimiento_id',
        'fecha_envio',
        'hora_envio',
        'archivo',
    ];

    public function establecimiento()
    {
        return $this->belongsTo(Establecimiento::class);
    }
    public function profesion()
    {
        return $this->belongsTo(Profesion::class);
    }



}
