@extends('admin.base')

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">Lista de Requerimientos</h4>

    <!-- Filtro por Mes y Año -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('pedidos.index') }}">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="mes" class="form-label fw-bold">Mes</label>
                        <select name="mes" id="mes" class="form-select">
                            <option value="">Seleccionar mes</option>
                            @foreach($mesesDisponibles as $m)
                                <option value="{{ $m }}" {{ request('mes') == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->locale('es')->monthName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="anio" class="form-label fw-bold">Año</label>
                        <select name="anio" id="anio" class="form-select">
                            <option value="">Seleccionar año</option>
                            @foreach($aniosDisponibles as $a)
                                <option value="{{ $a }}" {{ request('anio') == $a ? 'selected' : '' }}>{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary">🔍 Buscar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de pedidos -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>RED</th>
                            <th>MICRORED</th>
                            <th>IPRESS</th>
                            <th>Cant. Productos</th>
                            <th>Fecha Pedido</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedidos as $pedido)
                            @php
                                $almacen = $pedido->almacen;
                                $fecha_pedido = $pedido->fecha_registro;
                                $cant_productos = $pedido->productos_count ?? 0;
                            @endphp
                            <tr>
                                <td>{{ $almacen ? $almacen->red : 'N/A' }}</td>
                                <td>{{ $almacen ? $almacen->microred : 'N/A' }}</td>
                                <td>{{ $almacen ? $almacen->cod_ipress : 'N/A' }} - {{ $almacen ? $almacen->nombre_ipress : 'N/A' }}</td>
                                <td>{{ $cant_productos }}</td>
                                <td>{{ $fecha_pedido ? \Carbon\Carbon::parse($fecha_pedido)->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info ver-productos"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalProductos"
                                            data-pedido-id="{{ $pedido->pedido_id }}">
                                        Ver productos
                                    </button>
                                     <!-- Botón FER -->
                                    <button type="button" class="btn btn-sm btn-success btn-fer"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalFER"
                                            data-pedido-id="{{ $pedido->pedido_id }}">
                                        FER
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No se encontraron pedidos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver productos -->
<div class="modal fade" id="modalProductos" tabindex="-1" aria-labelledby="modalProductosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalProductosLabel">Productos del Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Código SISMED</th>
                                <th>Descripción</th>
                                <th>Req. Final</th>
                            </tr>
                        </thead>
                        <tbody id="productos-lista">
                            <tr>
                                <td colspan="3" class="text-center">Cargando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal FER -->
<div class="modal fade" id="modalFER" tabindex="-1" aria-labelledby="modalFERLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFERLabel">FORMATO ESTÁNDAR DE REQUERIMIENTO - FER</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="fer-content">
                <!-- Contenido dinámico se cargará aquí -->
                <div class="text-center">Cargando...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn-imprimir-fer">Imprimir FER</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $(document).on('click', '.ver-productos', function () {
            const pedidoId = $(this).data('pedido-id');
            
            $.ajax({
                url: "{{ route('pedidos.productos') }}",
                method: 'GET',
                data: { pedido_id: pedidoId },
                success: function (productos) {
                    let html = '';
                    if (productos.length > 0) {
                        productos.forEach(p => {
                            html += `
                                <tr>
                                    <td>${p.cod_sismed}</td>
                                    <td>${p.descripcion_producto}</td>
                                    <td>${p.req_final || 'N/A'}</td>
                                </tr>
                            `;
                        });
                    } else {
                        html = `<tr><td colspan="3" class="text-center">No hay productos.</td></tr>`;
                    }
                    $('#productos-lista').html(html);
                },
                error: function () {
                    $('#productos-lista').html(`<tr><td colspan="3" class="text-center text-danger">Error al cargar productos.</td></tr>`);
                }
            });
        });


         // Cargar modal FER
        $(document).on('click', '.btn-fer', function () {
            const pedidoId = $(this).data('pedido-id');
            const url = "{{ route('pedidos.fer', ['pedidoId' => ':id']) }}".replace(':id', pedidoId);

            $('#fer-content').html('<div class="text-center">Cargando...</div>');

            $.ajax({
                url: url,
                method: 'GET',
                success: function (html) {
                    $('#fer-content').html(html);
                },
                error: function () {
                    $('#fer-content').html('<div class="text-center text-danger">Error al cargar el FER.</div>');
                }
            });
        });

        // Imprimir el contenido del modal FER
        $('#btn-imprimir-fer').on('click', function () {
            const printContents = document.getElementById('fer-content').innerHTML;
            const originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            window.location.reload(); // Recargar para restaurar eventos y modales
        });




    });
</script>





@endsection