@extends('admin.base')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Editar permisos de usuario: {{ $user->name }}</h4>
    <a href="{{ route('admin.access.users.index') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
</div>

<form action="{{ route('admin.access.users.update', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Roles</div>
                <div class="card-body">
                    @foreach($roles as $role)
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->id }}" {{ in_array($role->id, $userRoleIds, true) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ $role->name }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Permisos directos por modulo</div>
                <div class="card-body">
                    @foreach($modules as $module)
                        <div class="mb-2">
                            <strong>{{ $module->name }}</strong>
                            <div class="row">
                                @foreach($permissions as $permission)
                                    @if(str_starts_with($permission->name, 'module.' . $module->slug . '.'))
                                        <div class="col-md-6">
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" {{ in_array($permission->id, $userPermissionIds, true) ? 'checked' : '' }}>
                                                <label class="form-check-label">{{ $permission->name }}</label>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <hr>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <button class="btn btn-primary">Guardar cambios</button>
    </div>
</form>
@endsection
