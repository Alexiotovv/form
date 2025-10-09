@extends('admin.dashboard')

@section('content')

  <h4 class="mb-4">🏥 Gestión de DISAS</h4>

  <!-- Botón Agregar -->
  <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">➕ Nueva DISA</button>

  <!-- Tabla -->
  <div class="table-responsive">
      <br>
      <table id="disasTable" class="table table-bordered table-striped">
          <thead>
              <tr>
                  <th>#</th>
                  <th>Código</th>
                  <th>Nombre</th>
                  <th>Acciones</th>
              </tr>
          </thead>
          <tbody>
              @foreach($disas as $disa)
              <tr>
                  <td>{{ $disa->id }}</td>
                  <td>{{ $disa->codigo }}</td>
                  <td>{{ $disa->nombre }}</td>
                  <td>
                      <button class="btn btn-light btn-sm editBtn"
                          data-id="{{ $disa->id }}"
                          data-codigo="{{ $disa->codigo }}"
                          data-nombre="{{ $disa->nombre }}"
                          data-bs-toggle="modal"
                          data-bs-target="#editModal">
                          ✏️ Editar
                      </button>
                  </td>
              </tr>
              @endforeach
          </tbody>
      </table>
  </div>

<!-- Modal Agregar -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('disas.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">➕ Nueva DISA</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="codigo" class="form-label">Código</label>
            <input type="text" name="codigo" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title">✏️ Editar DISA</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit_id">
          <div class="mb-3">
            <label for="edit_codigo" class="form-label">Código</label>
            <input type="text" name="codigo" id="edit_codigo" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="edit_nombre" class="form-label">Nombre</label>
            <input type="text" name="nombre" id="edit_nombre" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Actualizar</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    $('#disasTable').DataTable({
        paging: false, // Mostrar todo sin paginate
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json"
        }
    });

    // Pasar datos al modal de edición
    $('.editBtn').on('click', function () {
        let id = $(this).data('id');
        let codigo = $(this).data('codigo');
        let nombre = $(this).data('nombre');

        $('#edit_id').val(id);
        $('#edit_codigo').val(codigo);
        $('#edit_nombre').val(nombre);

        $('#editForm').attr('action', '/disas/' + id);
    });
});
</script>
@endpush
