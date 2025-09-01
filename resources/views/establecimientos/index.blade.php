@extends('layouts.app')
@section('title', 'Establecimientos')
@section('content')
    <div class="container">
        <a href="{{route('admin.dashboard')}}" class="">← Volver Panel Admin</a>
        <h3>Lista de Establecimientos</h3>

        <!-- Botón para abrir modal de nuevo establecimiento -->
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalNuevo">Nuevo Establecimiento</button>

        <!-- Tabla de establecimientos -->
        <table id="tablaEstablecimientos" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>código</th>
                    <th>Nombre</th>
                    <th>Cant.Envíos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($establecimientos as $est)
                <tr>
                    <td>{{ $est->id }}</td>
                    <td>{{ $est->codigo }}</td>
                    <td>{{ $est->nombre }}</td>
                    <td>{{ $est->envios }}</td>
                    <td>
                        <button class="btn btn-sm btn-warning btnEditar" 
                            data-id="{{ $est->id }}"
                            data-codigo="{{ $est->codigo }}"
                            data-nombre="{{ $est->nombre }}"
                            data-envios="{{ $est->envios }}"
                            data-bs-toggle="modal" 
                            data-bs-target="#modalEditar">Editar</button>

                        <form method="POST" action="{{ route('establecimientos.destroy', $est) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este establecimiento?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal Crear -->
    <div class="modal fade" id="modalNuevo" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('establecimientos.store') }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Establecimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="codigo" class="form-label">Código del establecimiento</label>
                        <input type="text" name="codigo" id="codigo" class="form-control" placeholder="Código del establecimiento">
                    </div>

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del establecimiento</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Nombre del establecimiento" required>
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Cant. de Envíos</label>
                        <select name="envios" id="envios" name ="envios" class="form-select">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="modalEditar" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" class="modal-content" id="formEditar">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar Establecimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="codigo" class="form-label">Código del establecimiento</label>
                        <input type="text" name="codigo" id="editCodigo" class="form-control" placeholder="Código del establecimiento" required>
                    </div>

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del establecimiento</label>
                        <input type="text" name="nombre" id="editNombre" class="form-control" placeholder="Nombre del establecimiento" required>
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Cant. de Envíos</label>
                        <select name="envios" name ="envios" id="editEnvios"  class="form-select">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<!-- jQuery, Bootstrap JS y DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#tablaEstablecimientos').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
            }
        });

        // Configurar modal editar
        $('.btnEditar').on('click', function () {
            let id = $(this).data('id');
            let nombre = $(this).data('nombre');
            let codigo = $(this).data('codigo');
            let envios = $(this).data('envios');
            console.log(codigo);
            console.log(nombre);
            $('#editNombre').val(nombre);
            $('#editCodigo').val(codigo);
            $('#editEnvios').val(envios).change();
            $('#formEditar').attr('action', '/establecimientos/' + id);
        });
    });
</script>
@endsection
