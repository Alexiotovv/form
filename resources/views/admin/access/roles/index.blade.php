@extends('admin.base')

@section('css')
<style>
    .role-card-header {
        cursor: pointer;
    }

    .role-toggle {
        user-select: none;
        font-size: 0.9rem;
        color: #6c757d;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Permisos por Rol</h4>
</div>

<div class="row g-3">
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header">Crear rol</div>
            <div class="card-body">
                <form action="{{ route('admin.access.roles.store') }}" method="POST">
                    @csrf
                    <label class="form-label">Nombre del rol</label>
                    <input type="text" name="name" class="form-control mb-3" required>
                    <button class="btn btn-success btn-sm">Guardar rol</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        @foreach($roles as $role)
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center role-card-header"
                    data-bs-toggle="collapse"
                    data-bs-target="#role-body-{{ $role->id }}"
                    aria-expanded="false"
                    aria-controls="role-body-{{ $role->id }}">
                    <div class="d-flex align-items-center gap-2">
                        <span class="role-toggle" id="role-toggle-icon-{{ $role->id }}">▸</span>
                        <strong>{{ $role->name }}</strong>
                    </div>
                    <div onclick="event.stopPropagation();">
                        @if($role->name !== 'superadmin')
                            <form action="{{ route('admin.access.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Eliminar rol?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm">Eliminar</button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-body collapse" id="role-body-{{ $role->id }}" data-role-id="{{ $role->id }}">
                    <form action="{{ route('admin.access.roles.permissions.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        @foreach($modules as $module)
                            @php
                                $modulePermissions = $permissions->filter(fn($permission) => str_starts_with($permission->name, 'module.' . $module->slug . '.'));
                                $permissionGroup = 'role-' . $role->id . '-module-' . $module->id;
                            @endphp

                            @if($modulePermissions->isNotEmpty())
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong>{{ $module->name }}</strong>
                                    <div class="form-check">
                                        <input
                                            class="form-check-input module-select-all"
                                            type="checkbox"
                                            id="select-all-{{ $permissionGroup }}"
                                            data-group="{{ $permissionGroup }}">
                                        <label class="form-check-label" for="select-all-{{ $permissionGroup }}">Seleccionar todo</label>
                                    </div>
                                </div>
                                <div class="row">
                                    @foreach($modulePermissions as $permission)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input module-permission-checkbox"
                                                    type="checkbox"
                                                    name="permissions[]"
                                                    value="{{ $permission->id }}"
                                                    data-group="{{ $permissionGroup }}"
                                                    {{ $role->permissions->contains('id', $permission->id) ? 'checked' : '' }}>
                                                <label class="form-check-label">
                                                    @if($permission->name === \App\Models\Module::REGISTRO_VIEW_ALL_PERMISSION)
                                                        Puede ver todos los registros
                                                    @elseif($permission->name === \App\Models\Module::REGISTRO_DELETE_PERMISSION)
                                                        Puede eliminar registros
                                                    @elseif($permission->name === \App\Models\Module::REGISTRO_PROCESS_PERMISSION)
                                                        Puede procesar registros
                                                    @else
                                                        {{ $permission->name }}
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <hr>
                            @endif
                        @endforeach

                        <button class="btn btn-primary btn-sm">Actualizar permisos</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleBodies = document.querySelectorAll('[id^="role-body-"]');

        roleBodies.forEach((body) => {
            const roleId = body.getAttribute('data-role-id');
            const storageKey = `role-permissions-collapsed-${roleId}`;
            const toggleIcon = document.getElementById(`role-toggle-icon-${roleId}`);

            const setExpandedState = (isExpanded) => {
                if (!toggleIcon) return;
                toggleIcon.textContent = isExpanded ? '▾' : '▸';
            };

            const collapsed = localStorage.getItem(storageKey);
            if (collapsed === 'false') {
                const instance = new bootstrap.Collapse(body, { toggle: true });
                setExpandedState(true);
            } else {
                setExpandedState(false);
            }

            body.addEventListener('shown.bs.collapse', () => {
                localStorage.setItem(storageKey, 'false');
                setExpandedState(true);
            });

            body.addEventListener('hidden.bs.collapse', () => {
                localStorage.setItem(storageKey, 'true');
                setExpandedState(false);
            });
        });

        const updateGroupState = (groupName) => {
            const children = document.querySelectorAll(`.module-permission-checkbox[data-group="${groupName}"]`);
            const master = document.querySelector(`.module-select-all[data-group="${groupName}"]`);

            if (!children.length || !master) {
                return;
            }

            const checkedCount = Array.from(children).filter((checkbox) => checkbox.checked).length;

            master.checked = checkedCount === children.length;
            master.indeterminate = checkedCount > 0 && checkedCount < children.length;
        };

        document.querySelectorAll('.module-select-all').forEach((master) => {
            const groupName = master.getAttribute('data-group');

            updateGroupState(groupName);

            master.addEventListener('change', () => {
                const children = document.querySelectorAll(`.module-permission-checkbox[data-group="${groupName}"]`);
                children.forEach((checkbox) => {
                    checkbox.checked = master.checked;
                });
                updateGroupState(groupName);
            });
        });

        document.querySelectorAll('.module-permission-checkbox').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                const groupName = checkbox.getAttribute('data-group');
                updateGroupState(groupName);
            });
        });
    });
</script>
@endsection
