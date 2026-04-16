<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportacionMatriz extends Model
{
    protected $table = 'exportaciones_matriz';
    
    protected $fillable = [
        'user_id', 'nombre_archivo', 'ruta_archivo', 'estado', 'progreso',
        'mensaje', 'filtros_aplicados', 'total_registros', 'registros_procesados',
        'fecha_inicio', 'fecha_fin'
    ];
    
    protected $casts = [
        'filtros_aplicados' => 'array',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}