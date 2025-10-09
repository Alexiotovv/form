@extends('admin.dashboard')

@section('content')

    <h4 class="mb-3">Unidades Ejecutoras</h4>

    {{-- Botón para abrir modal de crear --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <!-- Botón Nueva Unidad -->
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
            ➕ Nueva Unidad
        </button>

        <!-- Formulario importar Excel -->
        <form action="{{ route('unidadesejecutoras.import') }}" method="POST" enctype="multipart/form-data" class="d-flex">
            @csrf
            <input type="file" name="excel_file" class="form-control" required>
            <button type="submit" class="btn btn-success btn-sm">📥 Importar</button>
        </form>


        <!-- Buscador -->
        <form method="GET" action="{{ route('unidadesejecutoras.index') }}" class="d-flex">
            <div class="input-group">
                <input type="text" name="search" class="form-control" value="{{ $search ?? '' }}" placeholder="Buscar...">
                <button type="submit" class="btn btn-outline-primary btn-sm">🔍 Buscar</button>
            </div>
        </form>
    </div>


    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Código</th>
                    <th>Ejecutora</th>
                    <th>Pliego</th>
                    <th>Sector</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($unidades as $uni)
                    <tr>
                        <td>{{ $uni->codigo }}</td>
                        <td>{{ $uni->ejecutora }}</td>
                        <td>{{ $uni->pliego }}</td>
                        <td>{{ $uni->sector }}</td>
                        <td>
                            <button class="btn btn-sm btn-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editModal{{ $uni->id }}">
                                ✏️ Editar
                            </button>

                            <form action="{{ route('unidadesejecutoras.destroy', $uni) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('¿Seguro que deseas eliminar {{ $uni->ejecutora }}?')">
                                    🗑️ Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>

                    {{-- Modal Editar --}}
                    <div class="modal fade" id="editModal{{ $uni->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="{{ route('unidadesejecutoras.update', $uni) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Editar Unidad Ejecutora</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label>Código</label>
                                            <input type="text" name="codigo" class="form-control"
                                                   value="{{ $uni->codigo }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Ejecutora</label>
                                            <input type="text" name="ejecutora" class="form-control"
                                                   value="{{ $uni->ejecutora }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Pliego</label>
                                            <input type="text" name="pliego" class="form-control"
                                                   value="{{ $uni->pliego }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label>Sector</label>
                                            <input type="text" name="sector" class="form-control"
                                                   value="{{ $uni->sector }}" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">💾 Guardar cambios</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No hay registros</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $unidades->links() }}
</div>

{{-- Modal Crear --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('unidadesejecutoras.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Unidad Ejecutora</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Código</label>
                        <input type="text" name="codigo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Ejecutora</label>
                        <input type="text" name="ejecutora" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Pliego</label>
                        <input type="text" name="pliego" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Sector</label>
                        <input type="text" name="sector" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">💾 Guardar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

@endsection
