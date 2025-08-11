<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormDet extends Model
{
    protected $table = 'form_det';
    
    protected $fillable = [
        'CODIGO_EJE',
        'CODIGO_PRE',
        'TIPSUM',
        'ANNOMES',
        'CODIGO_MED',
        'SALDO',
        'PRECIO',
        'INGRE',
        'REINGRE',
        'VENTA',
        'SIS',
        'INTERSAN',
        'FAC_PERD',
        'DEFNAC',
        'EXO',
        'SOAT',
        'CREDHOSP',
        'OTR_CONV',
        'DEVOL',
        'VENCIDO',
        'MERMA',
        'DISTRI',
        'TRANSF',
        'VENTAINST',
        'DEV_VEN',
        'DEV_MERMA',
        'OTRAS_SAL',
        'STOCK_FIN',
        'STOCK_FIN1',
        'REQ',
        'TOTAL',
        'FEC_EXP',
        'DO_SALDO',
        'DO_INGRE',
        'DO_CON',
        'DO_OTR',
        'DO_TOT',
        'DO_STK',
        'DO_FECEXP',
        'FECHA',
        'USUARIO',
        'INDIPROC',
        'SIT',
        'INDISIGA',
        'DSTKCERO',
        'MPTOREPO',
        'ING_REGULA',
        'SAL_REGULA',
        'SAL_CONINS',
        'STOCKFIN',
        'STOCKFIN1'
    ];
}
