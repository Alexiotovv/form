@extends('admin.base')

@section('content')
    <h2 class="mb-4">📜 Registros Históricos</h2>

    <!-- Buscador -->
    <form method="GET" action="{{ route('historicos.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Buscar...">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
    </form>

    <div class="table-responsive">
        <table id="historicosTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha Ejecución</th>
                    <th>Tiempo Ejecución</th>
                    <th>Almacén</th>
                    <th>Archivo</th>
                    <th>Códigos PRE</th>
                    <th>Tablas Registros</th>
                    <th>Usuario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($historicos as $h)
                <tr>
                    <td>{{ $h->id }}</td>
                    <td>{{ $h->fecha_ejecucion }}</td>
                    <td>{{ $h->tiempo_ejecucion }}</td>
                    <td>
                        @if($h->registro && $h->registro->almacen)
                            <span class="badge bg-info">{{ $h->registro->almacen->nombre_ipress ?? 'N/A' }}</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td>
                        @if(!empty($h->registro_id))
                            <a href="{{ route('archivos.descargar', $h->registro_id) }}" class="btn btn-sm btn-success">
                                <i class="fas fa-download"></i> Descargar ZIP
                            </a>
                        @else
                            <span class="text-muted">Sin archivo</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $codigos = $h->obtenerCodigosPre();
                        @endphp
                        @if(!empty($codigos))
                            <div style="max-height: 100px; overflow-y: auto; font-size: 0.85rem;">
                                @foreach($codigos as $codigo)
                                    <span class="badge bg-secondary me-1 mb-1">{{ $codigo }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted">Sin códigos</span>
                        @endif
                    </td>
                    <td>
                        @if($h->tablas_registros)
                            <pre style="font-size: 0.85rem;">{{ Str::limit($h->tablas_registros, 100) }}</pre>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td>{{ $h->user->name ?? 'Desconocido' }}</td>
                    <td>
                        {{-- Botón de eliminar con confirmación --}}
                        @can('module.historicos.delete')
                        <form action="{{ route('historicos.destroy', $h->id) }}" 
                              method="POST" 
                              class="d-inline"
                              onsubmit="return confirm('¿Estás seguro de eliminar este procesamiento? Se eliminarán todos los registros de form_det asociados.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    Mostrando {{ $historicos->firstItem() }} a {{ $historicos->lastItem() }} 
    de {{ $historicos->total() }} historicos
    Página {{ $historicos->currentPage() }} de {{ $historicos->lastPage() }}   
    {{ $historicos->links('components.pagination') }}

@endsection

@push('scripts')
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#historicosTable').DataTable({
        paging: false,
        searching: false,
        info: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        }
    });
});
</script>
@endpush