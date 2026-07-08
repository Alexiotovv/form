@extends('admin.base')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>📦 Gestión de Almacenes</h4>

        {{-- Botón nuevo --}}
        @can('module.almacenes.create')
        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
            ➕ Nuevo Almacén
        </button>
        @endcan
        {{-- Botón importar --}}
        @can('module.almacenes.create')
        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
            📂 Importar
        </button>
        @endcan
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
            <table class="table table-hover table-bordered" style="font-size: 0.7rem;">
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
                                <button class="btn btn-sm btn-info view-btn me-1"
                                        data-almacen='@json($almacen)'
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewModal">
                                    👁️ Ver
                                </button>
                                {{-- ✅ Botón de edición CORRECTO --}}
                                @can('module.almacenes.update')
                                <button class="btn btn-sm btn-light edit-btn"
                                        data-almacen='@json($almacen)'
                                        data-bs-toggle="modal"
                                        data-bs-target="#editModal">
                                    ✏️ Editar
                                </button>
                                @endcan
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

    {{-- Modal Ver Detalle --}}
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalle de Almacén</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @php
                        $fillable = (new App\Models\Almacen)->getFillable();
                    @endphp
                    <div class="row g-3">
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
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
                                @if($campo === 'para_descarga_siga')
                                    <select name="{{ $campo }}" id="edit-{{ $campo }}" class="form-select">
                                        <option value="">Seleccione...</option>
                                        <option value="SI">SI</option>
                                        <option value="NO">NO</option>
                                    </select>
                                @else
                                    <input 
                                        type="text" 
                                        name="{{ $campo }}" 
                                        id="edit-{{ $campo }}" 
                                        class="form-control"
                                        @if($maxLength) maxlength="{{ $maxLength }}" @endif
                                    >
                                @endif
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
                                    @if($campo === 'para_descarga_siga')
                                        <select name="{{ $campo }}" class="form-select">
                                            <option value="">Seleccione...</option>
                                            <option value="SI">SI</option>
                                            <option value="NO">NO</option>
                                        </select>
                                    @else
                                        <input type="text" name="{{ $campo }}" class="form-control">
                                    @endif
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
    const viewButtons = document.querySelectorAll('.view-btn');
    const editButtons = document.querySelectorAll('.edit-btn');
    const editForm = document.getElementById('editForm');

    // === CARGAR DATOS EN MODAL DE VISTA ===
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const almacen = JSON.parse(this.getAttribute('data-almacen'));

            for (const [key, value] of Object.entries(almacen)) {
                const input = document.getElementById(`view-${key}`);
                if (input) {
                    input.value = value !== null ? value : '';
                }
            }
        });
    });

    // === CARGAR DATOS EN MODAL DE EDICIÓN ===
    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const almacen = JSON.parse(this.getAttribute('data-almacen'));
            const action = "{{ route('almacenes.update', ':id') }}".replace(':id', almacen.id);
            editForm.action = action;

            for (const [key, value] of Object.entries(almacen)) {
                const input = document.querySelector(`#editForm [name="${key}"]`);
                if (input) {
                    input.value = value !== null ? value : '';
                }
            }
        });
    });

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

    setupRealTimeValidation('#editForm', 'error-edit-');
    setupRealTimeValidation('form[action="{{ route("almacenes.store") }}"]', 'error-create-');
});
</script>
@endsection