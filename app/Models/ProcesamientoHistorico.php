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
        'registro_id',
    ];
    protected $casts = [
        'fecha_ejecucion' => 'datetime',
    ];
    // Relación con usuarios
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación con registros
    public function registro()
    {
        return $this->belongsTo(Registro::class);
    }

    // Relación con detalles de formulario
    public function formDet()
    {
        return $this->hasMany(FormDet::class, 'procesamiento_id');
    }

    // Obtener almacenes relacionados
    public function almacenes()
    {
        if ($this->registro) {
            return collect([$this->registro->almacen]);
        }
        return collect([]);
    }

    // Obtener códigos PRE únicos del procesamiento
    public function obtenerCodigosPre()
    {
        return $this->formDet()
            ->whereNotNull('CODIGO_PRE')
            ->distinct('CODIGO_PRE')
            ->pluck('CODIGO_PRE')
            ->toArray();
    }
}
