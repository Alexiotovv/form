@extends('admin.base')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Gestion de Modulos</h4>
    <form action="{{ route('admin.access.modules.sync') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-outline-primary btn-sm">Sincronizar desde rutas *.index</button>
    </form>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Nuevo modulo</div>
            <div class="card-body">
                <form action="{{ route('admin.access.modules.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" required>
                        <small class="text-muted">Ejemplo: matriz-exportacion</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Ruta index</label>
                        <input type="text" name="route_name_index" class="form-control" required>
                        <small class="text-muted">Ejemplo: matriz.exportacion.index o plazo.edit</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Descripcion</label>
                        <textarea name="description" rows="2" class="form-control"></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                        <label class="form-check-label">Activo</label>
                    </div>
                    <button class="btn btn-success btn-sm" type="submit">Guardar modulo</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Modulos registrados</div>
            <div class="card-body table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Slug</th>
                            <th>Ruta index</th>
                            <th>Estado</th>
                            <th>Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modules as $module)
                            <tr>
                                <td>{{ $module->name }}</td>
                                <td><code>{{ $module->slug }}</code></td>
                                <td><code>{{ $module->route_name_index }}</code></td>
                                <td>
                                    @if($module->is_active)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#edit-{{ $module->id }}">Editar</button>
                                </td>
                            </tr>
                            <tr class="collapse" id="edit-{{ $module->id }}">
                                <td colspan="5">
                                    <form action="{{ route('admin.access.modules.update', $module->id) }}" method="POST" class="row g-2">
                                        @csrf
                                        @method('PUT')
                                        <div class="col-md-3"><input class="form-control form-control-sm" type="text" name="name" value="{{ $module->name }}" required></div>
                                        <div class="col-md-2"><input class="form-control form-control-sm" type="text" name="slug" value="{{ $module->slug }}" required></div>
                                        <div class="col-md-3"><input class="form-control form-control-sm" type="text" name="route_name_index" value="{{ $module->route_name_index }}" required></div>
                                        <div class="col-md-2"><input class="form-control form-control-sm" type="text" name="description" value="{{ $module->description }}"></div>
                                        <div class="col-md-1 form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $module->is_active ? 'checked' : '' }}>
                                        </div>
                                        <div class="col-md-1 text-end"><button class="btn btn-primary btn-sm">OK</button></div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted">No hay modulos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
