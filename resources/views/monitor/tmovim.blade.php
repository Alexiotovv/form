@extends('admin.base')

@section('css')
<style>
    /* Badge de estado del script */
    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
    }
    .status-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .status-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .status-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    
    /* Tarjetas de estadísticas */
    .card-stats {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        border-left: 4px solid #0d6efd;
    }
    .card-stats:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
    }
    .card-stats.success { border-left-color: #198754; }
    .card-stats.warning { border-left-color: #ffc107; }
    .card-stats.danger { border-left-color: #dc3545; }
    
    /* Tabla de monitoreo */
    .table-monitor td {
        vertical-align: middle;
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem;
    }
    .table-monitor th {
        font-weight: 600;
        background: #f8f9fa;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .table-monitor .font-monospace {
        font-family: 'Consolas', 'Monaco', monospace;
        font-size: 0.8rem;
    }
    
    /* Indicador de tiempo */
    .time-indicator {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .time-indicator .exact {
        display: block;
        font-size: 0.75rem;
        color: #adb5bd;
    }
    
    /* Badge de estado */
    .badge-situa {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    
    /* Badge de cantidad de detalles */
    .badge-detalles {
        font-size: 0.7rem;
        background: #e9ecef;
        color: #495057;
    }
    
    /* Animación de pulso para estado activo */
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(25, 135, 84, 0); }
        100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
    }
    .status-success { animation: pulse 2s infinite; }
    
    /* Modal de detalles */
    .modal-detalles .table td {
        font-size: 0.85rem;
        padding: 0.4rem 0.5rem;
    }
</style>
@endsection

@section('titulo_pagina')
    📦 Monitor de Movimientos - TMovim
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- 🔷 Header con estado del script -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h5 class="mb-1 fw-bold">Estado del Script de Sincronización</h5>
                        <p class="text-muted mb-0 small">Monitoreo de recepción de movimientos desde script local</p>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3">
                        <span class="status-badge status-{{ $estadoScript['clase'] }}">
                            {!! $estadoScript['icono'] !!} {{ $estadoScript['texto'] }}
                        </span>
                        
                        <form action="{{ route('monitor.tmovim.refresh') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm" title="Actualizar datos">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 🔷 Alertas según estado -->
    @if($estadoScript['clase'] === 'danger' && $estadoScript['minutos'] !== null)
    <div class="alert alert-danger d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <div>
            <strong>Atención:</strong> {{ $estadoScript['descripcion'] }}. 
            Verifica que el script local esté ejecutándose y tenga conexión con la API.
        </div>
    </div>
    @elseif($estadoScript['clase'] === 'warning')
    <div class="alert alert-warning d-flex align-items-center" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <div>
            <strong>Nota:</strong> {{ $estadoScript['descripcion'] }}.
        </div>
    </div>
    @endif
    
    <!-- 🔷 Tarjetas de estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3 col-6">
            <div class="card card-stats success shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Mov. Hoy</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($estadisticas['total_hoy']) }}</h4>
                        </div>
                        <div class="align-self-center text-success">
                            <i class="fas fa-file-invoice fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-6">
            <div class="card card-stats warning shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Últ. Hora</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($estadisticas['total_ultima_hora']) }}</h4>
                        </div>
                        <div class="align-self-center text-warning">
                            <i class="fas fa-clock fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-6">
            <div class="card card-stats shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Detalles Hoy</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($estadisticas['total_detalles_hoy']) }}</h4>
                        </div>
                        <div class="align-self-center text-info">
                            <i class="fas fa-box-open fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 col-6">
            <div class="card card-stats danger shadow-sm h-100">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="text-muted mb-1 small text-uppercase">Últ. Mov.</p>
                            <h4 class="mb-0 fw-bold">
                                {{ $estadisticas['ultimo_movimiento'] ? \Carbon\Carbon::parse($estadisticas['ultimo_movimiento'])->format('H:i') : '—' }}
                            </h4>
                        </div>
                        <div class="align-self-center text-danger">
                            <i class="fas fa-exchange-alt fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 🔷 Tabla de últimos movimientos -->
    <div class="card shadow-sm border-0">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fas fa-list me-2"></i>Últimos 10 Movimientos Recibidos
            </h6>
            <small class="text-muted">
                <i class="fas fa-sync-alt me-1"></i>Actualizado: {{ now()->format('H:i:s') }}
            </small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-monitor table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Tipo</th>
                            <th width="12%">N° Movimiento</th>
                            <th width="12%">Origen</th>
                            <th width="12%">Destino</th>
                            <th width="10%" class="text-end">Total</th>
                            <th width="10%">Fec. Mov.</th>
                            <th width="12%">Recibido</th>
                            <th width="7%">Estado</th>
                            <th width="8%" class="text-center">Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movimientos as $mov)
                        <tr>
                            <td class="fw-bold text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $mov->movcoditip ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="font-monospace fw-medium">{{ $mov->movnumero ?? '—' }}</span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $mov->almcodiorg ?? '—' }}</small>
                            </td>
                            <td>
                                <small class="text-muted">{{ $mov->almcodidst ?? '—' }}</small>
                            </td>
                            <td class="text-end fw-bold text-primary">
                                {{ $mov->movtot ? 'S/ '.number_format($mov->movtot, 2) : '—' }}
                            </td>
                            <td>
                                @if($mov->movfechult)
                                    <small>{{ \Carbon\Carbon::parse($mov->movfechult)->format('d/m H:i') }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="time-indicator" title="Timestamp: {{ $mov->created_at ? $mov->created_at->format('Y-m-d H:i:s') : 'N/A' }}">
                                    {{ $mov->created_at ? $mov->created_at->diffForHumans() : '—' }}
                                    <span class="exact">{{ $mov->created_at ? $mov->created_at->format('H:i:s') : '' }}</span>
                                </span>
                            </td>
                            <td>
                                @if($mov->movsitua === 'A')
                                    <span class="badge bg-success badge-situa">Activo</span>
                                @elseif($mov->movsitua === 'I')
                                    <span class="badge bg-danger badge-situa">Inactivo</span>
                                @else
                                    <span class="badge bg-secondary badge-situa">{{ $mov->movsitua ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary btn-ver-detalles" 
                                        title="Ver productos de este movimiento"
                                        data-movnumero="{{ $mov->movnumero }}"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalDetallesMovimiento">
                                    👁️ <span class="badge-detalles rounded-pill px-2">{{ $mov->detalles_count ?? 0 }}</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-4x mb-3 d-block opacity-25"></i>
                                    <p class="mb-1 fw-medium">No hay movimientos recibidos aún</p>
                                    <small>Esperando datos del script local en <code>{{ config('app.url') }}/api/movimientos</code></small>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($movimientos->count() > 0)
        <div class="card-footer bg-white text-muted small py-2">
            <i class="fas fa-info-circle me-1"></i>
            Mostrando los {{ $movimientos->count() }} movimiento(s) más recientes.
        </div>
        @endif
    </div>
    
</div>

<!-- 🔷 Modal de Detalles del Movimiento (Productos TMovimDet) -->
<div class="modal fade modal-detalles" id="modalDetallesMovimiento" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div>
                    <h6 class="modal-title fw-bold">
                        📦 Productos del Movimiento
                    </h6>
                    <small class="text-muted" id="modalMovNumero">Cargando...</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loader -->
                <div id="modalLoader" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted small">Cargando productos...</p>
                </div>
                
                <!-- Contenido (se llena con AJAX) -->
                <div id="modalContenido" style="display: none;">
                    <!-- Info del movimiento -->
                    <div class="alert alert-info py-2 mb-3 small">
                        <strong>Proveedor:</strong> <span id="modalProveedor">—</span> | 
                        <strong>Total Mov.:</strong> <span id="modalTotal">—</span> |
                        <strong>Fec. Mov.:</strong> <span id="modalFecMov">—</span>
                    </div>
                    
                    <!-- Tabla de productos -->
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">Cód. Producto</th>
                                    <th width="20%">Lote</th>
                                    <th width="10%" class="text-end">Cant.</th>
                                    <th width="10%" class="text-end">Precio</th>
                                    <th width="12%" class="text-end">Total</th>
                                    <th width="12%">Fec. Vto.</th>
                                    <th width="8%">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="tablaDetallesBody">
                                <!-- Se llena con JS -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Total de productos -->
                    <div class="mt-3 text-end small text-muted">
                        <strong id="modalTotalProductos">0</strong> producto(s) en este movimiento
                    </div>
                </div>
                
                <!-- Error -->
                <div id="modalError" class="alert alert-danger py-2 small" style="display: none;">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <span id="modalErrorMsg">Error al cargar los detalles</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 🔹 Función para cargar detalles del movimiento vía AJAX
    function cargarDetallesMovimiento(movnumero) {
        const loader = document.getElementById('modalLoader');
        const contenido = document.getElementById('modalContenido');
        const error = document.getElementById('modalError');
        const tbody = document.getElementById('tablaDetallesBody');
        
        // Resetear modal
        loader.style.display = 'block';
        contenido.style.display = 'none';
        error.style.display = 'none';
        tbody.innerHTML = '';
        
        // Mostrar número de movimiento
        document.getElementById('modalMovNumero').textContent = `Movimiento: ${movnumero}`;
        
        // Fetch a la API
        fetch(`{{ route('monitor.tmovim.detalles.api', ['movnumero' => '__MOV__']) }}`.replace('__MOV__', movnumero))
            .then(response => {
                if (!response.ok) throw new Error('Error en la respuesta');
                return response.json();
            })
            .then(data => {
                // Llenar info del movimiento
                if (data.movimiento) {
                    document.getElementById('modalProveedor').textContent = data.movimiento.prvdescrip || '—';
                    document.getElementById('modalTotal').textContent = data.movimiento.movtot ? 'S/ ' + parseFloat(data.movimiento.movtot).toFixed(2) : '—';
                    document.getElementById('modalFecMov').textContent = data.movimiento.movfechult ? new Date(data.movimiento.movfechult).toLocaleDateString('es-PE') : '—';
                }
                
                // Llenar tabla de productos
                if (data.detalles && data.detalles.length > 0) {
                    data.detalles.forEach((det, index) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="text-muted small">${index + 1}</td>
                            <td class="fw-medium">${det.medcod || '—'}</td>
                            <td><small class="text-muted">${det.medlote ? det.medlote.substring(0, 15) + (det.medlote.length > 15 ? '...' : '') : '—'}</small></td>
                            <td class="text-end">${det.movcantid ? parseFloat(det.movcantid).toFixed(2) : '—'}</td>
                            <td class="text-end">${det.movprecio ? 'S/ ' + parseFloat(det.movprecio).toFixed(2) : '—'}</td>
                            <td class="text-end fw-bold text-primary">${det.movtotal ? 'S/ ' + parseFloat(det.movtotal).toFixed(2) : '—'}</td>
                            <td><small>${det.medfechvto ? new Date(det.medfechvto).toLocaleDateString('es-PE') : '—'}</small></td>
                            <td>
                                ${det.movsitua === 'A' ? '<span class="badge bg-success badge-situa">A</span>' : 
                                  det.movsitua === 'I' ? '<span class="badge bg-danger badge-situa">I</span>' : 
                                  '<span class="badge bg-secondary badge-situa">'+(det.movsitua||'—')+'</span>'}
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                    
                    document.getElementById('modalTotalProductos').textContent = data.total;
                    contenido.style.display = 'block';
                } else {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center py-3 text-muted small">No hay productos registrados para este movimiento</td></tr>';
                    document.getElementById('modalTotalProductos').textContent = '0';
                    contenido.style.display = 'block';
                }
                
                loader.style.display = 'none';
            })
            .catch(err => {
                console.error('Error cargando detalles:', err);
                loader.style.display = 'none';
                error.style.display = 'block';
                document.getElementById('modalErrorMsg').textContent = 'No se pudieron cargar los detalles. Intente nuevamente.';
            });
    }
    
    // 🔹 Event listener para los botones "Ver detalle"
    document.querySelectorAll('.btn-ver-detalles').forEach(btn => {
        btn.addEventListener('click', function() {
            const movnumero = this.getAttribute('data-movnumero');
            cargarDetallesMovimiento(movnumero);
        });
    });
    
    // 🔹 Limpiar modal al cerrarse
    document.getElementById('modalDetallesMovimiento').addEventListener('hidden.bs.modal', function() {
        document.getElementById('tablaDetallesBody').innerHTML = '';
        document.getElementById('modalLoader').style.display = 'block';
        document.getElementById('modalContenido').style.display = 'none';
    });
    
    // 🔹 Polling opcional para estado general (cada 30s)
    setInterval(function() {
        fetch("{{ route('monitor.tmovim.api') }}")
            .then(r => r.json())
            .then(data => {
                const badge = document.querySelector('.status-badge');
                if (badge && data.activo) {
                    badge.className = 'status-badge status-success';
                    badge.innerHTML = '🟢 Activo';
                } else if (badge && data.hace) {
                    badge.className = 'status-badge status-warning';
                    badge.innerHTML = '🟡 ' + data.hace;
                }
            })
            .catch(e => console.log('Polling skip:', e));
    }, 30000);
    
});
</script>
@endsection