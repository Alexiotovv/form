@extends('admin.base')

@section('css')
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet" />
    <link href="{{asset('css/select2-bootstrap.css')}}" rel="stylesheet"/>
@endsection

@section('content')
    <!-- Filtros en la parte superior de tu index -->
    <div class="row">
        <div class="col-md-4">
            <label for="fechaFinMes">Fin de Mes</label>
            <input type="date" id="fechaFinMes" name="fin_mes" class="form-control">
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-6">
            <label for="cod_ipress" class="form-label">Código IPRESS <span class="text-danger">*</span></label>
            <select id="cod_ipress" name="cod_ipress" class="form-control select2" required>
                <option value="">Seleccione un IPRESS</option>
                @if(request('cod_ipress'))
                    <option value="{{ request('cod_ipress') }}" selected>{{ request('cod_ipress') }} - {{ optional($registros->first())->nombre_ipress ?? 'Cargando...' }}</option>
                @endif
            </select>
        </div>
        <div class="col-md-6">
            <label for="cod_sismed" class="form-label">Código SISMED (Opcional)</label>
            <select id="cod_sismed" name="cod_sismed" class="form-control select2">
                <option value="">Todos los productos</option>
                @if(request('cod_sismed'))
                    <option value="{{ request('cod_sismed') }}" selected>{{ request('cod_sismed') }} - {{ optional($registros->first())->descripcion_producto ?? 'Cargando...' }}</option>
                @endif
            </select>
        </div>
    </div>

    <!-- Botón de búsqueda -->
    <div class="row mb-4">
        <div class="col-md-12">
            <button type="button" id="btn-filtrar" class="btn btn-primary btn-sm">Filtrar</button>
            <a href="{{ route('matriz.index') }}" class="btn btn-secondary btn-sm">Limpiar</a>
        </div>
    </div>

    <div class="container-fluid">
        <h4 class="mb-3">🧪Matriz de Disponibilidad</h4>

        {{-- <div class="mb-3">
            <button type="button" class="btn btn-success btn-sm">
                📥 Descargar Excel
            </button>
            <button type="button" class="btn btn-primary btn-sm">
                👁️ Visualizar
            </button>
        </div> --}}

        <!-- Contenedor con scroll horizontal explícito -->
        <div class="table-container">
            <table id="registros" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>DISA/DIRESA</th>
                        <th>UE_MEF</th>
                        <th>ALMACEN</th>
                        <th>RED</th>
                        <th>MICRORED</th>
                        <th>COD_IPRESS</th>
                        <th>NOMBRE IPRESS</th>
                        <th>TIPO ESTAB</th>
                        <th>IPRESS DENGUE</th>
                        <th>COD UNIF</th>
                        <th>DESC CUBO</th>
                        <th>COD SISMED</th>
                        <th>DESC PROD</th>
                        <th>DESC PROD ALT</th>
                        <th>TIPO PROD</th>
                        <th>TIPO ABAST</th>
                        <th>Mes1</th>
                        <th>Mes2</th>
                        <th>Mes3</th>
                        <th>Mes4</th>
                        <th>Mes5</th>
                        <th>Mes6</th>
                        <th>Mes7</th>
                        <th>Mes8</th>
                        <th>Mes9</th>
                        <th>Mes10</th>
                        <th>Mes11</th>
                        <th>Mes12</th>
                        <th>StockFinal</th>
                        <th>Ingreso ICI</th>
                        <th>FechaVcmto.</th>
                        <th>Consumo Total</th>
                        <th>CPMA</th>
                        <th>Consumo Últimos 4meses</th>
                        <th>MesesProv.</th>
                        <th>Monto</th>
                        <th>SituacionStock</th>
                        <th>Meses Para Vcmto.</th>
                        <th>Situación Fecha Vcmto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registros as $reg)
                        <tr>
                            <td>{{ $reg->disa_diresa }}</td>
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
                            <td>{{ $reg->cod_sismed }}</td>
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
                            <td>{{ $reg->monto }}</td>
                            <td>{{ $reg->situacion_stock }}</td>
                            <td>{{ $reg->meses_para_vencimiento }}</td>
                            <td>{{$reg->sit_fecha_vcmto}}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <br>
        {{ $registros->links() }}
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
                            tipo: $(this).attr('id') // 'cod_ipress' o 'cod_sismed'
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

            // Filtrar al hacer clic
            $('#btn-filtrar').on('click', function() {
                let cod_ipress = $('#cod_ipress').val();
                let cod_sismed = $('#cod_sismed').val();
                let fechaFinMes = $('#fechaFinMes').val(); // <-- ¡Agregamos esto!

                if (!cod_ipress) {
                    alert('Debe seleccionar un IPRESS...');
                    return;
                }

                if (!fechaFinMes) {
                    alert('Debe seleccionar una fecha de fin de mes...');
                    return;
                }

                let url = new URL(window.location.href);
                url.searchParams.set('cod_ipress', cod_ipress);
                url.searchParams.set('fin_mes', fechaFinMes); // <-- ¡Enviamos la fecha!

                if (cod_sismed) {
                    url.searchParams.set('cod_sismed', cod_sismed);
                } else {
                    url.searchParams.delete('cod_sismed');
                }

                window.location.href = url.toString();
            });
        });
    </script>
    <script>
    // Función para obtener el último día del mes actual
        function obtenerFinMesActual() {
            const hoy = new Date();
            const año = hoy.getFullYear();
            const mes = hoy.getMonth(); // Mes actual (0 = enero, 11 = diciembre)

            // Último día del mes: ponemos el día 0 del mes siguiente
            const ultimoDia = new Date(año, mes + 1, 0);

            // Formatear a YYYY-MM-DD (formato que espera el input type="date")
            const yyyy = ultimoDia.getFullYear();
            const mm = String(ultimoDia.getMonth() + 1).padStart(2, '0'); // Mes de 0-11 → 1-12
            const dd = String(ultimoDia.getDate()).padStart(2, '0');

            return `${yyyy}-${mm}-${dd}`;
        }

        // Asignar la fecha al input al cargar la página
        document.getElementById('fechaFinMes').value = obtenerFinMesActual();
    </script>
@endsection