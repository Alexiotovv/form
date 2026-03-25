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
        h4 {
            font-size: 1.2rem !important;
            margin-bottom: 0.5rem !important;
        }
        
        /* Achicar labels */
        label {
            font-size: 0.75rem !important;
            margin-bottom: 0.1rem !important;
            font-weight: 500;
        }
        
        /* Achicar inputs y selects */
        .form-control, .form-select {
            font-size: 0.75rem !important;
            padding: 0.25rem 0.4rem !important;
            height: auto !important;
        }
        
        /* Achicar los select2 */
        .select2-container .select2-selection--single {
            height: 28px !important;
            font-size: 0.75rem !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px !important;
            font-size: 0.75rem !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 26px !important;
        }
        
        /* Achicar botones */
        .btn, .btn-sm {
            font-size: 0.7rem !important;
            padding: 0.2rem 0.6rem !important;
        }
        
        /* Achicar la tabla - ESTO ES LO MÁS IMPORTANTE */
        .table {
            font-size: 0.65rem !important; /* Fuente muy pequeña para la tabla */
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
            white-space: nowrap; /* Evitar que el texto se rompa */
        }
        
        /* Reducir márgenes entre filas */
        .row {
            margin-bottom: 0.3rem !important;
        }
        
        .mb-3, .mb-4 {
            margin-bottom: 0.5rem !important;
        }
        
        /* Achicar el input de fecha */
        #fechaFinMes {
            width: 180px !important;
            font-size: 0.75rem !important;
        }
        
        /* Contenedor de la tabla con scroll */
        /* .table-container {
            overflow-x: auto;
            margin-top: 0.5rem;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 0;
        } */

            /* === NUEVO: Contenedor con altura fija y scroll vertical === */
        .table-responsive {
            max-height: 400px; /* Ajusta esta altura según necesites */
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            position: relative;
        }
        
        /* Mantener el encabezado fijo */
        .table-responsive thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* Ajustes para la tabla dentro del contenedor con scroll */
        .table-responsive table {
            margin-bottom: 0;
            font-size: 0.65rem !important;
        }
        
        .table-responsive table th {
            font-size: 0.6rem !important;
            padding: 0.3rem 0.2rem !important;
            white-space: nowrap;
            font-weight: 600;
        }
        
        .table-responsive table td {
            padding: 0.2rem 0.2rem !important;
            white-space: nowrap;
        }
        
        /* Eliminar bordes duplicados */
        .table-responsive {
            border-collapse: collapse;
        }
        
        /* Paginación más pequeña */
        .pagination {
            font-size: 0.7rem !important;
            margin-top: 0.5rem !important;
        }
        
        .pagination .page-link {
            padding: 0.2rem 0.5rem !important;
            font-size: 0.7rem !important;
        }
        
        /* Opcional: colores alternados en la tabla para mejor legibilidad */
        .table-striped > tbody > tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        /* Hover más sutil */
        .table-hover > tbody > tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }
        
        /* Columnas numéricas alineadas a la derecha (opcional) */
        .table td:nth-child(17), /* Mes1 */
        .table td:nth-child(18), /* Mes2 */
        .table td:nth-child(19), /* Mes3 */
        .table td:nth-child(20), /* Mes4 */
        .table td:nth-child(21), /* Mes5 */
        .table td:nth-child(22), /* Mes6 */
        .table td:nth-child(23), /* Mes7 */
        .table td:nth-child(24), /* Mes8 */
        .table td:nth-child(25), /* Mes9 */
        .table td:nth-child(26), /* Mes10 */
        .table td:nth-child(27), /* Mes11 */
        .table td:nth-child(28), /* Mes12 */
        .table td:nth-child(29), /* StockFinal */
        .table td:nth-child(30), /* Ingreso ICI */
        .table td:nth-child(32), /* Consumo Total */
        .table td:nth-child(33), /* CPMA */
        .table td:nth-child(34), /* Consumo Últimos 4meses */
        .table td:nth-child(35), /* MesesProv */
        .table td:nth-child(36), /* Precio_Unitario */
        .table td:nth-child(37) { /* Monto */
            text-align: right;
            padding-right: 0.5rem !important;
        }

        /* Minigráfico */
        .minigrafico-cell {
            padding: 2px !important;
            min-width: 100px;
        }

        .minigrafico {
            display: flex;
            align-items: flex-end;
            height: 30px;
            gap: 2px;
            padding: 2px 0;
        }

        .barra-mini {
            flex: 1;
            background-color: #4299e1;
            min-width: 3px;
            border-radius: 2px 2px 0 0;
            transition: height 0.2s ease;
        }

        /* Diferentes colores según el valor (opcional) */
        .barra-mini[data-valor="0"] {
            background-color: #cbd5e0;
        }

        .barra-mini.valor-bajo {
            background-color: #fbbf24; /* Amarillo para valores bajos */
        }

        .barra-mini.valor-medio {
            background-color: #4299e1; /* Azul para valores medios */
        }

        .barra-mini.valor-alto {
            background-color: #48bb78; /* Verde para valores altos */
        }

        /* Tooltip con el valor exacto */
        .barra-mini {
            position: relative;
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

        .barra-mini:hover::before {
            content: '';
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 4px;
            border-style: solid;
            border-color: #2d3748 transparent transparent transparent;
            margin-bottom: -4px;
            z-index: 20;
        }

        /* Estilos para filtros múltiples */
        .select2-multiple {
            /* width: 100% !important; */
        }

        .select2-container--default .select2-selection--multiple {
            font-size: 0.75rem !important;
            min-height: 28px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            font-size: 0.7rem !important;
            padding: 1px 4px !important;
            margin: 2px !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            font-size: 0.75rem !important;
        }

        /* Botón para limpiar filtros */
        .btn-limpiar-filtros {
            font-size: 0.7rem !important;
            padding: 0.2rem 0.5rem !important;
            margin-left: 5px;
        }

        /* Tooltip para filtros activos */
        .filtros-activos {
            font-size: 0.7rem;
            padding: 5px;
            background-color: #e9ecef;
            border-radius: 4px;
            margin-bottom: 10px;
        }

        .filtro-badge {
            display: inline-block;
            background-color: #007bff;
            color: white;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 10px;
            margin-right: 5px;
            margin-bottom: 3px;
        }

        /* Estilos para fila seleccionada */
        .table tbody tr.seleccionada {
            background-color: #cfe2ff !important; /* Azul claro */
            outline: 2px solid #0d6efd;
            outline-offset: -2px;
        }

        /* Estilo para celda seleccionada (opcional) */
        .table tbody tr.seleccionada td.seleccionada {
            background-color: #9ec5fe !important;
            outline: 2px solid #0a58ca;
            outline-offset: -2px;
        }

        /* Cambiar cursor para indicar que es seleccionable */
        .table tbody tr {
            cursor: pointer;
        }

        /* Estilo para cuando se pasa el mouse */
        .table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.1);
        }

    </style>

@endsection

@section('content')
    <!-- Filtros en la parte superior de tu index -->
    <div class="row">
        <div class="col-md-3">
            <label for="fechaFinMes">Fin de Mes</label>
            <input 
                type="date"
                id="fechaFinMes"
                name="fin_mes"
                class="form-control"
                value="{{ request('fin_mes') ?? date('Y-m-t') }}">
        </div>
    
        <div class="col-md-3">
            <label for="cod_ipress" class="form-label">Código IPRESS <span class="text-danger">*</span></label>
            <select id="cod_ipress" name="cod_ipress" class="form-control select2" required>
                <option value="">Seleccione un IPRESS</option>
                @if(request('cod_ipress'))
                    <option value="{{ request('cod_ipress') }}" selected>{{ request('cod_ipress') }} - {{ optional($registros->first())->nombre_ipress ?? 'Cargando...' }}</option>
                @endif
            </select>
        </div>
        <div class="col-md-3">
            <label for="cod_sismed" class="form-label">Código SISMED</label>
            <select id="cod_sismed" name="cod_sismed" class="form-control select2">
                <option value="">Todos los productos</option>
                @if(request('cod_sismed'))
                    <option value="{{ request('cod_sismed') }}" selected>{{ request('cod_sismed') }} - {{ optional($registros->first())->descripcion_producto ?? 'Cargando...' }}</option>
                @endif
            </select>
        </div>

        <!-- NUEVOS FILTROS MÚLTIPLES -->
        <div class="row mt-3">
            <div class="col-md-2">
                <label for="tip_sum" class="form-label">TIPSUM</label>
                <select id="tip_sum" name="tip_sum[]" class="form-control select2-multiple" multiple="multiple">
                    @php
                        $valoresTipSum = $registros->isNotEmpty() ? $registros->pluck('TIPSUM')->unique()->filter()->values() : collect([]);
                    @endphp
                    {{-- Agregar opción para valores vacíos/nulos --}}
                    @if($registros->whereNull('TIPSUM')->count() > 0 || $registros->where('TIPSUM', '')->count() > 0)
                        <option value="__NULL__" {{ in_array('__NULL__', request('tip_sum', [])) ? 'selected' : '' }}>
                            [En blanco]
                        </option>
                    @endif
                    @foreach($valoresTipSum as $valor)
                        <option value="{{ $valor }}" {{ in_array($valor, request('tip_sum', [])) ? 'selected' : '' }}>
                            {{ $valor }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="tipo_prod" class="form-label">Tipo Producto</label>
                <select id="tipo_prod" name="tipo_prod[]" class="form-control select2-multiple" multiple="multiple">
                    @php
                        $valoresTipoProd = $registros->isNotEmpty() ? $registros->pluck('tipo_prod')->unique()->filter()->values() : collect([]);
                    @endphp
                    @if($registros->whereNull('tipo_prod')->count() > 0 || $registros->where('tipo_prod', '')->count() > 0)
                        <option value="__NULL__" {{ in_array('__NULL__', request('tipo_prod', [])) ? 'selected' : '' }}>
                            [En blanco]
                        </option>
                    @endif
                    @foreach($valoresTipoProd as $valor)
                        <option value="{{ $valor }}" {{ in_array($valor, request('tipo_prod', [])) ? 'selected' : '' }}>
                            {{ $valor }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="tipo_abastecimiento" class="form-label">Tipo Abastecimiento</label>
                <select id="tipo_abastecimiento" name="tipo_abastecimiento[]" class="form-control select2-multiple" multiple="multiple">
                    @php
                        $valoresTipoAbast = $registros->isNotEmpty() ? $registros->pluck('tipo_abastecimiento')->unique()->filter()->values() : collect([]);
                    @endphp
                    @if($registros->whereNull('tipo_abastecimiento')->count() > 0 || $registros->where('tipo_abastecimiento', '')->count() > 0)
                        <option value="__NULL__" {{ in_array('__NULL__', request('tipo_abastecimiento', [])) ? 'selected' : '' }}>
                            [En blanco]
                        </option>
                    @endif
                    @foreach($valoresTipoAbast as $valor)
                        <option value="{{ $valor }}" {{ in_array($valor, request('tipo_abastecimiento', [])) ? 'selected' : '' }}>
                            {{ $valor }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="tipo_establecimiento" class="form-label">Tipo Establecimiento</label>
                <select id="tipo_establecimiento" name="tipo_establecimiento[]" class="form-control select2-multiple" multiple="multiple">
                    @php
                        $valoresTipoEstab = $registros->isNotEmpty() ? $registros->pluck('tipo_establecimiento')->unique()->filter()->values() : collect([]);
                    @endphp
                    @if($registros->whereNull('tipo_establecimiento')->count() > 0 || $registros->where('tipo_establecimiento', '')->count() > 0)
                        <option value="__NULL__" {{ in_array('__NULL__', request('tipo_establecimiento', [])) ? 'selected' : '' }}>
                            [En blanco]
                        </option>
                    @endif
                    @foreach($valoresTipoEstab as $valor)
                        <option value="{{ $valor }}" {{ in_array($valor, request('tipo_establecimiento', [])) ? 'selected' : '' }}>
                            {{ $valor }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="peti2023" class="form-label">PETI 2023</label>
                <select id="peti2023" name="peti2023[]" class="form-control select2-multiple" multiple="multiple">
                    @php
                        $valoresPeti = $registros->isNotEmpty() ? $registros->pluck('peti2023')->unique()->filter()->values() : collect([]);
                    @endphp
                    @if($registros->whereNull('peti2023')->count() > 0 || $registros->where('peti2023', '')->count() > 0)
                        <option value="__NULL__" {{ in_array('__NULL__', request('peti2023', [])) ? 'selected' : '' }}>
                            [En blanco]
                        </option>
                    @endif
                    @foreach($valoresPeti as $valor)
                        <option value="{{ $valor }}" {{ in_array($valor, request('peti2023', [])) ? 'selected' : '' }}>
                            {{ $valor }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="lista_1" class="form-label">Lista 1</label>
                <select id="lista_1" name="lista_1[]" class="form-control select2-multiple" multiple="multiple">
                    @php
                        $valoresLista1 = $registros->isNotEmpty() ? $registros->pluck('lista_1')->unique()->filter()->values() : collect([]);
                    @endphp
                    @if($registros->whereNull('lista_1')->count() > 0 || $registros->where('lista_1', '')->count() > 0)
                        <option value="__NULL__" {{ in_array('__NULL__', request('lista_1', [])) ? 'selected' : '' }}>
                            [En blanco]
                        </option>
                    @endif
                    @foreach($valoresLista1 as $valor)
                        <option value="{{ $valor }}" {{ in_array($valor, request('lista_1', [])) ? 'selected' : '' }}>
                            {{ $valor }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

    </div>

    <!-- Botón de búsqueda -->
    <div class="row mb-4">
        <div class="col-md-12 d-flex align-items-center">
            <button type="button" id="btn-filtrar" class="btn btn-primary btn-sm me-2">Filtrar</button>
            <a href="{{ route('matriz.index') }}" class="btn btn-secondary btn-sm me-2">Limpiar</a>
            <button type="button" id="btn-limpiar-filtros" class="btn btn-outline-secondary btn-sm">Limpiar Filtros</button>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h6 class="mb-0">🧪 Matriz de Disponibilidad</h6>
            </div>
            <div class="col-md-6 text-end">
                <strong>Total de registros: {{ $registros->count() }}</strong>
            </div>
        </div>
        {{-- <div class="mb-3">
            <button type="button" class="btn btn-success btn-sm">
                📥 Descargar Excel
            </button>
            <button type="button" class="btn btn-primary btn-sm">
                👁️ Visualizar
            </button>
        </div> --}}

        <!-- Contenedor con scroll horizontal y vertical explícito -->
        <div class="table-responsive">
            <table id="registros" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <!-- <th>DISA/DIRESA</th>
                        <th>UE_MEF</th>
                        <th>ALMACEN</th>
                        <th>RED</th>
                        <th>MICRORED</th>
                        <th>COD_IPRESS</th>
                        <th>NOMBRE IPRESS</th>
                        <th>TIPO ESTAB</th> -->
                        <!-- <th>IPRESS DENGUE</th>
                        <th>COD UNIF</th>
                        <th>DESC CUBO</th>
                        <th>COD SISMED</th> -->
                        <th>DESC PROD</th>
                        <th>DESC PROD ALT</th>
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
                        <th>INGRESO_ICI</th>
                        <th>FECHA_VCMTO.</th>
                        <th>CONSUMO_TOTAL</th>
                        <th>CPMA</th>
                        <th>CONSUMO_ÚLT.4MESES</th>
                        <th>MSD(MES_PROV.)</th>
                        <th>PRECIO_UNIT</th>
                        <th>MONTO</th>
                        <th>SIT.SOCK</th>
                        <th>MESES_PARA_VCMTO.</th>
                        <th>SIT_FECH_VCMTO.</th>
                        <th>MINIGRÁF.</th>
                        <th>DIST.1</th>
                        <th>DIST.2</th>
                        <th>PEND.ING.ICI</th>
                        <th>CONSUMO_TOT.PROYEC.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registros as $reg)
                        <tr>
                            <!-- <td>{{ $reg->disa_diresa }}</td>
                            <td>{{ $reg->ue_mef }}</td>
                            <td>{{ $reg->almacen_pertenece }}</td>
                            <td>{{ $reg->red }}</td>
                            <td>{{ $reg->microred }}</td>
                            <td>{{ $reg->cod_ipress }}</td>
                            <td>{{ $reg->nombre_ipress }}</td>
                            <td>{{ $reg->tipo_establecimiento }}</td> 
                            <td>{{ $reg->ipress_dengue }}</td>
                            <td>{{ $reg->cod_unificado }}</td>
                            <td>{{ $reg->descripcion_cubo }}</td>
                            <td>{{ $reg->cod_sismed }}</td>-->
                            <td>{{ $reg->descripcion_producto }}</td>
                            <td>{{ $reg->descripcion_producto_alt }}</td>
                            <td>{{ $reg->tipo_prod }}</td>
                            <td>{{ $reg->tipo_abastecimiento }}</td>
                            <td>{{ $reg->Mes1 }}</td>
                            <td>{{ $reg->Mes2 }}</td>
                            <td>{{ $reg->Mes3 }}</td>
                            <td>{{ $reg->Mes4 }}</td>
                            <td>{{ $reg->Mes5 }}</td>
                            <td>{{ $reg->Mes6 }}</td>
                            <td>{{ $reg->Mes7 }}</td>
                            <td>{{ $reg->Mes8 }}</td>
                            <td>{{ $reg->Mes9 }}</td>
                            <td>{{ $reg->Mes10 }}</td>
                            <td>{{ $reg->Mes11 }}</td>
                            <td>{{ $reg->Mes12 }}</td>
                            <td>{{ $reg->StockFinal }}</td>
                            <td>{{ $reg->ingre }}</td>
                            <td>{{ $reg->fec_exp }}</td>
                            <td>{{ $reg->consumo_total }}</td>
                            <td>{{ $reg->cpma }}</td>
                            <td>{{ $reg->consumo_ultimos_4meses }}</td>
                            <td>{{ $reg->meses_prov }}</td>
                            <td>{{$reg->precio}}</td>
                            <td>{{ $reg->monto }}</td>
                            <td>{{ $reg->situacion_stock }}</td>
                            <td>{{ $reg->meses_para_vencimiento }}</td>
                            <td>{{$reg->sit_fecha_vcmto}}</td>
                            <td class="minigrafico-cell">
                                <div class="minigrafico" data-meses="{{ $reg->Mes1 }},{{ $reg->Mes2 }},{{ $reg->Mes3 }},{{ $reg->Mes4 }},{{ $reg->Mes5 }},{{ $reg->Mes6 }},{{ $reg->Mes7 }},{{ $reg->Mes8 }},{{ $reg->Mes9 }},{{ $reg->Mes10 }},{{ $reg->Mes11 }},{{ $reg->Mes12 }}">
                                    <!-- Los gráficos se generarán con JavaScript -->
                                </div>
                            </td>
                           <td>{{$reg->dist1}}</td>
                           <td>{{$reg->dist2}}</td>
                           <td>{{$reg->pendingre_ici}}</td>
                           <td>{{$reg->consumo_total_proyectado}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
      
        <br>
    </div>
@endsection

@section('scripts')
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
                
                // Limpiar parámetros de filtros existentes
                url.searchParams.delete('tip_sum[]');
                url.searchParams.delete('tipo_prod[]');
                url.searchParams.delete('tipo_abastecimiento[]');
                url.searchParams.delete('tipo_establecimiento[]');
                url.searchParams.delete('peti2023[]');
                url.searchParams.delete('lista_1[]');
                
                // Agregar parámetros básicos
                url.searchParams.set('cod_ipress', cod_ipress);
                url.searchParams.set('fin_mes', fechaFinMes);
                
                if (cod_sismed) {
                    url.searchParams.set('cod_sismed', cod_sismed);
                } else {
                    url.searchParams.delete('cod_sismed');
                }
                
                // Agregar filtros múltiples
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


            // Botón para limpiar solo los filtros adicionales
            $('#btn-limpiar-filtros').on('click', function() {
                $('.select2-multiple').val(null).trigger('change');
                
                // Mantener los filtros principales
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
    </script>
    <script>
    // Función para obtener el último día del mes actual
        // Función para obtener el último día del mes actual
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

        // ✅ Solo asignar valor si el input está vacío (no viene de la URL)
        document.addEventListener('DOMContentLoaded', function() {
            generarMinigraficos();

            const inputFecha = document.getElementById('fechaFinMes');
            if (!inputFecha.value) {
                inputFecha.value = obtenerFinMesActual();
            }
        });
    </script>
    <script>
        function generarMinigraficos() {
            document.querySelectorAll('.minigrafico').forEach(function(container) {
                const mesesData = container.getAttribute('data-meses').split(',').map(Number);
                
                // Encontrar el valor máximo para escalar las barras
                const maxValor = Math.max(...mesesData, 1); // Evitar división por cero
                
                // Limpiar el contenedor
                container.innerHTML = '';
                
                // Crear las barras para cada mes
                mesesData.forEach(function(valor) {
                    const barra = document.createElement('div');
                    barra.className = 'barra-mini';
                    
                    // Calcular altura proporcional (mínimo 3px, máximo 25px)
                    const altura = Math.max(3, Math.min(25, (valor / maxValor) * 25));
                    barra.style.height = altura + 'px';
                    
                    // Guardar el valor como atributo para tooltip
                    barra.setAttribute('data-valor', valor);
                    
                    // Clasificar por rango de valores (opcional)
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
    </script>
    
    <script>
        // Variable para almacenar la fila seleccionada actualmente
        let filaSeleccionada = null;
        let celdaSeleccionada = null;
        let indiceFila = -1;
        let indiceColumna = -1;

        // Función para seleccionar una fila
        function seleccionarFila(fila, evento) {
            // Quitar selección anterior
            if (filaSeleccionada) {
                filaSeleccionada.classList.remove('seleccionada');
                filaSeleccionada.querySelectorAll('td').forEach(td => {
                    td.classList.remove('seleccionada');
                });
            }
            
            // Seleccionar nueva fila
            fila.classList.add('seleccionada');
            filaSeleccionada = fila;
            
            // Guardar índices
            const filas = Array.from(document.querySelectorAll('#registros tbody tr'));
            indiceFila = filas.indexOf(fila);
            
            // Si se hizo clic en una celda específica
            if (evento && evento.target.tagName === 'TD') {
                const celdas = Array.from(fila.children);
                indiceColumna = celdas.indexOf(evento.target);
                evento.target.classList.add('seleccionada');
                celdaSeleccionada = evento.target;
                
                // Hacer scroll a la celda seleccionada
                const contenedorTabla = document.querySelector('.table-responsive');
                if (contenedorTabla) {
                    const celdaOffset = evento.target.offsetLeft;
                    const celdaWidth = evento.target.offsetWidth;
                    const contenedorScrollLeft = contenedorTabla.scrollLeft;
                    const contenedorWidth = contenedorTabla.clientWidth;
                    
                    // Si la celda está fuera de vista
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
            
            // Scroll vertical a la fila seleccionada
            fila.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Función para navegar con teclado
        function navegarConTeclado(e) {
            if (!filaSeleccionada) return;
            
            const filas = Array.from(document.querySelectorAll('#registros tbody tr'));
            const encabezados = Array.from(document.querySelectorAll('#registros thead th'));
            const totalFilas = filas.length;
            const totalColumnas = document.querySelectorAll('#registros thead th').length;
            
            // Obtener el contenedor con scroll
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
                        // Quitar selección de celda anterior
                        if (celdaSeleccionada) {
                            celdaSeleccionada.classList.remove('seleccionada');
                        }
                        // Seleccionar nueva celda
                        const nuevaCelda = filaSeleccionada.children[indiceColumna];
                        nuevaCelda.classList.add('seleccionada');
                        celdaSeleccionada = nuevaCelda;
                        
                        // Hacer scroll horizontal para mostrar la celda
                        if (contenedorTabla) {
                            const celdaOffset = nuevaCelda.offsetLeft;
                            const celdaWidth = nuevaCelda.offsetWidth;
                            const contenedorScrollLeft = contenedorTabla.scrollLeft;
                            const contenedorWidth = contenedorTabla.clientWidth;
                            
                            // Si la celda está fuera de vista a la izquierda
                            if (celdaOffset < contenedorScrollLeft) {
                                contenedorTabla.scrollLeft = celdaOffset - 10;
                            }
                            // Si la celda está fuera de vista a la derecha
                            else if (celdaOffset + celdaWidth > contenedorScrollLeft + contenedorWidth) {
                                contenedorTabla.scrollLeft = celdaOffset + celdaWidth - contenedorWidth + 10;
                            }
                        }
                    }
                    break;
                    
                case 'ArrowRight':
                    e.preventDefault();
                    if (indiceColumna < totalColumnas - 1) {
                        indiceColumna++;
                        // Quitar selección de celda anterior
                        if (celdaSeleccionada) {
                            celdaSeleccionada.classList.remove('seleccionada');
                        }
                        // Seleccionar nueva celda
                        const nuevaCelda = filaSeleccionada.children[indiceColumna];
                        nuevaCelda.classList.add('seleccionada');
                        celdaSeleccionada = nuevaCelda;
                        
                        // Hacer scroll horizontal para mostrar la celda
                        if (contenedorTabla) {
                            const celdaOffset = nuevaCelda.offsetLeft;
                            const celdaWidth = nuevaCelda.offsetWidth;
                            const contenedorScrollLeft = contenedorTabla.scrollLeft;
                            const contenedorWidth = contenedorTabla.clientWidth;
                            
                            // Si la celda está fuera de vista a la izquierda
                            if (celdaOffset < contenedorScrollLeft) {
                                contenedorTabla.scrollLeft = celdaOffset - 10;
                            }
                            // Si la celda está fuera de vista a la derecha
                            else if (celdaOffset + celdaWidth > contenedorScrollLeft + contenedorWidth) {
                                contenedorTabla.scrollLeft = celdaOffset + celdaWidth - contenedorWidth + 10;
                            }
                        }
                    }
                    break;
                    
                case 'Home':
                    e.preventDefault();
                    if (e.ctrlKey) {
                        // Ctrl+Home: ir a primera fila
                        indiceFila = 0;
                        seleccionarFila(filas[0]);
                    } else {
                        // Home: ir a primera columna
                        if (filaSeleccionada) {
                            indiceColumna = 0;
                            if (celdaSeleccionada) {
                                celdaSeleccionada.classList.remove('seleccionada');
                            }
                            const primeraCelda = filaSeleccionada.children[0];
                            primeraCelda.classList.add('seleccionada');
                            celdaSeleccionada = primeraCelda;
                            
                            // Scroll al inicio
                            if (contenedorTabla) {
                                contenedorTabla.scrollLeft = 0;
                            }
                        }
                    }
                    break;
                    
                case 'End':
                    e.preventDefault();
                    if (e.ctrlKey) {
                        // Ctrl+End: ir a última fila
                        indiceFila = totalFilas - 1;
                        seleccionarFila(filas[totalFilas - 1]);
                    } else {
                        // End: ir a última columna
                        if (filaSeleccionada) {
                            indiceColumna = totalColumnas - 1;
                            if (celdaSeleccionada) {
                                celdaSeleccionada.classList.remove('seleccionada');
                            }
                            const ultimaCelda = filaSeleccionada.children[totalColumnas - 1];
                            ultimaCelda.classList.add('seleccionada');
                            celdaSeleccionada = ultimaCelda;
                            
                            // Scroll al final
                            if (contenedorTabla) {
                                contenedorTabla.scrollLeft = contenedorTabla.scrollWidth;
                            }
                        }
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
                    
                case 'Enter':
                    e.preventDefault();
                    if (filaSeleccionada) {
                        const codSismed = filaSeleccionada.querySelector('td:nth-child(4)')?.textContent;
                        console.log('Fila seleccionada:', codSismed);
                    }
                    break;
            }
        }

        // Función para manejar doble clic (opcional)
        function manejarDobleClic(e) {
            if (e.target.tagName === 'TD') {
                const fila = e.target.closest('tr');
                const codSismed = fila.querySelector('td:nth-child(4)')?.textContent;
                const descripcion = fila.querySelector('td:nth-child(5)')?.textContent;
                
                // Aquí puedes abrir un modal con detalles
                alert(`Producto: ${codSismed}\nDescripción: ${descripcion}`);
            }
        }

        // Inicializar eventos cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            const tabla = document.getElementById('registros');
            if (!tabla) return;
            
            const tbody = tabla.querySelector('tbody');
            
            // Agregar evento clic a cada fila
            tbody.querySelectorAll('tr').forEach(fila => {
                fila.addEventListener('click', function(e) {
                    seleccionarFila(this, e);
                });
            });
            
            // Agregar evento doble clic (opcional)
            tbody.addEventListener('dblclick', manejarDobleClic);
            
            // Agregar evento de teclado a nivel de documento
            document.addEventListener('keydown', navegarConTeclado);
            
            // Si hay paginación, necesitamos re-inicializar los eventos después de cada carga
            // (Esto es útil si usas DataTables o AJAX)
        });

        // Función para reinicializar eventos después de paginación o recarga AJAX
        function reinicializarEventosTabla() {
            const tabla = document.getElementById('registros');
            if (!tabla) return;
            
            const tbody = tabla.querySelector('tbody');
            
            // Quitar eventos anteriores
            tbody.querySelectorAll('tr').forEach(fila => {
                fila.removeEventListener('click', seleccionarFila);
            });
            
            // Agregar eventos nuevamente
            tbody.querySelectorAll('tr').forEach(fila => {
                fila.addEventListener('click', function(e) {
                    seleccionarFila(this, e);
                });
            });
        }

        // Si usas paginación de Laravel (con recarga de página), no necesitas esto
        // Pero si usas AJAX para recargar la tabla, llama a reinicializarEventosTabla()
        // después de cada recarga
    </script>



@endsection