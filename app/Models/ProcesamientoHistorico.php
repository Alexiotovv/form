<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class ProcesamientoHistorico extends Model
{
    use HasFactory;

    protected $table = 'procesamientos_historicos';

    protected $fillable = [
        'fecha_ejecucion',
        'tiempo_ejecucion',
        'tablas_registros',
        'user_id',
    ];

    // Relación con usuarios
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
