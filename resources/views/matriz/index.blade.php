@extends('admin.base')

@section('css')
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet" />
    <link href="{{asset('css/select2-bootstrap.css')}}" rel="stylesheet"/>

    <style>
        /* Reducir todo el contenedor */
        .container-fluid {
            font-size: 0.75rem;
            padding-left: 10px;
            padding-right: 10px;
        }
        
        /* Achicar títulos */
        h4, h5, h6 {
            font-size: 0.85rem !important;
            margin-bottom: 0.3rem !important;
        }
        
        /* Achicar labels */
        label {
            font-size: 0.7rem !important;
            margin-bottom: 0.1rem !important;
            font-weight: 500;
        }
        
        /* Achicar inputs y selects */
        .form-control, .form-select {
            font-size: 0.7rem !important;
            padding: 0.2rem 0.3rem !important;
            height: auto !important;
        }
        
        /* Achicar los select2 */
        .select2-container .select2-selection--single {
            height: 26px !important;
            font-size: 0.7rem !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 24px !important;
            font-size: 0.7rem !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 24px !important;
        }
        
        .select2-container--default .select2-selection--multiple {
            font-size: 0.7rem !important;
            min-height: 26px !important;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            font-size: 0.65rem !important;
            padding: 1px 4px !important;
            margin: 1px !important;
        }
        
        /* Achicar botones */
        .btn, .btn-sm {
            font-size: 0.65rem !important;
            padding: 0.15rem 0.5rem !important;
        }
        
        /* Layout de dos columnas principal */
        .dashboard-layout {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        /* Panel izquierdo - Filtros (35%) */
        .filtros-panel {
            width: 25%;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            border: 1px solid #dee2e6;
            height: fit-content;
        }
        
        /* Panel derecho - Resúmenes (65%) */
        .resumenes-panel {
            width: 75%;
            display: flex;
            gap: 12px;
        }
        
        /* Tarjetas de resumen */
        .card-resumen {
            flex: 1;
            background: white;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            overflow: hidden;
        }
        
        .card-resumen .card-header {
            background-color: #f8f9fa;
            padding: 6px 10px;
            font-weight: 600;
            font-size: 0.7rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .card-resumen .card-body {
            padding: 8px 10px;
        }
        
        /* Tabla de resumen de stock */
        .resumen-stock-table {
            width: 100%;
            font-size: 0.65rem;
        }
        
        .resumen-stock-table td {
            padding: 3px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .resumen-stock-table td:last-child {
            text-align: right;
            font-weight: 500;
        }
        
        .resumen-stock-table tr:last-child td {
            border-bottom: none;
            font-weight: bold;
            padding-top: 5px;
        }
        
        .resumen-stock-table .badge {
            font-size: 0.6rem;
            padding: 2px 5px;
        }
        
        /* Barra de progreso */
        .progress {
            height: 5px;
            margin-top: 5px;
        }
        
        /* Filtros avanzados en grid */
        .filtros-avanzados-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
            margin-top: 8px;
        }
        
        .filtros-basicos {
            display: grid;
            grid-template-columns: repeat(3, 2fr);
            gap: 3px;
            margin-bottom: 5px;
        }
        
        /* Achicar el input de fecha */
        #fechaFinMes {
            width: 100% !important;
        }
        
        /* Contenedor de la tabla con scroll */
        .table-responsive {
            max-height: 450px;
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            position: relative;
            margin-top: 15px;
        }
        
        
        .table-responsive thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }
        
        .table {
            font-size: 0.65rem !important;
            margin-bottom: 0;
        }
        
        .table th {
            font-size: 0.6rem !important;
            padding: 0.3rem 0.2rem !important;
            white-space: nowrap;
            font-weight: 600;
            background-color: #f8f9fa;
        }
        
        .table td {
            padding: 0.2rem 0.2rem !important;
            white-space: nowrap;
        }
        
        /* Minigráfico */
        .minigrafico-cell {
            padding: 2px !important;
            min-width: 100px;
        }
        
        .minigrafico {
            display: flex;
            align-items: flex-end;
            height: 28px;
            gap: 2px;
        }
        
        .barra-mini {
            flex: 1;
            background-color: #4299e1;
            min-width: 3px;
            border-radius: 2px 2px 0 0;
            transition: height 0.2s ease;
            position: relative;
        }
        
        .barra-mini[data-valor="0"] {
            background-color: #cbd5e0;
        }
        
        .barra-mini.valor-bajo {
            background-color: #fbbf24;
        }
        
        .barra-mini.valor-medio {
            background-color: #4299e1;
        }
        
        .barra-mini.valor-alto {
            background-color: #48bb78;
        }
        
        .barra-mini:hover::after {
            content: attr(data-valor);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #2d3748;
            color: white;
            font-size: 0.6rem;
            padding: 2px 4px;
            border-radius: 3px;
            white-space: nowrap;
            z-index: 20;
            pointer-events: none;
            margin-bottom: 2px;
        }
        
        /* Estilos para fila seleccionada */
        .table tbody tr.seleccionada {
            background-color: #cfe2ff !important;
            outline: 2px solid #0d6efd;
            outline-offset: -2px;
        }
        
        .table tbody tr.seleccionada td.seleccionada {
            background-color: #9ec5fe !important;
            outline: 2px solid #0a58ca;
            outline-offset: -2px;
        }
        
        .table tbody tr {
            cursor: pointer;
        }
        
        .table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.1);
        }
        
        /* Acciones de filtros */
        .acciones-filtros {
            margin-top: 12px;
            display: flex;
            gap: 8px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .dashboard-layout {
                flex-direction: column;
            }
            .filtros-panel, .resumenes-panel {
                width: 100%;
            }
            .resumenes-panel {
                flex-direction: column;
            }
            .filtros-basicos {
                grid-template-columns: 1fr;
            }
            .filtros-avanzados-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Columnas numéricas alineadas a la derecha */
        .table td:nth-child(5), .table td:nth-child(6), .table td:nth-child(7),
        .table td:nth-child(8), .table td:nth-child(9), .table td:nth-child(10),
        .table td:nth-child(11), .table td:nth-child(12), .table td:nth-child(13),
        .table td:nth-child(14), .table td:nth-child(15), .table td:nth-child(16),
        .table td:nth-child(17), .table td:nth-child(18), .table td:nth-child(20),
        .table td:nth-child(21), .table td:nth-child(22), .table td:nth-child(23),
        .table td:nth-child(24), .table td:nth-child(25), .table td:nth-child(26) {
            text-align: right;
        }

        /* Colores para encabezados de tabla */
        .table th.envio-sugerido {
            background-color: #ffcccc !important;
            color: #990000 !important;
        }

        .table th.proyectado {
            background-color: #cce5ff !important;
            color: #004085 !important;
        }

        .table th.distribucion {
            background-color: #cce5ff !important;
            color: #004085 !important;
        }

        .table th.vencimiento {
            background-color: #ffe5cc !important;
            color: #cc7000 !important;
        }
    </style>

    <style>
        /* Estilos para el icono de filtro en encabezado */
        .th-filter {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 5px;
        }
        
        .filter-icon {
            cursor: pointer;
            font-size: 0.7rem;
            color: #6c757d;
            transition: color 0.2s;
            padding: 2px;
            border-radius: 3px;
        }
        
        .filter-icon:hover {
            color: #0d6efd;
            background-color: #e9ecef;
        }
        
        .filter-icon.active {
            color: #0d6efd;
        }
        
        /* Modal de filtro */
        .filter-modal {
            display: none;
            position: absolute;
            z-index: 10000;
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            min-width: 200px;
            max-width: 300px;
            max-height: 300px;
            overflow: hidden;
        }
        
        .filter-modal-header {
            padding: 8px 12px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
            font-size: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .filter-modal-body {
            padding: 8px 12px;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .filter-modal-footer {
            padding: 8px 12px;
            border-top: 1px solid #dee2e6;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }
        
        .filter-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 0;
            font-size: 0.7rem;
        }
        
        .filter-option input {
            margin: 0;
        }
        
        .filter-option label {
            margin: 0;
            cursor: pointer;
            font-weight: normal;
        }
        
        .filter-option:hover {
            background-color: #f8f9fa;
        }
        
        .btn-filter-sm {
            font-size: 0.65rem;
            padding: 2px 6px;
        }
        
        .filter-badge {
            display: inline-block;
            background-color: #0d6efd;
            color: white;
            font-size: 0.55rem;
            padding: 2px 5px;
            border-radius: 10px;
            margin-left: 5px;
        }

        /* === COLUMNAS FIJAS (primera y segunda) === */
.table-responsive {
    position: relative;
    overflow-x: auto;
    /* max-height ya lo tienes */
}

/* Primera columna (DESC PROD) - índice 1 */
#registros th:nth-child(1),
#registros td:nth-child(1) {
    position: sticky;
    left: 0;
    z-index: 11;
    background-color: #f8f9fa;           /* fondo sólido para evitar transparencias */
    box-shadow: 2px 0 5px -2px rgba(0,0,0,0.15); /* sombra sutil para separar */
    border-right: 2px solid #dee2e6;
}

/* Segunda columna (COD_SISMED) - índice 2 */
#registros th:nth-child(2),
#registros td:nth-child(2) {
    position: sticky;
    left: 420px;                         /* ancho aproximado de la columna 1 (ajusta si es necesario) */
    z-index: 10;
    background-color: #f8f9fa;
    box-shadow: 2px 0 5px -2px rgba(0,0,0,0.1);
    border-right: 1px solid #dee2e6;
}

/* Cabeceras de las columnas fijadas (más prioridad) */
#registros thead th:nth-child(1),
#registros thead th:nth-child(2) {
    z-index: 20;                         /* más alto que el body y que el sticky header */
    background-color: #f8f9fa !important;
    top: 0;                              /* mantiene compatibilidad con sticky header */
}

/* Asegura que el sticky del header funcione bien con las columnas fijas */
#registros thead th {
    position: sticky;
    top: 0;
    z-index: 15;
    background-color: #f8f9fa !important;
}

#registros th:nth-child(1),
#registros td:nth-child(1) {
    min-width: 420px;   /* ancho fijo para que left: 220px funcione correctamente */
    max-width: 280px;
    white-space: normal; /* permite salto de línea si el nombre es muy largo */
}

    </style>
@endsection

@section('titulo_pagina')
    <!-- Título -->
    📊 Matriz de Disponibilidad
@endsection

<!-- @section('fecha_pagina')
    Fecha: {{ request('fin_mes', date('d/m/Y')) }}
@endsection -->

@section('content')
<div class="container-fluid">


    <!-- Layout principal -->
    <div class="dashboard-layout">
        <!-- Panel izquierdo - Filtros -->
        <div class="filtros-panel">
            <!-- Cuadro de Niveles de Disponibilidad -->
            <div class="mb-1" style="background-color: #f8f9fa; border-radius: 2px; padding: 1px;">
                <div class="small fw-bold mb-1 text-center">📊 Niveles de Disponibilidad</div>
                <div style="font-size: 0.65rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 0; border-bottom: 1px solid #e9ecef;">
                        <span>Disponibilidad ≥ 90%</span>
                        <span class="fw-bold" style="color: #28a745;">ÓPTIMO</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 0; border-bottom: 1px solid #e9ecef;">
                        <span>Disponibilidad ≥ 80% y &lt; 90%</span>
                        <span class="fw-bold" style="color: #17a2b8;">ALTO</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 0; border-bottom: 1px solid #e9ecef;">
                        <span>Disponibilidad ≥ 70% y &lt; 80%</span>
                        <span class="fw-bold" style="color: #fd7e14;">REGULAR</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 4px 0;">
                        <span>Disponibilidad &lt; 70%</span>
                        <span class="fw-bold" style="color: #dc3545;">BAJO</span>
                    </div>
                </div>
            </div>
            <h6 class="mb-1">🔍 Filtros</h6>
            
            <!-- Filtros básicos en grid -->
            <div class="filtros-basicos">
                <div>
                    <label for="fechaFinMes" class="form-label">📅 Fin de Mes</label>
                    <input type="date" id="fechaFinMes" name="fin_mes" class="form-control" value="{{ request('fin_mes') ?? date('Y-m-t') }}">
                </div>
                <div>
                    <label for="cod_ipress" class="form-label">🏥 IPRESS <span class="text-danger">*</span></label>
                    <select id="cod_ipress" name="cod_ipress" class="form-control select2" required>
                        <option value="">Seleccione...</option>
                        @if(request('cod_ipress'))
                            <option value="{{ request('cod_ipress') }}" selected>{{ request('cod_ipress') }} - {{ optional($registros->first())->nombre_ipress ?? 'Cargando...' }}</option>
                        @endif
                    </select>
                </div>
                <div>
                    <label for="cod_sismed" class="form-label">💊 Código SISMED</label>
                    <select id="cod_sismed" name="cod_sismed" class="form-control select2">
                        <option value="">Todos</option>
                        @if(request('cod_sismed'))
                            <option value="{{ request('cod_sismed') }}" selected>{{ request('cod_sismed') }} - {{ optional($registros->first())->descripcion_producto ?? 'Cargando...' }}</option>
                        @endif
                    </select>
                </div>
            </div>
   
            
            <h6 class="mb-1">🎯 Filtros Avanzados</h6>
            
            <!-- Filtros avanzados en grid de 3 columnas (2 filas) -->
            <div class="filtros-avanzados-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 8px;">
                <div>
                    <label for="tip_sum" class="form-label">TIPSUM</label>
                    <select id="tip_sum" name="tip_sum[]" class="form-control select2-multiple" multiple="multiple">
                        @php
                            $valoresTipSum = $registros->isNotEmpty() ? $registros->pluck('TIPSUM')->unique()->filter()->values() : collect([]);
                        @endphp
                        @if($registros->whereNull('TIPSUM')->count() > 0 || $registros->where('TIPSUM', '')->count() > 0)
                            <option value="__NULL__" {{ in_array('__NULL__', request('tip_sum', [])) ? 'selected' : '' }}>[En blanco]</option>
                        @endif
                        @foreach($valoresTipSum as $valor)
                            <option value="{{ $valor }}" {{ in_array($valor, request('tip_sum', [])) ? 'selected' : '' }}>{{ $valor }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="tipo_prod" class="form-label">Tipo Producto</label>
                    <select id="tipo_prod" name="tipo_prod[]" class="form-control select2-multiple" multiple="multiple">
                        @php
                            $valoresTipoProd = $registros->isNotEmpty() ? $registros->pluck('tipo_prod')->unique()->filter()->values() : collect([]);
                        @endphp
                        @if($registros->whereNull('tipo_prod')->count() > 0 || $registros->where('tipo_prod', '')->count() > 0)
                            <option value="__NULL__" {{ in_array('__NULL__', request('tipo_prod', [])) ? 'selected' : '' }}>[En blanco]</option>
                        @endif
                        @foreach($valoresTipoProd as $valor)
                            <option value="{{ $valor }}" {{ in_array($valor, request('tipo_prod', [])) ? 'selected' : '' }}>{{ $valor }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="tipo_abastecimiento" class="form-label">Tipo Abastecimiento</label>
                    <select id="tipo_abastecimiento" name="tipo_abastecimiento[]" class="form-control select2-multiple" multiple="multiple">
                        @php
                            $valoresTipoAbast = $registros->isNotEmpty() ? $registros->pluck('tipo_abastecimiento')->unique()->filter()->values() : collect([]);
                        @endphp
                        @if($registros->whereNull('tipo_abastecimiento')->count() > 0 || $registros->where('tipo_abastecimiento', '')->count() > 0)
                            <option value="__NULL__" {{ in_array('__NULL__', request('tipo_abastecimiento', [])) ? 'selected' : '' }}>[En blanco]</option>
                        @endif
                        @foreach($valoresTipoAbast as $valor)
                            <option value="{{ $valor }}" {{ in_array($valor, request('tipo_abastecimiento', [])) ? 'selected' : '' }}>{{ $valor }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="tipo_establecimiento" class="form-label">Tipo Establecimiento</label>
                    <select id="tipo_establecimiento" name="tipo_establecimiento[]" class="form-control select2-multiple" multiple="multiple">
                        @php
                            $valoresTipoEstab = $registros->isNotEmpty() ? $registros->pluck('tipo_establecimiento')->unique()->filter()->values() : collect([]);
                        @endphp
                        @if($registros->whereNull('tipo_establecimiento')->count() > 0 || $registros->where('tipo_establecimiento', '')->count() > 0)
                            <option value="__NULL__" {{ in_array('__NULL__', request('tipo_establecimiento', [])) ? 'selected' : '' }}>[En blanco]</option>
                        @endif
                        @foreach($valoresTipoEstab as $valor)
                            <option value="{{ $valor }}" {{ in_array($valor, request('tipo_establecimiento', [])) ? 'selected' : '' }}>{{ $valor }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="peti2023" class="form-label">PETI 2023</label>
                    <select id="peti2023" name="peti2023[]" class="form-control select2-multiple" multiple="multiple">
                        @php
                            $valoresPeti = $registros->isNotEmpty() ? $registros->pluck('peti2023')->unique()->filter()->values() : collect([]);
                        @endphp
                        @if($registros->whereNull('peti2023')->count() > 0 || $registros->where('peti2023', '')->count() > 0)
                            <option value="__NULL__" {{ in_array('__NULL__', request('peti2023', [])) ? 'selected' : '' }}>[En blanco]</option>
                        @endif
                        @foreach($valoresPeti as $valor)
                            <option value="{{ $valor }}" {{ in_array($valor, request('peti2023', [])) ? 'selected' : '' }}>{{ $valor }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="lista_1" class="form-label">Lista 1</label>
                    <select id="lista_1" name="lista_1[]" class="form-control select2-multiple" multiple="multiple">
                        @php
                            $valoresLista1 = $registros->isNotEmpty() ? $registros->pluck('lista_1')->unique()->filter()->values() : collect([]);
                        @endphp
                        @if($registros->whereNull('lista_1')->count() > 0 || $registros->where('lista_1', '')->count() > 0)
                            <option value="__NULL__" {{ in_array('__NULL__', request('lista_1', [])) ? 'selected' : '' }}>[En blanco]</option>
                        @endif
                        @foreach($valoresLista1 as $valor)
                            <option value="{{ $valor }}" {{ in_array($valor, request('lista_1', [])) ? 'selected' : '' }}>{{ $valor }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- Botones de acción -->
            <div class="acciones-filtros">
                <button type="button" id="btn-filtrar" class="btn btn-primary btn-sm w-100">🔍 Filtrar</button>
                <a href="{{ route('matriz.index') }}" class="btn btn-secondary btn-sm w-100">🗑️ Limpiar</a>
            </div>
            <button type="button" id="btn-limpiar-filtros" class="btn btn-outline-secondary btn-sm w-100 mt-2">🧹 Limpiar Filtros Avanzados</button>
        </div>
        
        <!-- Panel derecho - Resúmenes (horizontal) -->
        <div class="resumenes-panel">
            @php
                // Inicializar resumen de stock
                $resumenStock = [
                    'DESABASTECIDO' => ['count' => 0, 'monto' => 0, 'color' => '#dc3545'],
                    'SUBSTOCK' => ['count' => 0, 'monto' => 0, 'color' => '#fd7e14'],
                    'NORMOSTOCK' => ['count' => 0, 'monto' => 0, 'color' => '#28a745'],
                    'SOBRESTOCK' => ['count' => 0, 'monto' => 0, 'color' => '#6c757d'],
                    'SIN ROTACION' => ['count' => 0, 'monto' => 0, 'color' => '#6f42c1'],
                    'SIN CONSUMO' => ['count' => 0, 'monto' => 0, 'color' => '#17a2b8'],
                    'SIN DATOS' => ['count' => 0, 'monto' => 0, 'color' => '#6c757d'],
                    'POR VENCER' => ['count' => 0, 'monto' => 0, 'color' => '#fd7e14'],
                    'VENCIDO' => ['count' => 0, 'monto' => 0, 'color' => '#dc3545']
                ];
                
                foreach ($registros as $item) {
                    $stock = $item->situacion_stock;
                    if (isset($resumenStock[$stock])) {
                        $resumenStock[$stock]['count']++;
                        $resumenStock[$stock]['monto'] += $item->monto;
                    }
                    
                    $venc = $item->sit_fecha_vcmto;
                    if ($venc == 'POR VENCER' || $venc == 'VENCIDO') {
                        if (isset($resumenStock[$venc])) {
                            $resumenStock[$venc]['count']++;
                            $resumenStock[$venc]['monto'] += $item->monto;
                        }
                    }
                }
                
                // TOTAL = solo DESABASTECIDO + SUBSTOCK + NORMOSTOCK + SOBRESTOCK
                $baseTotal = $resumenStock['DESABASTECIDO']['count'] + 
                            $resumenStock['SUBSTOCK']['count'] + 
                            $resumenStock['NORMOSTOCK']['count'] + 
                            $resumenStock['SOBRESTOCK']['count'];
                
                // MONTO TOTAL = SUBSTOCK + NORMOSTOCK + SOBRESTOCK + SIN ROTACION
                $baseTotalMonto = $resumenStock['SUBSTOCK']['monto'] + 
                                $resumenStock['NORMOSTOCK']['monto'] + 
                                $resumenStock['SOBRESTOCK']['monto'] + 
                                $resumenStock['SIN ROTACION']['monto'];
                
                // Calcular porcentajes
                $resumenStock['DESABASTECIDO']['porcentaje'] = $baseTotal > 0 ? round(($resumenStock['DESABASTECIDO']['count'] / $baseTotal) * 100, 1) : 0;
                $resumenStock['SUBSTOCK']['porcentaje'] = $baseTotal > 0 ? round(($resumenStock['SUBSTOCK']['count'] / $baseTotal) * 100, 1) : 0;
                $resumenStock['NORMOSTOCK']['porcentaje'] = $baseTotal > 0 ? round(($resumenStock['NORMOSTOCK']['count'] / $baseTotal) * 100, 1) : 0;
                $resumenStock['SOBRESTOCK']['porcentaje'] = $baseTotal > 0 ? round(($resumenStock['SOBRESTOCK']['count'] / $baseTotal) * 100, 1) : 0;
                
                $baseSinRotacion = $baseTotal + $resumenStock['SIN ROTACION']['count'];
                $resumenStock['SIN ROTACION']['porcentaje'] = $baseSinRotacion > 0 ? round(($resumenStock['SIN ROTACION']['count'] / $baseSinRotacion) * 100, 1) : 0;
                
                $baseSinConsumo = $resumenStock['SUBSTOCK']['count'] + 
                                $resumenStock['NORMOSTOCK']['count'] + 
                                $resumenStock['SOBRESTOCK']['count'] + 
                                $resumenStock['SIN ROTACION']['count'] + 
                                $resumenStock['SIN CONSUMO']['count'];
                $resumenStock['SIN CONSUMO']['porcentaje'] = $baseSinConsumo > 0 ? round(($resumenStock['SIN CONSUMO']['count'] / $baseSinConsumo) * 100, 1) : 0;
                
                $basePorVencer = $baseTotal + $resumenStock['SIN ROTACION']['count'];
                $resumenStock['POR VENCER']['porcentaje'] = $basePorVencer > 0 ? round(($resumenStock['POR VENCER']['count'] / $basePorVencer) * 100, 1) : 0;
                $resumenStock['VENCIDO']['porcentaje'] = $baseTotal > 0 ? round(($resumenStock['VENCIDO']['count'] / $baseTotal) * 100, 1) : 0;
                
                // Sumatoria porcentaje (DESABASTECIDO + SUBSTOCK + NORMOSTOCK + SOBRESTOCK)
                $sumatoriaPorcentaje = $resumenStock['DESABASTECIDO']['porcentaje'] + 
                                    $resumenStock['SUBSTOCK']['porcentaje'] + 
                                    $resumenStock['NORMOSTOCK']['porcentaje'] + 
                                    $resumenStock['SOBRESTOCK']['porcentaje'];
                
                // NORMOSTOCK + SOBRESTOCK para el nivel
                $regularTotal = $resumenStock['NORMOSTOCK']['count'] + $resumenStock['SOBRESTOCK']['count'];
                $regularPorcentaje = $baseTotal > 0 ? round(($regularTotal / $baseTotal) * 100, 1) : 0;
                
                $disponibilidad = $regularPorcentaje;
                $nivel = '';
                $nivelColor = '';
                
                if ($disponibilidad >= 90) {
                    $nivel = 'ÓPTIMO';
                    $nivelColor = '#28a745';
                } elseif ($disponibilidad >= 80 && $disponibilidad < 90) {
                    $nivel = 'ALTO';
                    $nivelColor = '#17a2b8';
                } elseif ($disponibilidad >= 70 && $disponibilidad < 80) {
                    $nivel = 'REGULAR';
                    $nivelColor = '#fd7e14';
                } else {
                    $nivel = 'BAJO';
                    $nivelColor = '#dc3545';
                }
                
                // Variables para mostrar en la tabla
                $totalRegistrosMostrar = $baseTotal;
                $totalMontoMostrar = $baseTotalMonto;

                // Inicializar resumen proyectado
                $resumenProyectado = [
                    'DESABASTECIDO' => ['count' => 0, 'monto' => 0, 'color' => '#dc3545'],
                    'SUBSTOCK' => ['count' => 0, 'monto' => 0, 'color' => '#fd7e14'],
                    'NORMOSTOCK' => ['count' => 0, 'monto' => 0, 'color' => '#28a745'],
                    'SOBRESTOCK' => ['count' => 0, 'monto' => 0, 'color' => '#6c757d'],
                    'SIN ROTACION' => ['count' => 0, 'monto' => 0, 'color' => '#6f42c1'],
                    'SIN CONSUMO' => ['count' => 0, 'monto' => 0, 'color' => '#17a2b8'],
                    'POR VENCER' => ['count' => 0, 'monto' => 0, 'color' => '#fd7e14'],
                    'VENCIDO' => ['count' => 0, 'monto' => 0, 'color' => '#dc3545']
                ];
                
                $totalMontoProyectado = 0; // 👈 Agrega esta línea
                
                foreach ($registros as $item) {
                    $stockProyectado = $item->situacion_stock_proyectado;
                    if (isset($resumenProyectado[$stockProyectado])) {
                        $resumenProyectado[$stockProyectado]['count']++;
                        $resumenProyectado[$stockProyectado]['monto'] += $item->stockfinal_proyectado * $item->precio;
                    }
                    
                    // Calcular POR VENCER proyectado
                    if ($item->sit_fecha_vcmto == 'POR VENCER' && $item->meses_para_vencimiento >= 1) {
                        $resumenProyectado['POR VENCER']['count']++;
                        $resumenProyectado['POR VENCER']['monto'] += $item->stockfinal_proyectado * $item->precio;
                    }
                    
                    // Calcular VENCIDO proyectado
                    if ($item->meses_prov > 1 && $item->meses_para_vencimiento >= 0 && $item->meses_para_vencimiento < 1) {
                        $resumenProyectado['VENCIDO']['count']++;
                        $resumenProyectado['VENCIDO']['monto'] += $item->stockfinal_proyectado * $item->precio;
                    }
                }
                
                // Calcular totales proyectados
                $baseTotalProyectado = $resumenProyectado['DESABASTECIDO']['count'] + 
                                    $resumenProyectado['SUBSTOCK']['count'] + 
                                    $resumenProyectado['NORMOSTOCK']['count'] + 
                                    $resumenProyectado['SOBRESTOCK']['count'];
                
                $totalMontoProyectado = $resumenProyectado['SUBSTOCK']['monto'] + 
                                        $resumenProyectado['NORMOSTOCK']['monto'] + 
                                        $resumenProyectado['SOBRESTOCK']['monto'] + 
                                        $resumenProyectado['SIN ROTACION']['monto'];



            @endphp
            
            <!-- Tarjeta única: Situación de Stock y Vencimiento -->
            <div class="card-resumen">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>📦 Disponibilidad</span>
                    <!-- <span class="text-muted">Total: S/ {{ number_format($totalMontoMostrar, 2) }}</span> -->
                </div>
                <div class="card-body">
                    <!-- Tabla de stock -->
                    <table class="resumen-stock-table">
                        @foreach(['DESABASTECIDO', 'SUBSTOCK', 'NORMOSTOCK', 'SOBRESTOCK', 'SIN ROTACION', 'SIN CONSUMO', 'POR VENCER', 'VENCIDO'] as $nombre)
                            @if($resumenStock[$nombre]['count'] > 0)
                            <tr>
                                <td><span class="badge" style="background-color: {{ $resumenStock[$nombre]['color'] }};">{{ $nombre }}</span></td>
                                <td class="text-end">{{ $resumenStock[$nombre]['count'] }}</td>
                                <td class="text-end">S/ {{ number_format($resumenStock[$nombre]['monto'], 2) }}</td>
                                <td class="text-end">{{ $resumenStock[$nombre]['porcentaje'] }}%</td>
                            </tr>
                            @endif
                        @endforeach
                        <tr style="border-top: 1px solid #dee2e6;">
                            <td><strong>TOTAL</strong></td>
                            <td class="text-end"><strong>{{ $totalRegistrosMostrar }}</strong></td>
                            <td class="text-end"><strong>S/ {{ number_format($totalMontoMostrar, 2) }}</strong></td>
                            <td class="text-end"><strong>{{ $sumatoriaPorcentaje }}%</strong></td>
                        </tr>
                    </table>

                    <!-- Barra y nivel -->
                    <div class="text-center mt-1 pt-1 border-top">
                        <div class="display-6 fw-bold" style="font-size: 1.3rem; color: {{ $nivelColor }};">{{ $regularPorcentaje }}%</div>
                        <div class="badge" style="background-color: {{ $nivelColor }}; font-size: 0.75rem; padding: 4px 12px;">{{ $nivel }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Cuadro: Sobrestock por Meses -->
            <div class="card-resumen">
                <div class="card-header">
                    📊 Sobrestock por Meses de Vencimiento
                    <span class="float-end" style="z-index: 1000;">
                        <i class="fas fa-info-circle text-muted" style="cursor: help;" title="Atención con los items que están en esta situación. La probabilidad de que expiren es muy alta. Realizar monitoreo mediante la validación de la información y realizar acciones para evitar vencimiento."></i>
                    </span>
                </div>
                <div class="card-body" style="padding: 8px; overflow: visible;">
                    @php
                        $rangosVencimiento = [
                            ['label' => '> 0 y ≤ 6', 'min' => 0, 'max' => 6, 'condicion' => 'exclude_min', 'color' => '#ffffff'],
                            ['label' => '> 6 y < 12', 'min' => 6, 'max' => 12, 'condicion' => 'exclude_min', 'color' => '#ffe6e6'],
                            ['label' => '≥ 12 y < 24', 'min' => 12, 'max' => 24, 'color' => '#ffcccc'],
                            ['label' => '≥ 24 y < 36', 'min' => 24, 'max' => 36, 'color' => '#ff9999'],
                            ['label' => '≥ 36 y < 48', 'min' => 36, 'max' => 48, 'color' => '#ff6666'],
                            ['label' => '≥ 48 (4 años)', 'min' => 48, 'max' => null, 'color' => '#ff0000'],
                        ];
                        
                        $totalItems = 0;
                        $totalMonto = 0;
                        $datosRangos = [];
                        
                        foreach ($rangosVencimiento as $rango) {
                            $items = $registros->filter(function($item) use ($rango) {
                                $meses = $item->meses_para_vencimiento;
                                if ($item->situacion_stock != 'SOBRESTOCK') return false;
                                if ($meses === null) return false;
                                
                                if ($rango['max'] === null) {
                                    return $meses >= $rango['min'];
                                }
                                
                                if (isset($rango['condicion']) && $rango['condicion'] == 'exclude_min') {
                                    return $meses > $rango['min'] && $meses <= $rango['max'];
                                }
                                
                                return $meses >= $rango['min'] && $meses < $rango['max'];
                            });
                            
                            $count = $items->count();
                            $monto = $items->sum('monto');
                            
                            $datosRangos[] = [
                                'label' => $rango['label'],
                                'count' => $count,
                                'monto' => $monto,
                                'color' => $rango['color']
                            ];
                            
                            $totalItems += $count;
                            $totalMonto += $monto;
                        }
                    @endphp
                    
                    <style>
                        .tooltip-mensaje {
                            position: relative;
                            cursor: pointer;
                            border-bottom: 1px dashed #ff0000;
                        }
                        .tooltip-mensaje .tooltip-texto {
                            visibility: hidden;
                            width: 280px;
                            background-color: #333;
                            color: #fff;
                            text-align: left;
                            border-radius: 6px;
                            padding: 8px 12px;
                            position: fixed;
                            z-index: 99999;
                            /* bottom: 125%; */
                            /* left: 50%; */
                            /* margin-left: -140px; */
                            font-size: 0.7rem;
                            font-weight: normal;
                            opacity: 0;
                            transition: opacity 0.3s;
                            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                            pointer-events: none;
                            white-space: normal;
                            /* word-wrap: break-word; */
                        }
                        .tooltip-mensaje .tooltip-texto{
                            content: "";
                            position: absolute;
                            top: 100%;
                            left: 50%;
                            margin-left: -5px;
                            border-width: 5px;
                            border-style: solid;
                            border-color: #333 transparent transparent transparent;
                        }
                        .tooltip-mensaje:hover .tooltip-texto {
                            visibility: visible;
                            opacity: 1;
                        }
                        .tooltip-mensaje .tooltip-texto::after {
                            content: "";
                            position: absolute;
                            top: 100%;
                            left: 50%;
                            margin-left: -5px;
                            border-width: 5px;
                            border-style: solid;
                            border-color: #333 transparent transparent transparent;
                        }
                        .tooltip-mensaje:hover .tooltip-texto {
                            visibility: visible;
                            opacity: 1;
                        }
                    </style>
                    
                    <table class="resumen-stock-table" style="width: 100%;">
                        <thead>
                            <tr style="background-color: #f8f9fa;">
                                <th>Meses para vencimiento</th>
                                <th class="text-end">Nº ITEM</th>
                                <th class="text-end">MONTO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($datosRangos as $index => $rango)
                                <tr style="background: linear-gradient(90deg, {{ $rango['color'] }} 0%, #ffffff 100%);">
                                    <td class="tooltip-mensaje">
                                        @if($index == 0)
                                            {{ $rango['label'] }}
                                            <span class="tooltip-texto">⚠️ Atención con los items que están en esta situación. La probabilidad de que expiren es muy alta. Realizar monitoreo mediante la validación de la información y realizar acciones para evitar vencimiento.</span>
                                        @else
                                            {{ $rango['label'] }}
                                        @endif
                                    </td>
                                    <td class="text-end" style="color: {{ $index == 0 ? '#ff0000' : 'inherit' }};">{{ $rango['count'] }}</td>
                                    <td class="text-end" style="color: {{ $index == 0 ? '#ff0000' : 'inherit' }};">S/ {{ number_format($rango['monto'], 2) }}</td>
                                </tr>
                            @endforeach
                            <tr style="border-top: 2px solid #dee2e6; font-weight: bold; background-color: #f8f9fa;">
                                <td>TOTAL</td>
                                <td class="text-end">{{ $totalItems }}</td>
                                <td class="text-end">S/ {{ number_format($totalMonto, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cuadro: Disponibilidad Proyectada -->
            <div class="card-resumen">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>📊 Disponibilidad Proyectada</span>
                    <!-- <span class="text-muted">Total: S/ {{ number_format($totalMontoProyectado, 2) }}</span> -->
                </div>
                <div class="card-body">
                    @php
                        // Inicializar resumen proyectado
                        $resumenProyectado = [
                            'DESABASTECIDO' => ['count' => 0, 'monto' => 0, 'color' => '#dc3545'],
                            'SUBSTOCK' => ['count' => 0, 'monto' => 0, 'color' => '#fd7e14'],
                            'NORMOSTOCK' => ['count' => 0, 'monto' => 0, 'color' => '#28a745'],
                            'SOBRESTOCK' => ['count' => 0, 'monto' => 0, 'color' => '#6c757d'],
                            'SIN ROTACION' => ['count' => 0, 'monto' => 0, 'color' => '#6f42c1'],
                            'SIN CONSUMO' => ['count' => 0, 'monto' => 0, 'color' => '#17a2b8'],
                            'POR VENCER' => ['count' => 0, 'monto' => 0, 'color' => '#fd7e14'],
                            'VENCIDO' => ['count' => 0, 'monto' => 0, 'color' => '#dc3545']
                        ];
                        
                        foreach ($registros as $item) {
                            $stockProyectado = $item->situacion_stock_proyectado;
                            if (isset($resumenProyectado[$stockProyectado])) {
                                $resumenProyectado[$stockProyectado]['count']++;
                                $resumenProyectado[$stockProyectado]['monto'] += $item->stockfinal_proyectado * $item->precio;
                            }
                            
                            // Calcular POR VENCER proyectado
                            if ($item->sit_fecha_vcmto == 'POR VENCER' && $item->meses_para_vencimiento >= 1) {
                                $resumenProyectado['POR VENCER']['count']++;
                                $resumenProyectado['POR VENCER']['monto'] += $item->stockfinal_proyectado * $item->precio;
                            }
                            
                            // Calcular VENCIDO proyectado
                            if ($item->meses_prov > 1 && $item->meses_para_vencimiento >= 0 && $item->meses_para_vencimiento < 1) {
                                $resumenProyectado['VENCIDO']['count']++;
                                $resumenProyectado['VENCIDO']['monto'] += $item->stockfinal_proyectado * $item->precio;
                            }
                        }
                        
                        // Calcular totales proyectados
                        $baseTotalProyectado = $resumenProyectado['DESABASTECIDO']['count'] + 
                                            $resumenProyectado['SUBSTOCK']['count'] + 
                                            $resumenProyectado['NORMOSTOCK']['count'] + 
                                            $resumenProyectado['SOBRESTOCK']['count'];
                        
                        $totalMontoProyectado = $resumenProyectado['SUBSTOCK']['monto'] + 
                                                $resumenProyectado['NORMOSTOCK']['monto'] + 
                                                $resumenProyectado['SOBRESTOCK']['monto'] + 
                                                $resumenProyectado['SIN ROTACION']['monto'];
                        
                        // Calcular porcentajes proyectados
                        foreach (['DESABASTECIDO', 'SUBSTOCK', 'NORMOSTOCK', 'SOBRESTOCK'] as $nombre) {
                            $resumenProyectado[$nombre]['porcentaje'] = $baseTotalProyectado > 0 ? 
                                round(($resumenProyectado[$nombre]['count'] / $baseTotalProyectado) * 100, 1) : 0;
                        }
                        
                        $baseSinRotacionProyectado = $baseTotalProyectado + $resumenProyectado['SIN ROTACION']['count'];
                        $resumenProyectado['SIN ROTACION']['porcentaje'] = $baseSinRotacionProyectado > 0 ? 
                            round(($resumenProyectado['SIN ROTACION']['count'] / $baseSinRotacionProyectado) * 100, 1) : 0;
                        
                        $baseSinConsumoProyectado = $resumenProyectado['SUBSTOCK']['count'] + 
                                                    $resumenProyectado['NORMOSTOCK']['count'] + 
                                                    $resumenProyectado['SOBRESTOCK']['count'] + 
                                                    $resumenProyectado['SIN ROTACION']['count'] + 
                                                    $resumenProyectado['SIN CONSUMO']['count'];
                        $resumenProyectado['SIN CONSUMO']['porcentaje'] = $baseSinConsumoProyectado > 0 ? 
                            round(($resumenProyectado['SIN CONSUMO']['count'] / $baseSinConsumoProyectado) * 100, 1) : 0;
                        
                        $basePorVencerProyectado = $baseTotalProyectado + $resumenProyectado['SIN ROTACION']['count'];
                        $resumenProyectado['POR VENCER']['porcentaje'] = $basePorVencerProyectado > 0 ? 
                            round(($resumenProyectado['POR VENCER']['count'] / $basePorVencerProyectado) * 100, 1) : 0;
                        $resumenProyectado['VENCIDO']['porcentaje'] = $baseTotalProyectado > 0 ? 
                            round(($resumenProyectado['VENCIDO']['count'] / $baseTotalProyectado) * 100, 1) : 0;
                        
                        $sumatoriaPorcentajeProyectado = $resumenProyectado['DESABASTECIDO']['porcentaje'] + 
                                                        $resumenProyectado['SUBSTOCK']['porcentaje'] + 
                                                        $resumenProyectado['NORMOSTOCK']['porcentaje'] + 
                                                        $resumenProyectado['SOBRESTOCK']['porcentaje'];
                        
                        // Calcular regular porcentaje para nivel
                        $regularTotalProyectado = $resumenProyectado['NORMOSTOCK']['count'] + $resumenProyectado['SOBRESTOCK']['count'];
                        $regularPorcentajeProyectado = $baseTotalProyectado > 0 ? 
                            round(($regularTotalProyectado / $baseTotalProyectado) * 100, 1) : 0;
                        
                        // Calcular nivel
                        $consumoTotalProyectado = $regularPorcentajeProyectado / 100;
                        if ($consumoTotalProyectado >= 0.9) {
                            $nivelProyectado = 'ÓPTIMO';
                            $nivelColorProyectado = '#28a745';
                        } elseif ($consumoTotalProyectado < 0.7) {
                            $nivelProyectado = 'BAJO';
                            $nivelColorProyectado = '#dc3545';
                        } elseif ($consumoTotalProyectado >= 0.8 && $consumoTotalProyectado < 0.9) {
                            $nivelProyectado = 'ALTO';
                            $nivelColorProyectado = '#17a2b8';
                        } elseif ($consumoTotalProyectado >= 0.7 && $consumoTotalProyectado < 0.8) {
                            $nivelProyectado = 'REGULAR';
                            $nivelColorProyectado = '#fd7e14';
                        } else {
                            $nivelProyectado = 'SIN CLASIFICAR';
                            $nivelColorProyectado = '#6c757d';
                        }
                    @endphp
                    
                    <table class="resumen-stock-table">
                        @foreach(['DESABASTECIDO', 'SUBSTOCK', 'NORMOSTOCK', 'SOBRESTOCK', 'SIN ROTACION', 'SIN CONSUMO', 'POR VENCER', 'VENCIDO'] as $nombre)
                            @if($resumenProyectado[$nombre]['count'] > 0)
                            <tr>
                                <td><span class="badge" style="background-color: {{ $resumenProyectado[$nombre]['color'] }};">{{ $nombre }}</span></td>
                                <td class="text-end">{{ $resumenProyectado[$nombre]['count'] }}</td>
                                <td class="text-end">S/ {{ number_format($resumenProyectado[$nombre]['monto'], 2) }}</td>
                                <td class="text-end">{{ $resumenProyectado[$nombre]['porcentaje'] }}%</td>
                            </tr>
                            @endif
                        @endforeach
                        <tr style="border-top: 1px solid #dee2e6;">
                            <td><strong>TOTAL</strong></td>
                            <td class="text-end"><strong>{{ $baseTotalProyectado }}</strong></td>
                            <td class="text-end"><strong>S/ {{ number_format($totalMontoProyectado, 2) }}</strong></td>
                            <td class="text-end"><strong>{{ $sumatoriaPorcentajeProyectado }}%</strong></td>
                        </tr>
                    </table>
                    
                    <div class="text-center mt-1 pt-1 border-top">
                        <div class="display-6 fw-bold" style="font-size: 1.3rem; color: {{ $nivelColorProyectado }};">{{ $regularPorcentajeProyectado }}%</div>
                        <div class="badge" style="background-color: {{ $nivelColorProyectado }}; font-size: 0.75rem; padding: 4px 12px;">{{ $nivelProyectado }}</div>
                    </div>
                </div>
            </div>

            <!-- Cuadro: Sobrestock por Meses (Proyectado) -->
            <div class="card-resumen">
                <div class="card-header">
                    📊 Sobrestock Proyectado por Meses de Vencimiento
                    <span class="float-end" style="z-index: 1000;">
                        <i class="fas fa-info-circle text-muted" style="cursor: help;" title="Atención con los items que están en esta situación. La probabilidad de que expiren es muy alta. Realizar monitoreo mediante la validación de la información y realizar acciones para evitar vencimiento."></i>
                    </span>
                </div>
                <div class="card-body" style="padding: 8px; overflow: visible;">
                    @php
                        $rangosVencimientoProyectado = [
                            ['label' => '> 6 y < 12', 'min' => 6, 'max' => 12, 'condicion' => 'exclude_min', 'color' => '#ffe6e6'],
                            ['label' => '≥ 12 y < 24', 'min' => 12, 'max' => 24, 'color' => '#ffcccc'],
                            ['label' => '≥ 24 y < 36', 'min' => 24, 'max' => 36, 'color' => '#ff9999'],
                            ['label' => '≥ 36 y < 48', 'min' => 36, 'max' => 48, 'color' => '#ff6666'],
                            ['label' => '≥ 48 (4 años)', 'min' => 48, 'max' => null, 'color' => '#ff0000'],
                        ];
                        
                        $totalItemsProyectado = 0;
                        $totalMontoProyectadoSobrestock = 0;
                        $datosRangosProyectado = [];
                        
                        foreach ($rangosVencimientoProyectado as $rango) {
                            $items = $registros->filter(function($item) use ($rango) {
                                $msdProyectado = $item->msd_proyectado;
                                if ($item->situacion_stock_proyectado != 'SOBRESTOCK') return false;
                                if ($msdProyectado === null) return false;
                                
                                if ($rango['max'] === null) {
                                    return $msdProyectado >= $rango['min'];
                                }
                                
                                if (isset($rango['condicion']) && $rango['condicion'] == 'exclude_min') {
                                    return $msdProyectado > $rango['min'] && $msdProyectado < $rango['max'];
                                }
                                
                                return $msdProyectado >= $rango['min'] && $msdProyectado < $rango['max'];
                            });
                            
                            $count = $items->count();
                            $monto = $items->sum(function($item) {
                                return $item->stockfinal_proyectado * $item->precio;
                            });
                            
                            $datosRangosProyectado[] = [
                                'label' => $rango['label'],
                                'count' => $count,
                                'monto' => $monto,
                                'color' => $rango['color']
                            ];
                            
                            $totalItemsProyectado += $count;
                            $totalMontoProyectadoSobrestock += $monto;
                        }
                    @endphp
                    
                    <table class="resumen-stock-table" style="width: 100%;">
                        <thead>
                            <tr style="background-color: #f8f9fa;">
                                <th>Meses para vencimiento</th>
                                <th class="text-end">Nº ITEM</th>
                                <th class="text-end">MONTO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($datosRangosProyectado as $index => $rango)
                                <tr style="background: linear-gradient(90deg, {{ $rango['color'] }} 0%, #ffffff 100%);">
                                    <td>{{ $rango['label'] }}</td>
                                    <td class="text-end">{{ $rango['count'] }}</td>
                                    <td class="text-end">S/ {{ number_format($rango['monto'], 2) }}</td>
                                </tr>
                            @endforeach
                            <tr style="border-top: 2px solid #dee2e6; font-weight: bold; background-color: #f8f9fa;">
                                <td>TOTAL</td>
                                <td class="text-end">{{ $totalItemsProyectado }}</td>
                                <td class="text-end">S/ {{ number_format($totalMontoProyectadoSobrestock, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
    
    <!-- Tabla principal -->
    <div class="mt-2 text-end">
        <small class="text-muted">Total de registros: {{ $registros->count() }}</small>
        <button id="btn-exportar-excel" class="btn btn-success btn-sm">📥 Exportar a Excel</button>
        <button type="button" id="btn-limpiar-filtros-columna" class="btn btn-outline-secondary btn-sm">🧹 Limpiar Filtros de Columna</button>
    </div>
    <div class="table-responsive">
        <table id="registros" class="table table-bordered table-hover table-striped">
            <thead>
                <tr>
                    <th>DESC PROD</th>
                    <th>COD_SISMED</th>
                    <th>TIPO PROD</th>
                    <th>TIPO ABAST</th>
                    <th>MES1</th>
                    <th>MES2</th>
                    <th>MES3</th>
                    <th>MES4</th>
                    <th>MES5</th>
                    <th>MES6</th>
                    <th>MES7</th>
                    <th>MES8</th>
                    <th>MES9</th>
                    <th>MES10</th>
                    <th>MES11</th>
                    <th>MES12</th>
                    <th>STOCK_FINAL</th>
                    <th>FECHA_VCMTO.</th>
                    <th>CONSUMO_TOTAL</th>
                    <th>CPMA</th>
                    <th>CONSUMO_ÚLT.4MESES</th>
                    <th>MSD(MES_PROV.)</th>
                    <th>PRECIO_UNIT</th>
                    <th>MONTO</th>
                    <th class="vencimiento">SIT.STOCK</th>
                    <th class="vencimiento">MESES_PARA_VCMTO.</th>
                    <th class="vencimiento">SIT.FECH_VCMTO.</th>
                    <th>MINIGRÁF.</th>
                    <th class="distribucion">DIST.1</th>
                    <th class="distribucion">INGRESO_ICI</th>
                    <th class="distribucion">PEND.ING.ICI</th>
                    <th class="distribucion">DIST.2</th>
                    <th class="proyectado">CONSUMO_PROYEC.</th>
                    <th class="proyectado">STOCK_PROYEC.</th>
                    <th class="proyectado">CPMA PROYEC.</th>
                    <th class="proyectado">CONSUMO_4M_PROYEC.</th>
                    <th class="proyectado">MSD PROYEC.</th>
                    <th class="proyectado">
                        <div class="th-filter">
                            <span>SIT.STOCK_PROYEC</span>
                            <span class="filter-icon" data-columna="sit_stock_proyec" data-titulo="Situación Stock Proyectado">
                                🔽
                            </span>
                        </div>
                    </th>
                    <th class="envio-sugerido">
                        <div class="th-filter">
                            <span>ENVÍO SUGERIDO</span>
                            <span class="filter-icon" data-columna="envio_sugerido" data-titulo="Envío Sugerido">
                                🔽
                            </span>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($registros as $reg)
                <tr>
                    <td>{{ $reg->descripcion_producto }}</td>
                    <td>{{ $reg->cod_sismed }}</td>
                    <td>{{ $reg->tipo_prod }}</td>
                    <td>{{ $reg->tipo_abastecimiento }}</td>
                    <td class="text-end">{{ $reg->Mes1 }}</td>
                    <td class="text-end">{{ $reg->Mes2 }}</td>
                    <td class="text-end">{{ $reg->Mes3 }}</td>
                    <td class="text-end">{{ $reg->Mes4 }}</td>
                    <td class="text-end">{{ $reg->Mes5 }}</td>
                    <td class="text-end">{{ $reg->Mes6 }}</td>
                    <td class="text-end">{{ $reg->Mes7 }}</td>
                    <td class="text-end">{{ $reg->Mes8 }}</td>
                    <td class="text-end">{{ $reg->Mes9 }}</td>
                    <td class="text-end">{{ $reg->Mes10 }}</td>
                    <td class="text-end">{{ $reg->Mes11 }}</td>
                    <td class="text-end">{{ $reg->Mes12 }}</td>
                    <td class="text-end">{{ number_format($reg->StockFinal, 2) }}</td>
                    <td>{{ $reg->fec_exp }}</td>
                    <td class="text-end">{{ number_format($reg->consumo_total, 2) }}</td>
                    <td class="text-end">{{ number_format($reg->cpma, 2) }}</td>
                    <td class="text-end">{{ number_format($reg->consumo_ultimos_4meses, 2) }}</td>
                    <td class="text-end">{{ number_format($reg->meses_prov, 2) }}</td>
                    <td class="text-end">{{ number_format($reg->precio, 2) }}</td>
                    <td class="text-end">{{ number_format($reg->monto, 2) }}</td>
                    <td>
                        <span class="badge" style="background-color: 
                            @switch($reg->situacion_stock)
                            @case('DESABASTECIDO') #dc3545 @break
                            @case('SUBSTOCK') #fd7e14 @break
                            @case('NORMOSTOCK') #28a745 @break
                            @case('SOBRESTOCK') #6c757d @break
                            @case('SIN ROTACION') #6f42c1 @break
                            @case('SIN CONSUMO') #17a2b8 @break
                            @default #6c757d
                            @endswitch
                            ">{{ $reg->situacion_stock }}</span>
                    </td>
                    <td class="text-end">{{ $reg->meses_para_vencimiento }}</td>
                    <td>{{ $reg->sit_fecha_vcmto }}</td>
                    <td class="minigrafico-cell">
                        <div class="minigrafico" data-meses="{{ $reg->Mes1 }},{{ $reg->Mes2 }},{{ $reg->Mes3 }},{{ $reg->Mes4 }},{{ $reg->Mes5 }},{{ $reg->Mes6 }},{{ $reg->Mes7 }},{{ $reg->Mes8 }},{{ $reg->Mes9 }},{{ $reg->Mes10 }},{{ $reg->Mes11 }},{{ $reg->Mes12 }}">
                        </div>
                    </td>
                    <td class="text-end">{{ number_format($reg->dist1, 2) }}</td>
                    <td class="text-end">{{ number_format($reg->ingre, 2) }}</td>
                    <td class="text-end">{{ number_format($reg->pendingre_ici, 2) }}</td>
                    <td class="text-end">{{ number_format($reg->dist2, 2) }}</td>
                    <td class="text-end">{{ number_format($reg->consumo_total_proyectado, 2) }}</td>
                    <td class="text-end">{{ number_format($reg->stockfinal_proyectado, 2) }}</td>
                    <td class="text-end">{{ number_format($reg->cpma_proyectado, 2) }}</td>
                    <td class="text-end">{{ number_format($reg->consumo_cuatro_ult_meses_proyectado, 2) }}</td>
                    <td class="text-end">{{ number_format($reg->msd_proyectado, 2) }}</td>
                    <td>{{ $reg->situacion_stock_proyectado }}</td>
                    <td class="text-end">{{ number_format($reg->envio_sugerido, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    
</div>
@endsection

@section('scripts')
    <!-- Agrega esto en la sección de scripts, antes de tu código -->
    <script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script src="{{ asset('js/select2-focus.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                placeholder: "Escriba para buscar...",
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: '{{ route("matriz.search") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term,
                            tipo: $(this).attr('id')
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });

            $('.select2-multiple').select2({
                placeholder: "Seleccione opciones...",
                allowClear: true,
                width: '100%'
            });

            // Filtrar al hacer clic
            $('#btn-filtrar').on('click', function() {
                let cod_ipress = $('#cod_ipress').val();
                let cod_sismed = $('#cod_sismed').val();
                let fechaFinMes = $('#fechaFinMes').val();

                if (!cod_ipress) {
                    alert('Debe seleccionar un IPRESS...');
                    return;
                }

                if (!fechaFinMes) {
                    alert('Debe seleccionar una fecha de fin de mes...');
                    return;
                }

                let url = new URL(window.location.href);
                
                url.searchParams.delete('tip_sum[]');
                url.searchParams.delete('tipo_prod[]');
                url.searchParams.delete('tipo_abastecimiento[]');
                url.searchParams.delete('tipo_establecimiento[]');
                url.searchParams.delete('peti2023[]');
                url.searchParams.delete('lista_1[]');
                
                url.searchParams.set('cod_ipress', cod_ipress);
                url.searchParams.set('fin_mes', fechaFinMes);
                
                if (cod_sismed) {
                    url.searchParams.set('cod_sismed', cod_sismed);
                } else {
                    url.searchParams.delete('cod_sismed');
                }
                
                $('#tip_sum').val().forEach(value => {
                    url.searchParams.append('tip_sum[]', value);
                });
                
                $('#tipo_prod').val().forEach(value => {
                    url.searchParams.append('tipo_prod[]', value);
                });
                
                $('#tipo_abastecimiento').val().forEach(value => {
                    url.searchParams.append('tipo_abastecimiento[]', value);
                });
                
                $('#tipo_establecimiento').val().forEach(value => {
                    url.searchParams.append('tipo_establecimiento[]', value);
                });
                
                $('#peti2023').val().forEach(value => {
                    url.searchParams.append('peti2023[]', value);
                });
                
                $('#lista_1').val().forEach(value => {
                    url.searchParams.append('lista_1[]', value);
                });

                window.location.href = url.toString();
            });

            $('#btn-limpiar-filtros').on('click', function() {
                $('.select2-multiple').val(null).trigger('change');
                
                let cod_ipress = $('#cod_ipress').val();
                let cod_sismed = $('#cod_sismed').val();
                let fechaFinMes = $('#fechaFinMes').val();
                
                if (cod_ipress && fechaFinMes) {
                    let url = new URL(window.location.href);
                    url.search = `?cod_ipress=${cod_ipress}&fin_mes=${fechaFinMes}`;
                    if (cod_sismed) {
                        url.search += `&cod_sismed=${cod_sismed}`;
                    }
                    window.location.href = url.toString();
                }
            });
        });
        
        function obtenerFinMesActual() {
            const hoy = new Date();
            const año = hoy.getFullYear();
            const mes = hoy.getMonth();
            const ultimoDia = new Date(año, mes + 1, 0);
            const yyyy = ultimoDia.getFullYear();
            const mm = String(ultimoDia.getMonth() + 1).padStart(2, '0');
            const dd = String(ultimoDia.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        function generarMinigraficos() {
            document.querySelectorAll('.minigrafico').forEach(function(container) {
                const mesesData = container.getAttribute('data-meses').split(',').map(Number);
                const maxValor = Math.max(...mesesData, 1);
                
                container.innerHTML = '';
                
                mesesData.forEach(function(valor) {
                    const barra = document.createElement('div');
                    barra.className = 'barra-mini';
                    const altura = Math.max(3, Math.min(25, (valor / maxValor) * 25));
                    barra.style.height = altura + 'px';
                    barra.setAttribute('data-valor', valor);
                    
                    if (valor === 0) {
                        barra.classList.add('valor-cero');
                    } else if (valor < maxValor * 0.3) {
                        barra.classList.add('valor-bajo');
                    } else if (valor < maxValor * 0.7) {
                        barra.classList.add('valor-medio');
                    } else {
                        barra.classList.add('valor-alto');
                    }
                    
                    container.appendChild(barra);
                });
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            generarMinigraficos();
            
            const inputFecha = document.getElementById('fechaFinMes');
            if (!inputFecha.value) {
                inputFecha.value = obtenerFinMesActual();
            }
        });
    </script>
    
    <script>
        // Navegación por teclado y selección de celdas
        let filaSeleccionada = null;
        let celdaSeleccionada = null;
        let indiceFila = -1;
        let indiceColumna = -1;

        function seleccionarFila(fila, evento) {
            if (filaSeleccionada) {
                filaSeleccionada.classList.remove('seleccionada');
                filaSeleccionada.querySelectorAll('td').forEach(td => {
                    td.classList.remove('seleccionada');
                });
            }
            
            fila.classList.add('seleccionada');
            filaSeleccionada = fila;
            
            const filas = Array.from(document.querySelectorAll('#registros tbody tr'));
            indiceFila = filas.indexOf(fila);
            
            if (evento && evento.target.tagName === 'TD') {
                const celdas = Array.from(fila.children);
                indiceColumna = celdas.indexOf(evento.target);
                evento.target.classList.add('seleccionada');
                celdaSeleccionada = evento.target;
                
                const contenedorTabla = document.querySelector('.table-responsive');
                if (contenedorTabla) {
                    const celdaOffset = evento.target.offsetLeft;
                    const celdaWidth = evento.target.offsetWidth;
                    const contenedorScrollLeft = contenedorTabla.scrollLeft;
                    const contenedorWidth = contenedorTabla.clientWidth;
                    
                    if (celdaOffset < contenedorScrollLeft || 
                        celdaOffset + celdaWidth > contenedorScrollLeft + contenedorWidth) {
                        evento.target.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'nearest',
                            inline: 'center'
                        });
                    }
                }
            } else {
                indiceColumna = -1;
                celdaSeleccionada = null;
            }
            
            fila.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function navegarConTeclado(e) {
            if (!filaSeleccionada) return;
            
            const filas = Array.from(document.querySelectorAll('#registros tbody tr'));
            const totalFilas = filas.length;
            const totalColumnas = document.querySelectorAll('#registros thead th').length;
            const contenedorTabla = document.querySelector('.table-responsive');
            
            switch(e.key) {
                case 'ArrowUp':
                    e.preventDefault();
                    if (indiceFila > 0) {
                        indiceFila--;
                        seleccionarFila(filas[indiceFila]);
                    }
                    break;
                    
                case 'ArrowDown':
                    e.preventDefault();
                    if (indiceFila < totalFilas - 1) {
                        indiceFila++;
                        seleccionarFila(filas[indiceFila]);
                    }
                    break;
                    
                case 'ArrowLeft':
                    e.preventDefault();
                    if (indiceColumna > 0) {
                        indiceColumna--;
                        if (celdaSeleccionada) {
                            celdaSeleccionada.classList.remove('seleccionada');
                        }
                        const nuevaCelda = filaSeleccionada.children[indiceColumna];
                        nuevaCelda.classList.add('seleccionada');
                        celdaSeleccionada = nuevaCelda;
                        
                        if (contenedorTabla) {
                            const celdaOffset = nuevaCelda.offsetLeft;
                            const celdaWidth = nuevaCelda.offsetWidth;
                            const contenedorScrollLeft = contenedorTabla.scrollLeft;
                            const contenedorWidth = contenedorTabla.clientWidth;
                            
                            if (celdaOffset < contenedorScrollLeft) {
                                contenedorTabla.scrollLeft = celdaOffset - 10;
                            } else if (celdaOffset + celdaWidth > contenedorScrollLeft + contenedorWidth) {
                                contenedorTabla.scrollLeft = celdaOffset + celdaWidth - contenedorWidth + 10;
                            }
                        }
                    }
                    break;
                    
                case 'ArrowRight':
                    e.preventDefault();
                    if (indiceColumna < totalColumnas - 1) {
                        indiceColumna++;
                        if (celdaSeleccionada) {
                            celdaSeleccionada.classList.remove('seleccionada');
                        }
                        const nuevaCelda = filaSeleccionada.children[indiceColumna];
                        nuevaCelda.classList.add('seleccionada');
                        celdaSeleccionada = nuevaCelda;
                        
                        if (contenedorTabla) {
                            const celdaOffset = nuevaCelda.offsetLeft;
                            const celdaWidth = nuevaCelda.offsetWidth;
                            const contenedorScrollLeft = contenedorTabla.scrollLeft;
                            const contenedorWidth = contenedorTabla.clientWidth;
                            
                            if (celdaOffset < contenedorScrollLeft) {
                                contenedorTabla.scrollLeft = celdaOffset - 10;
                            } else if (celdaOffset + celdaWidth > contenedorScrollLeft + contenedorWidth) {
                                contenedorTabla.scrollLeft = celdaOffset + celdaWidth - contenedorWidth + 10;
                            }
                        }
                    }
                    break;
                    
                case 'Home':
                    e.preventDefault();
                    if (e.ctrlKey) {
                        indiceFila = 0;
                        seleccionarFila(filas[0]);
                    } else if (filaSeleccionada) {
                        indiceColumna = 0;
                        if (celdaSeleccionada) {
                            celdaSeleccionada.classList.remove('seleccionada');
                        }
                        const primeraCelda = filaSeleccionada.children[0];
                        primeraCelda.classList.add('seleccionada');
                        celdaSeleccionada = primeraCelda;
                        if (contenedorTabla) contenedorTabla.scrollLeft = 0;
                    }
                    break;
                    
                case 'End':
                    e.preventDefault();
                    if (e.ctrlKey) {
                        indiceFila = totalFilas - 1;
                        seleccionarFila(filas[totalFilas - 1]);
                    } else if (filaSeleccionada) {
                        indiceColumna = totalColumnas - 1;
                        if (celdaSeleccionada) {
                            celdaSeleccionada.classList.remove('seleccionada');
                        }
                        const ultimaCelda = filaSeleccionada.children[totalColumnas - 1];
                        ultimaCelda.classList.add('seleccionada');
                        celdaSeleccionada = ultimaCelda;
                        if (contenedorTabla) contenedorTabla.scrollLeft = contenedorTabla.scrollWidth;
                    }
                    break;
                    
                case 'PageDown':
                    e.preventDefault();
                    indiceFila = Math.min(totalFilas - 1, indiceFila + 10);
                    seleccionarFila(filas[indiceFila]);
                    break;
                    
                case 'PageUp':
                    e.preventDefault();
                    indiceFila = Math.max(0, indiceFila - 10);
                    seleccionarFila(filas[indiceFila]);
                    break;
            }
        }

        function manejarDobleClic(e) {
            if (e.target.tagName === 'TD') {
                const fila = e.target.closest('tr');
                const codSismed = fila.querySelector('td:nth-child(4)')?.textContent;
                alert(`Producto seleccionado: ${codSismed}`);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const tabla = document.getElementById('registros');
            if (!tabla) return;
            
            const tbody = tabla.querySelector('tbody');
            
            tbody.querySelectorAll('tr').forEach(fila => {
                fila.addEventListener('click', function(e) {
                    seleccionarFila(this, e);
                });
            });
            
            tbody.addEventListener('dblclick', manejarDobleClic);
            document.addEventListener('keydown', navegarConTeclado);
        });
    </script>
    
    <script>
        // Exportar tabla a Excel
        document.getElementById('btn-exportar-excel').addEventListener('click', function() {
            // Obtener la tabla
            const tabla = document.getElementById('registros');
            
            // Crear una copia de la tabla para exportar (sin los selectores visuales)
            const tablaExportar = tabla.cloneNode(true);
            
            // Remover clases de selección y estilos visuales
            tablaExportar.querySelectorAll('.seleccionada').forEach(el => {
                el.classList.remove('seleccionada');
            });
            
            // Remover tooltips y elementos extra
            tablaExportar.querySelectorAll('.minigrafico-cell .minigrafico').forEach(el => {
                // Reemplazar el minigráfico con texto "Gráfico"
                const textNode = document.createTextNode('Ver gráfico');
                el.parentNode.replaceChild(textNode, el);
            });
            
            // Crear el contenido HTML para exportar
            const html = `
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>Matriz de Disponibilidad</title>
                    <style>
                        th, td {
                            border: 1px solid #ddd;
                            padding: 5px;
                            text-align: left;
                        }
                        th {
                            background-color: #f2f2f2;
                        }
                        table {
                            border-collapse: collapse;
                            width: 100%;
                        }
                    </style>
                </head>
                <body>
                    <h2>Matriz de Disponibilidad</h2>
                    <p>Fecha de exportación: ${new Date().toLocaleString()}</p>
                    ${tablaExportar.outerHTML}
                </body>
                </html>
            `;
            
            // Crear blob y descargar
            const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.href = url;
            link.download = `matriz_disponibilidad_${new Date().toISOString().slice(0,19).replace(/:/g, '-')}.xls`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(url);
        });
    </script>

    <script>
        // Filtro por columna
        $(document).ready(function() {
            let filtroActivo = {};
            
            // Obtener valores únicos de la columna
            function obtenerValoresUnicos(columna) {
                const valores = new Set();
                $('#registros tbody tr').each(function() {
                    let valor;
                    if (columna === 'envio_sugerido') {
                        valor = $(this).find('td:last').text().trim();
                    } else if (columna === 'sit_stock_proyec') {
                        // La columna SIT.STOCK_PROYEC está en la penúltima posición
                        // Ajusta el índice según tu tabla
                        valor = $(this).find('td:nth-last-child(2)').text().trim();
                    }
                    if (valor && valor !== '') {
                        valores.add(valor);
                    }
                });
                return Array.from(valores).sort();
            }
            
            // Aplicar filtro a la tabla
            function aplicarFiltroColumna(columna, valoresSeleccionados) {
                $('#registros tbody tr').each(function() {
                    let mostrar = true;
                    let valor;
                    
                    if (columna === 'envio_sugerido') {
                        valor = $(this).find('td:last').text().trim();
                    } else if (columna === 'sit_stock_proyec') {
                        valor = $(this).find('td:nth-last-child(2)').text().trim();
                    }
                    
                    if (valoresSeleccionados && valoresSeleccionados.length > 0) {
                        if (!valoresSeleccionados.includes(valor)) {
                            mostrar = false;
                        }
                    }
                    
                    $(this).toggle(mostrar);
                });
                
                // Actualizar contador de registros visibles
                const visibles = $('#registros tbody tr:visible').length;
                const total = $('#registros tbody tr').length;
                $('.total-registros-filtrados').remove();
                $('.table-responsive').before(`<div class="total-registros-filtrados small text-muted mb-1">Mostrando ${visibles} de ${total} registros</div>`);
                
                // Guardar estado del filtro
                if (valoresSeleccionados && valoresSeleccionados.length > 0) {
                    filtroActivo[columna] = valoresSeleccionados;
                    $(`.filter-icon[data-columna="${columna}"]`).addClass('active');
                } else {
                    delete filtroActivo[columna];
                    $(`.filter-icon[data-columna="${columna}"]`).removeClass('active');
                }
            }
            
            // Crear y mostrar modal de filtro
            function mostrarModalFiltro(columna, titulo, event) {
                // Eliminar modal existente
                $('.filter-modal').remove();
                
                const valores = obtenerValoresUnicos(columna);
                const valoresActivos = filtroActivo[columna] || [];
                
                const modal = $(`
                    <div class="filter-modal">
                        <div class="filter-modal-header">
                            <span>Filtrar por ${titulo}</span>
                            <span class="filter-icon-close" style="cursor:pointer;">✕</span>
                        </div>
                        <div class="filter-modal-body">
                            <div class="filter-option" style="border-bottom: 1px solid #e9ecef; margin-bottom: 5px;">
                                <input type="checkbox" id="select-all-${columna}" class="select-all">
                                <label for="select-all-${columna}">Seleccionar todos</label>
                            </div>
                            <div class="filter-options-list">
                                ${valores.map(valor => `
                                    <div class="filter-option">
                                        <input type="checkbox" class="filter-checkbox" value="${valor}" ${valoresActivos.includes(valor) ? 'checked' : ''}>
                                        <label>${valor}</label>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                        <div class="filter-modal-footer">
                            <button class="btn btn-sm btn-secondary btn-filter-sm limpiar-filtro">Limpiar</button>
                            <button class="btn btn-sm btn-primary btn-filter-sm aplicar-filtro">Aplicar</button>
                        </div>
                    </div>
                `);
                
                // Posicionar modal
                const $icon = $(event.target).closest('.filter-icon');
                const offset = $icon.offset();
                modal.css({
                    top: offset.top + $icon.outerHeight() + 5,
                    left: offset.left - 150,
                    display: 'block'
                });
                
                $('body').append(modal);
                
                // Eventos del modal
                modal.find('.filter-icon-close, .btn-secondary').on('click', function() {
                    modal.remove();
                });
                
                modal.find('.select-all').on('change', function() {
                    const checked = $(this).is(':checked');
                    modal.find('.filter-checkbox').prop('checked', checked);
                });
                
                modal.find('.limpiar-filtro').on('click', function() {
                    modal.find('.filter-checkbox').prop('checked', false);
                    modal.find('.select-all').prop('checked', false);
                });
                
                modal.find('.aplicar-filtro').on('click', function() {
                    const seleccionados = modal.find('.filter-checkbox:checked').map(function() {
                        return $(this).val();
                    }).get();
                    
                    aplicarFiltroColumna(columna, seleccionados);
                    modal.remove();
                });
                
                // Cerrar al hacer clic fuera
                $(document).one('click', function(e) {
                    if (!$(e.target).closest('.filter-modal').length && !$(e.target).closest('.filter-icon').length) {
                        modal.remove();
                    }
                });
            }
            
            // Evento clic en icono de filtro
            $(document).on('click', '.filter-icon', function(e) {
                e.stopPropagation();
                const columna = $(this).data('columna');
                const titulo = $(this).data('titulo');
                mostrarModalFiltro(columna, titulo, e);
            });
        });

        // Limpiar todos los filtros de columna
        $('#btn-limpiar-filtros-columna').on('click', function() {
            filtroActivo = {};
            $('#registros tbody tr').show();
            $('.filter-icon').removeClass('active');
            $('.total-registros-filtrados').remove();
            $('.filter-badge').remove();
        });

    </script>                    

@endsection