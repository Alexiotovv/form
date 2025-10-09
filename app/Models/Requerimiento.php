<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requerimiento extends Model
{
    use HasFactory;

    protected $fillable = [
        'cod_sismed',
        'descripcion_producto',
        'stock_final',
        'fecha_venc',
        'cpm',
        'msd',
        'situacion_stock',
        'situacion_fecha_venc',
        'req_sugerido',
        'req_final',
        'confirmado', 
        'almacen_id',
        'fecha_registro',
        'user_id' 
    ];

    protected $casts = [
        'fecha_venc' => 'date',
        'req_final' => 'integer',
        'req_sugerido' => 'integer',
        'stock_final' => 'integer',
        'cpm' => 'integer',
        'msd' => 'decimal:2'
    ];
    // Relación
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'cod_sismed', 'cod_sismed');
    }
}