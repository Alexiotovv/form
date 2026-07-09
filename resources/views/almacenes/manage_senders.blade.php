@extends('admin.base')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>👥 Gestionar Quienes Envían</h4>
        <a href="{{ route('almacenes.index') }}" class="btn btn-light btn-sm">← Volver a Almacenes</a>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Envían (SI)</strong>
                        <small class="text-muted ms-2" id="count-si">(0)</small>
                    </div>
                    <div class="input-group input-group-sm" style="width: 320px;">
                        <input type="text" class="form-control" id="search-si" placeholder="Buscar por código o nombre...">
                        <button class="btn btn-outline-secondary" type="button" id="clear-si">✖</button>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-hover" id="table-senders-si">
                        <thead>
                            <tr>
                                <th>COD IPRESS</th>
                                <th>NOMBRE IPRESS</th>
                                <th>ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div>
                            <small id="pagination-si" class="text-muted"></small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" id="prev-si">Anterior</button>
                            <button class="btn btn-sm btn-outline-secondary" id="next-si">Siguiente</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <strong>No envían (NO)</strong>
                        <small class="text-muted ms-2" id="count-no">(0)</small>
                    </div>
                    <div class="input-group input-group-sm" style="width: 320px;">
                        <input type="text" class="form-control" id="search-no" placeholder="Buscar por código o nombre...">
                        <button class="btn btn-outline-secondary" type="button" id="clear-no">✖</button>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-sm table-hover" id="table-senders-no">
                        <thead>
                            <tr>
                                <th>COD IPRESS</th>
                                <th>NOMBRE IPRESS</th>
                                <th>ACCIÓN</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div>
                            <small id="pagination-no" class="text-muted"></small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary" id="prev-no">Anterior</button>
                            <button class="btn btn-sm btn-outline-secondary" id="next-no">Siguiente</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto" id="toast-title">Aviso</strong>
                <small class="text-muted"></small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="toast-body"></div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const siTableBody = document.querySelector('#table-senders-si tbody');
    const noTableBody = document.querySelector('#table-senders-no tbody');

    let state = {
        si: { page: 1, total: 0, last_page: 1, search: '' },
        no: { page: 1, total: 0, last_page: 1, search: '' }
    };

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    async function loadTables() {
        await Promise.all([loadTable('SI', siTableBody), loadTable('NO', noTableBody)]);
    }

    async function loadTable(status, tbody) {
        const key = status === 'SI' ? 'si' : 'no';
        tbody.innerHTML = '<tr><td colspan="3">Cargando...</td></tr>';
        const page = state[key].page || 1;
        const search = encodeURIComponent(state[key].search || '');
        try {
            const res = await fetch(`{{ route('almacenes.senders') }}?status=${status}&page=${page}&search=${search}`);
            if (!res.ok) throw new Error('Error en la respuesta');
            const json = await res.json();
            const data = json.data || [];

            // pagination metadata
            state[key].total = json.total || 0;
            state[key].last_page = json.last_page || 1;
            state[key].current_page = json.current_page || page;

            tbody.innerHTML = '';
            data.forEach(a => {
                const tr = document.createElement('tr');
                tr.dataset.id = a.id;

                tr.innerHTML = `
                    <td>${a.cod_ipress ?? ''}</td>
                    <td>${a.nombre_ipress ?? ''}</td>
                    <td>
                        <button class="btn btn-sm btn-primary toggle-send">${status === 'SI' ? 'No Envía' : 'Sí Envía'}</button>
                    </td>
                `;

                tbody.appendChild(tr);
            });

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3">Sin resultados</td></tr>';
            }

            updateCounts();
            renderPagination(status);

        } catch (e) {
            tbody.innerHTML = '<tr><td colspan="3">Error cargando datos</td></tr>';
            console.error(e);
        }
    }

    // Delegación de eventos para botones de togglear
    document.addEventListener('click', async function (e) {
        if (!e.target.classList.contains('toggle-send')) return;
        const btn = e.target;
        const tr = btn.closest('tr');
        const id = tr.dataset.id;
        if (!id) return;

        btn.disabled = true;
        const originalText = btn.textContent;
        btn.textContent = 'Procesando...';

        try {
            const res = await fetch(`{{ url('almacenes/toggle-sender') }}/${id}`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': csrfToken(),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) throw new Error('Error en la respuesta');
            const json = await res.json();
            if (json.success) {
                showToast('Éxito', 'Estado actualizado.');
                // recargar tablas
                await loadTables();
            } else {
                showToast('Error', 'No se pudo actualizar el estado.');
            }
        } catch (err) {
            console.error(err);
            showToast('Error', 'Error al comunicarse con el servidor.');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });

    function updateCounts() {
        const siCount = state.si.total || 0;
        const siShown = siTableBody.querySelectorAll('tr[data-id]').length;
        document.getElementById('count-si').textContent = `(${siShown} / ${siCount})`;

        const noCount = state.no.total || 0;
        const noShown = noTableBody.querySelectorAll('tr[data-id]').length;
        document.getElementById('count-no').textContent = `(${noShown} / ${noCount})`;
    }

    function renderPagination(status) {
        const key = status === 'SI' ? 'si' : 'no';
        const infoEl = document.getElementById(`pagination-${key}`);
        const current = state[key].current_page || 1;
        const last = state[key].last_page || 1;
        infoEl.textContent = `Página ${current} de ${last} — Total ${state[key].total || 0}`;

        document.getElementById(`prev-${key}`).disabled = current <= 1;
        document.getElementById(`next-${key}`).disabled = current >= last;
    }

    // debounce helper
    function debounce(fn, wait) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    const doSearchSi = debounce(function (val) {
        state.si.search = val;
        state.si.page = 1;
        loadTable('SI', siTableBody);
    }, 300);

    const doSearchNo = debounce(function (val) {
        state.no.search = val;
        state.no.page = 1;
        loadTable('NO', noTableBody);
    }, 300);

    document.getElementById('search-si').addEventListener('input', function () {
        doSearchSi(this.value);
    });
    document.getElementById('search-no').addEventListener('input', function () {
        doSearchNo(this.value);
    });
    document.getElementById('clear-si').addEventListener('click', function () {
        document.getElementById('search-si').value = '';
        doSearchSi('');
    });
    document.getElementById('clear-no').addEventListener('click', function () {
        document.getElementById('search-no').value = '';
        doSearchNo('');
    });

    // pagination button handlers
    document.getElementById('prev-si').addEventListener('click', function () {
        if (state.si.page > 1) {
            state.si.page--;
            loadTable('SI', siTableBody);
        }
    });
    document.getElementById('next-si').addEventListener('click', function () {
        if (state.si.page < state.si.last_page) {
            state.si.page++;
            loadTable('SI', siTableBody);
        }
    });
    document.getElementById('prev-no').addEventListener('click', function () {
        if (state.no.page > 1) {
            state.no.page--;
            loadTable('NO', noTableBody);
        }
    });
    document.getElementById('next-no').addEventListener('click', function () {
        if (state.no.page < state.no.last_page) {
            state.no.page++;
            loadTable('NO', noTableBody);
        }
    });

    // Toast helper
    function showToast(title, message) {
        const toastEl = document.getElementById('liveToast');
        document.getElementById('toast-title').textContent = title;
        document.getElementById('toast-body').textContent = message;
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }

    loadTables();
});
</script>
@endsection
