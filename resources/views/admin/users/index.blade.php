@extends('admin.base')
@section('css')

<!-- DataTables con Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">


@endsection

@section('content')
    <br>
    <div class="row justify-content-between mb-4">
        <div class="col-md-6">
            <h4>Gestión de Usuarios</h4>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('admin.users.export') }}" class="btn btn-success btn-sm">⬇️ Exportar Excel</a>
            <a href="{{ route('admin.users.create') }}" class="btn btn-light btn-sm">➕ Crear Usuario</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs" id="userTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button" role="tab">Listado</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk" type="button" role="tab">Creación masiva</button>
                </li>
            </ul>

            <div class="tab-content mt-3" id="userTabsContent">
                <div class="tab-pane fade show active" id="list" role="tabpanel">
                    <table id="usersTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Estado</th>
                                <th>Establecimiento</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->roles->isNotEmpty())
                                        <span class="badge bg-warning text-dark">{{ $user->roles->pluck('name')->join(', ') }}</span>
                                    @elseif ($user->is_admin==true)
                                        <span class="badge bg-warning text-dark">superadmin (legacy)</span>
                                    @else
                                        <span class="badge bg-success">Sin rol</span>
                                    @endif
                        
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="badge bg-primary">Habilitado</span>
                                    @else
                                        <span class="badge bg-secondary">Deshabilitado</span>
                                    @endif
                                </td>
                                <td>{{ $user->almacen?->nombre_ipress ?? 'Sin asignar' }}</td>
                                <td>
                                    <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-light">
                                            {{ $user->is_active ? '🚫 Deshabilitar' : '✅ Habilitar' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-light">✏️ Edit</a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light" onclick="return confirm('Are you sure?')">🗑️ Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="bulk" role="tabpanel">
                    @include('admin.users.bulk')
                </div>
            </div>
        </div>
    </div>
    
    @endsection
    
    @section('scripts')
        <!-- DataTables con Bootstrap 5 -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
        
        <!-- DataTables con Bootstrap 5 -->
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

        <script>
            $('#usersTable').DataTable({
                "pagingType": "simple_numbers", // estilo de paginación
                "language": {
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "zeroRecords": "No se encontraron resultados",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(filtrado de _MAX_ registros en total)",
                    "search": "Buscar:",
                    "paginate": {
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                }
            });
        </script>
    @endsection