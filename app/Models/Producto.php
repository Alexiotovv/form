<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'cod_unificado',
        'cod_sismed_analisis',
        'cod_sismed',
        'cod_siga',
        'codigo_atc',
        'cod_unspsc',
        'descripcion_sismed',
        'concentracion',
        'forma_farmaceutica',
        'presentacion',
        'tipo_prod',
        'lista_1',
        'lista_2',
        'tipo_abastecimiento','estrategico','biologicos','odontologicos','reactivos',
        'vitales','peti2023','peti2018','peti2015','peti2012','peti2010','venta',
        'estado','reg_sanit','descripcion_siga','descripcion_cubo','unidad_medida_x',
        'descripcion_cubo_2','descripcion_producto','descripcion_producto_alt',
        'descripcion_producto_eca','unidad_medida_siga','grupo','programas',
        'programas_presupuestales','producto_fed','producto_fed_actual','tipo_indicador_fed',
        'producto_ap_endis','anemia','claves_obstetricas','clave_azul','clave_amarilla',
        'clave_roja','iras','iras_menor_12','edas','dengue','dengue_grupo_a',
        'dengue_grupo_b','dengue_grupo_c','malaria','chikungunya','zika','leishmania',
        'chagas','ofidismo','leptospirosis','planificacion_familiar','epp','covid19',
        'covid19_apoyo_tto','covid_protocolo_minsa','pareto','vital','convenio_gestion_2020',
        'convenio_gestion_2021','producto_cap_eca'
    ];
}
