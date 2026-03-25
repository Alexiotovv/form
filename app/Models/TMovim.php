<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TMovim extends Model
{
    use HasFactory;

    protected $table = 'tmovim';

    protected $fillable = [
        'movcoditip',
        'movnumero',
        'almcodiorg',
        'almorgvir',
        'almcodidst',
        'almdstvir',
        'movtipodci',
        'movnumedci',
        'movtipodco',
        'movnumedco',
        'cctcodigo',
        'movtot',
        'prvnumeruc',
        'prvdescrip',
        'movrefe',
        'movfechult',
        'movsitua',
        'tip_comp',
        'tip_proc',
        'num_proc',
        'movfecanul',
    ];

    protected $casts = [
        'movtot' => 'decimal:2',
        'movfechult' => 'datetime',
    ];

    /**
     * Relación con los detalles del movimiento
     */
    public function detalles()
    {
        return $this->hasMany(TMovimDet::class, 'movnumero', 'movnumero');
    }

    /**
     * Scope para movimientos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('movsitua', 'A');
    }

    /**
     * Scope para movimientos por tipo
     */
    public function scopeTipo($query, $tipo)
    {
        return $query->where('movcoditip', $tipo);
    }

    /**
     * Scope para movimientos por fecha
     */
    public function scopeUltimos($query, $dias = 30)
    {
        return $query->where('movfechult', '>=', now()->subDays($dias));
    }
}