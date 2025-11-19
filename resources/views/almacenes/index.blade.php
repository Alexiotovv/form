@extends('admin.base')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>📦 Gestión de Almacenes</h4>

        {{-- Botón nuevo --}}
        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
            ➕ Nuevo Almacén
        </button>
        {{-- Botón importar --}}
        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
            📂 Importar
        </button>
    </div>

    {{-- Buscador --}}
    <form method="GET" action="{{ route('almacenes.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control"
                   value="{{ $search ?? '' }}" placeholder="Buscar por: pliego, cod_ipress, disa_diresa, departamento o nombre de ipress">
            <button type="submit" class="btn btn-outline-primary">🔍 Buscar</button>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th>COD PLIEGO</th>
                        <th>PLIEGO</th>
                        <th>COD DISA</th>
                        <th>DISA/DIRESA</th>
                        <th>COD UE MEF</th>
                        <th>UE MEF</th>
                        <th>DEPARTAMENTO</th>
                        <th>UBIGEO</th>
                        <th>PROVINCIA</th>
                        <th>DISTRITO</th>
                        <th>NOMBRE IPRESS</th>
                        <th>NIVEL</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($almacenes as $almacen)
                        <tr>
                            <td>{{ $almacen->cod_pliego }}</td>
                            <td>{{ $almacen->pliego }}</td>
                            <td>{{ $almacen->cod_disa }}</td>
                            <td>{{ $almacen->disa_diresa }}</td>
                            <td>{{ $almacen->cod_ue_mef }}</td>
                            <td>{{ $almacen->ue_mef }}</td>
                            <td>{{ $almacen->departamento }}</td>
                            <td>{{ $almacen->ubigeo }}</td>
                            <td>{{ $almacen->provincia }}</td>
                            <td>{{ $almacen->distrito }}</td>
                            <td>{{ $almacen->nombre_ipress }}</td>
                            <td>{{ $almacen->nivel }}</td>
                            <td>
                                {{-- ✅ Botón de edición CORRECTO --}}
                                <button class="btn btn-sm btn-light edit-btn"
                                        data-almacen="{{ $almacen->toJson() }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal">
                                    ✏️ Editar
                                </button>
                            </td>
                        </tr>
                        
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $almacenes->links() }}
            </div>
        </div>
    </div>

    {{-- ✅ Modal Editar Único --}}
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Almacén</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit-id">
                        @php
                            $fillable = (new App\Models\Almacen)->getFillable();
                        @endphp

                        <div class="row g-3">
                        @foreach($fillable as $campo)
                            @php
                                $maxLengths = [
                                    'pliego' => 250,
                                    'disa_diresa' => 250,
                                    'ue_mef' => 255,
                                    'departamento' => 50,
                                    'ubigeo' => 25,
                                    'provincia' => 50,
                                    'distrito' => 50,
                                    'cod_renipress' => 255,
                                    'cod_ipress' => 10,
                                    'red' => 255,
                                    'microred' => 255,
                                    'nombre_ipress' => 100,
                                    'codigo_nombre_ipress' => 100,
                                    'nivel' => 10,
                                    'tipo_establecimiento' => 50,
                                    'estado_ipress' => 10,
                                    'universo_ipress' => 2,
                                    'ipress_feed' => 2,
                                    'ipress_eca' => 2,
                                    'ipress_evaluar_disponibilidad' => 2,
                                    'ipress_dengue' => 7,
                                    'ipress_prio_temp_bajas' => 2,
                                    'ipress_prio_riesg_lluv' => 15,
                                    'est_pert_cuencas' => 10,
                                    'ipress_prio_plan_malaria' => 2,
                                    'almacen_pertenece' => 50,
                                    'filtro' => 6,
                                    'ruta_distribucion' => 7,
                                    'monitor' => 100,
                                    'digitador' => 100,
                                    'envios'=>1,
                                    'para_descarga_siga'=>2,

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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">💾 Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ✅ Modal Crear --}}
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">➕ Nuevo Almacén</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('almacenes.store') }}" method="POST">
                    @csrf
                    <div class="modal-body row">
                        <div class="row g-3">
                            @foreach((new App\Models\Almacen)->getFillable() as $campo)
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">{{ ucfirst(str_replace('_',' ',$campo)) }}</label>
                                    <input type="text" name="{{ $campo }}" class="form-control">
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success btn-sm">💾 Guardar</button>
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
                    <h5 class="modal-title">📂 Importar Almacenes desde Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('almacenes.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file" class="form-label">Selecciona un archivo Excel (.xlsx, .xls)</label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
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

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // === CARGAR DATOS EN MODAL DE EDICIÓN ===
    const editButtons = document.querySelectorAll('.edit-btn');
    const editForm = document.getElementById('editForm');

    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const almacen = JSON.parse(this.getAttribute('data-almacen'));
            const action = "{{ route('almacenes.update', ':id') }}".replace(':id', almacen.id);
            editForm.action = action;

            // Rellenar campos
            for (const [key, value] of Object.entries(almacen)) {
                const input = document.querySelector(`#editForm [name="${key}"]`);
                if (input) {
                    input.value = value || '';
                }
            }
        });
    });

    // === VALIDACIÓN EN TIEMPO REAL ===
    function setupRealTimeValidation(formSelector, errorPrefix = 'error-') {
        const form = document.querySelector(formSelector);
        if (!form) return;

        const inputs = form.querySelectorAll('input[maxlength], input[required]');
        inputs.forEach(input => {
            const fieldName = input.name;
            const errorDiv = document.getElementById(`${errorPrefix}${fieldName}`);
            const maxLength = input.getAttribute('maxlength');
            const isRequired = input.hasAttribute('required');

            const validate = () => {
                const value = input.value.trim();
                input.classList.remove('is-invalid');

                if (isRequired && value === '') {
                    input.classList.add('is-invalid');
                    if (errorDiv) errorDiv.textContent = 'Este campo es obligatorio.';
                    return false;
                }

                if (maxLength && value.length > parseInt(maxLength)) {
                    input.classList.add('is-invalid');
                    if (errorDiv) errorDiv.textContent = `Máximo ${maxLength} caracteres.`;
                    return false;
                }

                if (errorDiv) errorDiv.textContent = '';
                return true;
            };

            input.addEventListener('input', validate);
            input.addEventListener('blur', validate);
        });

        // Validar al enviar
        form.addEventListener('submit', function (e) {
            let isValid = true;
            inputs.forEach(input => {
                const value = input.value.trim();
                const maxLength = input.getAttribute('maxlength');
                const isRequired = input.hasAttribute('required');

                if (isRequired && value === '') {
                    isValid = false;
                    input.classList.add('is-invalid');
                } else if (maxLength && value.length > parseInt(maxLength)) {
                    isValid = false;
                    input.classList.add('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Corrige los campos marcados en rojo.');
            }
        });
    }

    // Aplicar validación a ambos formularios
    setupRealTimeValidation('#editForm', 'error-edit-');
    setupRealTimeValidation('form[action="{{ route("almacenes.store") }}"]', 'error-create-');
});
</script>
@endsection