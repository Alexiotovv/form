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
        'almacen_id',
        'fecha_envio',
        'hora_envio',
        'archivo',
        'procesado',
        'user_id',
    ];

    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }
    public function profesion()
    {
        return $this->belongsTo(Profesion::class);
        // return $this->belongsTo(Establecimiento::class);

    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    protected $casts = [
        'procesado' => 'boolean'
    ];


}
