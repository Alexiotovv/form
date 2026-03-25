<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TMovimDet extends Model
{
    use HasFactory;

    protected $table = 'tmovimdet';

    protected $fillable = [
        'movcoditip',
        'movnumero',
        'medcod',
        'medlote',
        'medfechvto',
        'movcantid',
        'movprecio',
        'movtotal',
        'movfechult',
        'movsitua',
    ];

    protected $casts = [
        'movcantid' => 'decimal:2',
        'movprecio' => 'decimal:2',
        'movtotal' => 'decimal:2',
        'medfechvto' => 'date',
        'movfechult' => 'datetime',
    ];

    /**
     * Relación con el movimiento principal
     */
    public function movimiento()
    {
        return $this->belongsTo(TMovim::class, 'movnumero', 'movnumero');
    }

    /**
     * Scope para detalles activos
     */
    public function scopeActivos($query)
    {
        return $query->where('movsitua', 'A');
    }

    /**
     * Scope para productos específicos
     */
    public function scopeProducto($query, $medcod)
    {
        return $query->where('medcod', $medcod);
    }

    /**
     * Scope para lotes específicos
     */
    public function scopeLote($query, $lote)
    {
        return $query->where('medlote', $lote);
    }

    /**
     * Scope para productos próximos a vencer
     */
    public function scopePorVencer($query, $dias = 90)
    {
        return $query->where('medfechvto', '<=', now()->addDays($dias))
                     ->where('medfechvto', '>', now());
    }

    /**
     * Scope para productos vencidos
     */
    public function scopeVencidos($query)
    {
        return $query->where('medfechvto', '<', now());
    }

    /**
     * Calcular el subtotal
     */
    public function getSubtotalAttribute()
    {
        return $this->movcantid * $this->movprecio;
    }
}