@extends('admin.base')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>📦 Gestión de Productos</h4>
    {{-- Nuevo Producto --}}
    @can('module.productos.create')
    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
      ➕ Nuevo Producto
    </button>
    @endcan

    {{-- Botón importar --}}
    @can('module.productos.create')
    <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
        📂 imp...
    </button>
    @endcan


</div>
 <!-- Barra de búsqueda -->
  <form method="GET" action="{{ route('productos.index') }}" class="mb-3">
    <div class="input-group">  
      <input type="text" name="search" class="form-control" 
          placeholder="Buscar por código o descripción" value="{{ request('search') }}">
        <button type="submit" class="btn btn-outline-primary">🔍 Buscar</button>
    </div>
  </form>

  <!-- Botón importar Excel -->
    {{-- <form action="{{ route('productos.import') }}" method="POST" enctype="multipart/form-data" class="mb-4">
        @csrf
        <div class="input-group">
            <input type="file" name="excel_file" class="form-control" required>
            <button type="submit" class="btn btn-success">📥 Importar Excel</button>
        </div>
    </form> --}}

  <br>
  <table class="table table-bordered table-striped" id="productosTable" style="font-size: 0.7rem;">
      <thead>
          <tr>
              <th>ID</th>
              <th>Cod SISMED</th>
              <th>Descripción Producto</th>
              <th>Estado</th>
              <th>Acciones</th>
          </tr>
      </thead>
      <tbody>
          @foreach($productos as $producto)
          <tr>
              <td>{{ $producto->id }}</td>
              <td>{{ $producto->cod_sismed }}</td>
              <td>{{ $producto->descripcion_producto }}</td>
              <td>{{ $producto->estado }}</td>
              <td>
                <button class="btn btn-sm btn-info view-btn me-1"
                  data-product="{{ $producto->toJson() }}"
                  data-bs-toggle="modal"
                  data-bs-target="#viewModal">
                    👁️
                </button>
                @can('module.productos.update')
                <button class="btn btn-sm btn-warning edit-btn"
                  data-product="{{ $producto->toJson() }}"
                  data-bs-toggle="modal" 
                  data-bs-target="#editModal">
                    ✏️
                </button>
                @endcan
              </td>
          </tr>
          @endforeach
      </tbody>
  </table>

  {{ $productos->links() }}
</div>

 <!-- Modal Ver Detalle -->
  <div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog" style="max-width: 1400px;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detalle de Producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body row">
          @php
            $fillable = (new App\Models\Producto)->getFillable();
          @endphp
          @foreach($fillable as $campo)
            <div class="col-md-4 mb-3">
              <label class="form-label fw-semibold">{{ ucfirst(str_replace('_', ' ', $campo)) }}</label>
              <input
                type="text"
                id="view-{{ $campo }}"
                class="form-control"
                readonly
                disabled
              >
            </div>
          @endforeach
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

 <!-- Modal Editar Único -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog" style="max-width: 1400px;">
      <div class="modal-content">
        <form id="editForm" method="POST">
          @csrf
          @method('PUT')
          <div class="modal-header">
            <h5 class="modal-title">Editar Producto</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body row">
            <!-- Campos dinámicos -->
            <input type="hidden" name="id" id="edit-id">

            @php
              $fillable = (new App\Models\Producto)->getFillable();
            @endphp

            @foreach($fillable as $campo)
                @php
                  // Mapeo de longitudes según tu migración
                  $maxLengths = [
                      'cod_unificado' => 6,
                      'cod_sismed_analisis' => 6,
                      'cod_sismed' => 8,
                      'cod_siga' => 13,
                      'codigo_atc' => 4,
                      'cod_unspsc' => 10,
                      'descripcion_sismed' => 180,
                      'concentracion' => 50,
                      'forma_farmaceutica' => 50,
                      'presentacion' => 30,
                      'tipo_prod' => 1,
                      'lista_1' => 20,
                      'lista_2' => 20,
                      'tipo_abastecimiento' => 10,
                      'estrategico' => 1,
                      'biologicos' => 1,
                      'odontologicos' => 1,
                      'reactivos' => 1,
                      'vitales' => 5,
                      'peti2023' => 1,
                      'peti2018' => 1,
                      'peti2015' => 1,
                      'peti2012' => 1,
                      'peti2010' => 1,
                      'venta' => 1,
                      'estado' => 1,
                      'reg_sanit' => 50,
                      'descripcion_siga' => 200,
                      'descripcion_cubo' => 200,
                      'unidad_medida_x' => 10,
                      'descripcion_cubo_2' => 250,
                      'descripcion_producto' => 220,
                      'descripcion_producto_alt' => 220,
                      'descripcion_producto_eca' => 250,
                      'unidad_medida_siga' => 25,
                      'grupo' => 1,
                      'programas' => 50,
                      'programas_presupuestales' => 20,
                      'producto_fed' => 7,
                      'producto_fed_actual' => 10,
                      'tipo_indicador_fed' => 5,
                      'producto_ap_endis' => 10,
                      'anemia' => 10,
                      'claves_obstetricas' => 60,
                      'clave_azul' => 10,
                      'clave_amarilla' => 10,
                      'clave_roja' => 10,
                      'iras' => 20,
                      'iras_menor_12' => 15,
                      'edas' => 4,
                      'dengue' => 6,
                      'dengue_grupo_a' => 50,
                      'dengue_grupo_b' => 50,
                      'dengue_grupo_c' => 50,
                      'malaria' => 7,
                      'chikungunya' => 11,
                      'zika' => 4,
                      'leishmania' => 10,
                      'chagas' => 6,
                      'ofidismo' => 8,
                      'leptospirosis' => 13,
                      'planificacion_familiar' => 150,
                      'epp' => 5,
                      'covid19' => 8,
                      'covid19_apoyo_tto' => 15,
                      'covid_protocolo_minsa' => 30,
                      'pareto' => 1,
                      'vital' => 2,
                      'convenio_gestion_2020' => 5,
                      'convenio_gestion_2021' => 5,
                      'producto_cap_eca' => 7,
                  ];
                  $maxLength = $maxLengths[$campo] ?? null;
                @endphp

                <div class="col-md-4 mb-3">
                    <label class="form-label">{{ ucfirst(str_replace('_',' ',$campo)) }}</label>
                    <input 
                        type="text" 
                        name="{{ $campo }}" 
                        id="edit-{{ $campo }}" 
                        class="form-control"
                        @if($maxLength) maxlength="{{ $maxLength }}" @endif
                    >
                    <div class="invalid-feedback" id="error-{{ $campo }}"></div>
                </div>
            @endforeach
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Guardar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Modal Importar Excel --}}
  <div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title">📂 Importar Productos desde Excel</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <form action="{{ route('productos.import') }}" method="POST" enctype="multipart/form-data">
                  @csrf
                  <div class="modal-body">
                      <div class="mb-3">
                          <label for="file" class="form-label">Selecciona un archivo Excel (.xlsx, .xls)</label>
                          <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls" required>
                      </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                      <button type="submit" class="btn btn-success">📂 Importar</button>
                  </div>
              </form>
          </div>
      </div>
  </div>

  <!-- Modal Crear -->
  <div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <form action="{{ route('productos.store') }}" method="POST">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title">Nuevo Producto</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body row">
              @foreach((new App\Models\Almacen)->getFillable() as $campo)
                <div class="col-md-4 mb-3">
                  <label class="form-label">{{ ucfirst(str_replace('_',' ',$campo)) }}</label>
                  <input type="text" name="{{ $campo }}" class="form-control">
                </div>
              @endforeach
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Guardar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          </div>
        </form>
      </div>
  </div>

 

@endsection

@section('scripts')
  <script>
  document.addEventListener('DOMContentLoaded', function () {
      const editModal = document.getElementById('editModal');
      const editForm = document.getElementById('editForm');
      const viewButtons = document.querySelectorAll('.view-btn');

      viewButtons.forEach(button => {
          button.addEventListener('click', function () {
              const producto = JSON.parse(this.getAttribute('data-product'));

              for (const [key, value] of Object.entries(producto)) {
                  const input = document.getElementById(`view-${key}`);
                  if (input) {
                      input.value = value ?? '';
                  }
              }
          });
      });

      const editButtons = document.querySelectorAll('.edit-btn');

      editButtons.forEach(button => {
          button.addEventListener('click', function () {
              const producto = JSON.parse(this.getAttribute('data-product'));
              
              // Establecer la URL de actualización
              const action = "{{ route('productos.update', ':id') }}".replace(':id', producto.id);
              editForm.setAttribute('action', action);

              // Rellenar todos los campos del formulario
              for (const [key, value] of Object.entries(producto)) {
                  const input = document.querySelector(`[name="${key}"]`);
                  if (input) {
                      input.value = value || '';
                  }
              }
          });
      });
  });
  </script>

@endsection