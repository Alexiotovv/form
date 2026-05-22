@extends('admin.base')

@section('css')
<style>
    #bulkRoleModal .modal-title,
    #bulkRoleModal label,
    #bulkRoleModal .form-check-label,
    #bulkRoleModal .table {
        font-size: 0.9rem;
    }

    #bulkRoleModal .modal-content {
        max-height: 90vh;
    }

    #bulkRoleModal .modal-body {
        overflow-y: auto;
    }

    #bulkRoleModal .bulk-top-section {
        margin-bottom: 0.5rem !important;
    }

    #bulkRoleModal .bulk-top-section .form-label,
    #bulkRoleModal .bulk-top-section .form-check-label {
        margin-bottom: 0.25rem;
    }

    #bulkRoleModal .bulk-top-section .form-select,
    #bulkRoleModal .bulk-top-section .form-check {
        margin-bottom: 0;
    }

    #bulkRoleModal .table-responsive {
        max-height: 250px !important;
    }

    #bulkRoleModal .modal-footer {
        flex-shrink: 0;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Permisos por Usuario</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bulkRoleModal">Asignar rol masivamente</button>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th>Accion</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->roles->pluck('name')->join(', ') ?: 'Sin rol' }}</td>
                        <td>
                            <a href="{{ route('admin.access.users.edit', $user->id) }}" class="btn btn-outline-primary btn-sm">Gestionar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="bulkRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.access.users.bulk-assign-roles') }}" id="bulkRoleForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Asignar rol masivamente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2 mb-2 bulk-top-section">
                        <div class="col-md-6">
                            <label class="form-label">Rol a asignar</label>
                            <select name="role_id" class="form-select" required>
                                <option value="">Seleccione un rol</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check me-3">
                                <input class="form-check-input" type="checkbox" name="only_without_role" value="1" id="onlyWithoutRole" checked>
                                <label class="form-check-label" for="onlyWithoutRole">Solo usuarios sin rol</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong>Usuarios seleccionados</strong>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary" id="markAllUsers">Seleccionar todos</button>
                            <button type="button" class="btn btn-outline-secondary" id="clearAllUsers">Deseleccionar</button>
                        </div>
                    </div>

                    <input type="hidden" name="user_ids_json" id="userIdsJson">
                    <div class="alert alert-info py-1 mb-2" id="selectedUsersInfo">No hay usuarios seleccionados.</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th></th>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Roles</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="bulk-user-check" name="user_ids[]" value="{{ $user->id }}" {{ $user->roles->isEmpty() ? 'checked' : '' }}>
                                        </td>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->roles->pluck('name')->join(', ') ?: 'Sin rol' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Asignar rol</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bulkChecks = () => Array.from(document.querySelectorAll('.bulk-user-check'));
        const selectedUsersInfo = document.getElementById('selectedUsersInfo');
        const userIdsJson = document.getElementById('userIdsJson');

        const syncSelectedInfo = () => {
            const selected = bulkChecks().filter((checkbox) => checkbox.checked);
            const ids = selected.map((checkbox) => checkbox.value);
            userIdsJson.value = JSON.stringify(ids);
            selectedUsersInfo.textContent = selected.length
                ? `${selected.length} usuarios seleccionados.`
                : 'No hay usuarios seleccionados.';

        }

        document.getElementById('markAllUsers')?.addEventListener('click', function () {
            bulkChecks().forEach((checkbox) => checkbox.checked = true);
            syncSelectedInfo();
        });

        document.getElementById('clearAllUsers')?.addEventListener('click', function () {
            bulkChecks().forEach((checkbox) => checkbox.checked = false);
            syncSelectedInfo();
        });

        bulkChecks().forEach((checkbox) => {
            checkbox.addEventListener('change', syncSelectedInfo);
        });

        syncSelectedInfo();

        document.getElementById('bulkRoleForm')?.addEventListener('submit', function (event) {
            const selected = bulkChecks().filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.value);

            if (!selected.length) {
                event.preventDefault();
                alert('Selecciona al menos un usuario.');
                return;
            }
        });
    });
</script>
@endsection
