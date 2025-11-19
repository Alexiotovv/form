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
                    <th>Tablas Registros</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                @foreach($historicos as $h)
                <tr>
                    <td>{{ $h->id }}</td>
                    <td>{{ $h->fecha_ejecucion }}</td>
                    <td>{{ $h->tiempo_ejecucion }}</td>
                    <td>
                        @if($h->tablas_registros)
                            <pre>{{ Str::limit($h->tablas_registros, 100) }}</pre>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td>{{ $h->user->name ?? 'Desconocido' }}</td>
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
        paging: false, // usamos la paginación de Laravel
        searching: false, // usamos nuestro buscador personalizado
        info: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        }
    });
});
</script>
@endpush
