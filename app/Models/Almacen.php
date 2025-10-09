<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Almacen extends Model
{
    use HasFactory;

    protected $table = 'almacenes';

    protected $fillable = [
        'cod_pliego',
        'pliego',
        'cod_disa',
        'disa_diresa',
        'cod_ue_mef',
        'ue_mef',
        'departamento',
        'ubigeo',
        'provincia',
        'distrito',
        'cod_renipress',
        'cod_ipress',
        'red',
        'microred',
        'nombre_ipress',
        'codigo_nombre_ipress',
        'nivel',
        'tipo_establecimiento',
        'estado_ipress',
        'universo_ipress',
        'ipress_feed',
        'ipress_eca',
        'ipress_evaluar_disponibilidad',
        'ipress_dengue',
        'ipress_prio_temp_bajas',
        'ipress_prio_riesg_lluv',
        'est_pert_cuencas',
        'ipress_prio_plan_malaria',
        'almacen_pertenece',
        'filtro',
        'ruta_distribucion',
        'monitor',
        'digitador',
        'envios',
    ];
    // protected $casts = [
    //     'envios' => 'integer',
    // ];

}
