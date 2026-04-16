@extends('admin.base')

@section('css')
    <link href="{{asset('css/select2.min.css')}}" rel="stylesheet" />
    <style>
        .progress-bar {
            transition: width 0.3s ease;
        }
        .exportacion-card {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }
        .exportacion-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .estado-pendiente { background-color: #fef3c7; color: #92400e; }
        .estado-procesando { background-color: #dbeafe; color: #1e40af; }
        .estado-completado { background-color: #d1fae5; color: #065f46; }
        .estado-error { background-color: #fee2e2; color: #991b1b; }
    </style>
@endsection

@section('titulo_pagina')
    📥 Exportación de Matriz de Disponibilidad
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Panel de filtros simplificado -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter me-2"></i>Filtros para Exportación
                </h6>
            </div>
            <div class="card-body">
                <form id="formExportacion" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">📅 Fin de Mes</label>
                        <input type="date" name="fin_mes" class="form-control" value="{{ date('Y-m-t') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">🏥 IPRESS</label>
                        <select name="cod_ipress" class="form-control select2" style="width: 100%">
                            <option value="">TODAS</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">💊 Código SISMED</label>
                        <select name="cod_sismed" class="form-control select2" style="width: 100%">
                            <option value="">TODOS</option>
                        </select>
                    </div>
                    <div class="col-md-12 mt-3">
                        <button type="submit" class="btn btn-success btn-lg w-100" id="btnExportar">
                            <i class="fas fa-download me-2"></i>Iniciar Exportación en Segundo Plano
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Progreso de exportación actual -->
        <div id="progresoContainer" class="card shadow mb-4" style="display: none;">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-spinner fa-spin me-2"></i>Progreso de Exportación
                </h6>
            </div>
            <div class="card-body">
                <div class="progress mb-3" style="height: 30px;">
                    <div id="progresoBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                        role="progressbar" style="width: 0%">0%</div>
                </div>
                <p id="progresoMensaje" class="text-muted mb-0">Iniciando...</p>
                <p id="progresoRegistros" class="text-muted small mt-2"></p>
            </div>
        </div>
        
        <!-- Historial de exportaciones -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-history me-2"></i>Historial de Exportaciones
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="tablaExportaciones">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Archivo</th>
                                <th>Registros</th>
                                <th>Estado</th>
                                <th>Progreso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($exportaciones as $exp)
                            <tr id="exportacion-{{ $exp->id }}">
                                <td>{{ $exp->created_at->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $exp->nombre_archivo ?: '—' }}</td>
                                <td>{{ number_format($exp->total_registros) }}</td>
                                <td>
                                    <span class="badge estado-{{ $exp->estado }}">
                                        {{ ucfirst($exp->estado) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" style="width: {{ $exp->progreso }}%">
                                            {{ $exp->progreso }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($exp->estado == 'completado')
                                        <a href="{{ route('matriz.exportacion.descargar', $exp->id) }}" 
                                        class="btn btn-sm btn-success">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button onclick="eliminarExportacion({{ $exp->id }})" 
                                                class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $exportaciones->links() }}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{asset('js/select2.min.js')}}"></script>
    <script src="{{ asset('js/select2-focus.js') }}"></script>
    <script>
            let intervaloProgreso = null;
            let exportacionActual = null;

            $(document).ready(function() {
                $('.select2').select2({
                    placeholder: "Buscar...",
                    allowClear: true,
                    minimumInputLength: 2,
                    ajax: {
                        url: '{{ route("matriz.search") }}',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term,
                                tipo: $(this).attr('name')
                            };
                        },
                        processResults: function(data) {
                            return { results: data };
                        }
                    }
                });
                
                $('#formExportacion').on('submit', function(e) {
                    e.preventDefault();
                    
                    const btn = $('#btnExportar');
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Iniciando...');
                    
                    $.ajax({
                        url: '{{ route("matriz.exportacion.exportar") }}',
                        method: 'POST',
                        data: $(this).serialize(),
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                mostrarProgreso(response.exportacion_id);
                                cargarHistorial();
                            }
                            btn.prop('disabled', false).html('<i class="fas fa-download me-2"></i>Iniciar Exportación en Segundo Plano');
                        },
                        error: function() {
                            alert('Error al iniciar exportación');
                            btn.prop('disabled', false).html('<i class="fas fa-download me-2"></i>Iniciar Exportación en Segundo Plano');
                        }
                    });
                });
            });

            // Definir la URL base para las rutas
            const estadoUrl = '{{ route("matriz.exportacion.estado", ["id" => "__ID__"]) }}';
            
            // Luego en tu función mostrarProgreso:
            function mostrarProgreso(exportacionId) {
                if (intervaloProgreso) clearInterval(intervaloProgreso);
                exportacionActual = exportacionId;
                $('#progresoContainer').show();
                
                const url = estadoUrl.replace('__ID__', exportacionId);
                
                intervaloProgreso = setInterval(function() {
                    $.get(url, function(data) {
                        $('#progresoBar').css('width', data.progreso + '%').text(data.progreso + '%');
                        $('#progresoMensaje').text(data.mensaje);
                        
                        if (data.registros_procesados && data.total_registros) {
                            $('#progresoRegistros').text(`Procesados: ${data.registros_procesados.toLocaleString()} de ${data.total_registros.toLocaleString()}`);
                        }
                        
                        if (data.estado === 'completado' || data.estado === 'error') {
                            clearInterval(intervaloProgreso);
                            if (data.estado === 'completado') {
                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                            }
                        }
                    });
                }, 2000);
            }

        function cargarHistorial() {
            $.get('{{ route("matriz.exportacion.index") }}', function(response) {
                // Recargar solo la tabla
                location.reload();
            });
        }

        function eliminarExportacion(id) {
            if (confirm('¿Eliminar esta exportación?')) {
                $.ajax({
                    url: '/matriz/exportacion/eliminar/' + id,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function() {
                        $(`#exportacion-${id}`).remove();
                    }
                });
            }
        }
    </script>   
@endsection