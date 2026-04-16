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
    
    /* Badge de estado de registro */
    .badge-situa {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }
    
    /* Animación de pulso para estado activo */
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(25, 135, 84, 0); }
        100% { box-shadow: 0 0 0 0 rgba(25, 135, 84, 0); }
    }
    .status-success {
        animation: pulse 2s infinite;
    }
</style>
@endsection

@section('titulo_pagina')
    🔍 Monitor de Recepción API - TMovimDet
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
                        <p class="text-muted mb-0 small">Monitoreo de recepción de detalles desde script local</p>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3">
                        <!-- Badge de estado con animación -->
                        <span class="status-badge status-{{ $estadoScript['clase'] }}">
                            {!! $estadoScript['icono'] !!} {{ $estadoScript['texto'] }}
                        </span>
                        
                        <!-- Botón refresh manual -->
                        <form action="{{ route('monitor.tmovimdet.refresh') }}" method="POST" class="d-inline">
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
            Verifica que el script local esté ejecutándose, tenga conexión con la API 
            y que las credenciales sean correctas.
        </div>
    </div>
    @elseif($estadoScript['clase'] === 'warning')
    <div class="alert alert-warning d-flex align-items-center" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <div>
            <strong>Nota:</strong> {{ $estadoScript['descripcion'] }}. 
            Si el script debería estar activo, revisa su configuración o logs.
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
                            <p class="text-muted mb-1 small text-uppercase">Hoy</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($estadisticas['total_hoy']) }}</h4>
                        </div>
                        <div class="align-self-center text-success">
                            <i class="fas fa-calendar-day fa-lg"></i>
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
                            <p class="text-muted mb-1 small text-uppercase">Productos</p>
                            <h4 class="mb-0 fw-bold">{{ number_format($estadisticas['productos_unicos']) }}</h4>
                        </div>
                        <div class="align-self-center text-info">
                            <i class="fas fa-boxes fa-lg"></i>
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
    
    <!-- 🔷 Tabla de últimos registros -->
    <div class="card shadow-sm border-0">
        <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
            <h6 class="m-0 fw-bold text-primary">
                <i class="fas fa-list me-2"></i>Últimos 10 Registros Recibidos
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
                            <th width="12%">Movimiento</th>
                            <th width="12%">Producto</th>
                            <th width="15%">Lote</th>
                            <th width="8%" class="text-end">Cant.</th>
                            <th width="8%" class="text-end">Total</th>
                            <th width="10%">Fec. Mov.</th>
                            <th width="15%">Recibido</th>
                            <th width="8%">Estado</th>
                            <th width="7%">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registros as $reg)
                        <tr>
                            <td class="fw-bold text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <span class="font-monospace small">{{ $reg->movnumero ?? '—' }}</span>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $reg->medcod ?? '—' }}</span>
                            </td>
                            <td>
                                <small class="text-muted" title="{{ $reg->medlote }}">
                                    {{ \Str::limit($reg->medlote, 12) ?? '—' }}
                                </small>
                            </td>
                            <td class="text-end">
                                {{ $reg->movcantid ? number_format($reg->movcantid, 2) : '—' }}
                            </td>
                            <td class="text-end fw-bold text-primary">
                                {{ $reg->movtotal ? 'S/ '.number_format($reg->movtotal, 2) : '—' }}
                            </td>
                            <td>
                                @if($reg->movfechult)
                                    <small>{{ \Carbon\Carbon::parse($reg->movfechult)->format('d/m H:i') }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="time-indicator" title="Timestamp: {{ $reg->created_at->format('Y-m-d H:i:s') }}">
                                    {{ $reg->created_at->diffForHumans() }}
                                    <span class="exact">{{ $reg->created_at->format('H:i:s') }}</span>
                                </span>
                            </td>
                            <td>
                                @if($reg->movsitua === 'A')
                                    <span class="badge bg-success badge-situa">Activo</span>
                                @elseif($reg->movsitua === 'I')
                                    <span class="badge bg-danger badge-situa">Inactivo</span>
                                @else
                                    <span class="badge bg-secondary badge-situa">{{ $reg->movsitua ?? '—' }}</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-light" 
                                        title="Ver detalles"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalDetalle{{ $reg->id }}">
                                    <i class="fas fa-eye text-primary"></i>
                                </button>
                            </td>
                        </tr>
                        
                        <!-- Modal de detalles del registro -->
                        <div class="modal fade" id="modalDetalle{{ $reg->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h6 class="modal-title">📋 Detalle del Registro #{{ $reg->id }}</h6>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <small class="text-muted d-block">Movimiento</small>
                                                <strong class="font-monospace">{{ $reg->movnumero }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">Producto</small>
                                                <strong>{{ $reg->medcod }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">Lote</small>
                                                <span class="text-break">{{ $reg->medlote ?? '—' }}</span>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">Fec. Vencimiento</small>
                                                <strong>{{ $reg->medfechvto?->format('d/m/Y') ?? '—' }}</strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">Cantidad</small>
                                                <strong>{{ number_format($reg->movcantid ?? 0, 2) }}</strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">Precio</small>
                                                <strong>S/ {{ number_format($reg->movprecio ?? 0, 2) }}</strong>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted d-block">Total</small>
                                                <strong class="text-primary">S/ {{ number_format($reg->movtotal ?? 0, 2) }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">Fec. Movimiento</small>
                                                <strong>{{ $reg->movfechult?->format('d/m/Y H:i') ?? '—' }}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted d-block">Recibido en servidor</small>
                                                <strong>{{ $reg->created_at->format('d/m/Y H:i:s') }}</strong>
                                            </div>
                                            <div class="col-12">
                                                <small class="text-muted d-block">Estado (movsitua)</small>
                                                <span class="badge {{ $reg->movsitua === 'A' ? 'bg-success' : ($reg->movsitua === 'I' ? 'bg-danger' : 'bg-secondary') }}">
                                                    {{ $reg->movsitua ?? '—' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-4x mb-3 d-block opacity-25"></i>
                                    <p class="mb-1 fw-medium">No hay registros recibidos aún</p>
                                    <small>Esperando datos del script local en <code>{{ config('app.url') }}/api/movimientos/det</code></small>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($registros->count() > 0)
        <div class="card-footer bg-white text-muted small py-2">
            <i class="fas fa-info-circle me-1"></i>
            Mostrando los {{ $registros->count() }} registro(s) más recientes. 
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalAyuda">
                ¿Necesitas consultar más?
            </a>
        </div>
        @endif
    </div>
    
</div>

<!-- 🔷 Modal de ayuda -->
<div class="modal fade" id="modalAyuda" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ℹ️ Información del Monitor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Esta vista muestra los <strong>últimos 10 registros</strong> de <code>tmovimdet</code> recibidos desde el script local para monitoreo rápido.</p>
                
                <hr>
                
                <h6 class="small fw-bold text-muted text-uppercase">Columnas clave:</h6>
                <ul class="small mb-3">
                    <li><strong>Recibido</strong>: Cuándo Laravel guardó el registro (monitoreo real de actividad)</li>
                    <li><strong>Fec. Mov.</strong>: Fecha original del movimiento en el sistema fuente (campo <code>movfechult</code>)</li>
                    <li><strong>Estado</strong>: Valor del campo <code>movsitua</code> (A=Activo, I=Inactivo)</li>
                </ul>
                
                <h6 class="small fw-bold text-muted text-uppercase">Indicador de estado:</h6>
                <div class="d-flex gap-2 small">
                    <span class="status-badge status-success">🟢 Activo</span>
                    <span class="status-badge status-warning">🟡 Pausa</span>
                    <span class="status-badge status-danger">🔴 Inactivo</span>
                </div>
                <p class="small mt-2 mb-0">
                    • 🟢 Último registro hace ≤ 5 minutos<br>
                    • 🟡 Último registro hace 6-30 minutos<br>
                    • 🔴 Último registro hace > 30 minutos o sin registros
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// 🔹 Polling opcional: Actualizar estado cada 30 segundos sin recargar página
document.addEventListener('DOMContentLoaded', function() {
    
    // Solo activar polling si hay al menos un registro previo
    const tieneRegistros = @json($registros->count() > 0);
    
    if (tieneRegistros) {
        setInterval(function() {
            fetch("{{ route('monitor.tmovimdet.api') }}", {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Actualizar badge de estado visualmente
                const badge = document.querySelector('.status-badge');
                if (badge && data.activo) {
                    badge.className = 'status-badge status-success';
                    badge.innerHTML = '🟢 Activo';
                } else if (badge && !data.activo && data.hace) {
                    badge.className = 'status-badge status-warning';
                    badge.innerHTML = '🟡 ' + data.hace;
                }
                
                // Actualizar timestamp de "Actualizado"
                const timestamp = document.querySelector('.card-header small');
                if (timestamp) {
                    const now = new Date();
                    timestamp.innerHTML = `<i class="fas fa-sync-alt me-1"></i>Actualizado: ${now.toLocaleTimeString('es-PE')}`;
                }
            })
            .catch(e => console.log('Polling skip:', e));
        }, 30000); // 30 segundos
    }
    
    // 🔹 Tooltip nativo para tiempos exactos
    document.querySelectorAll('.time-indicator').forEach(el => {
        el.setAttribute('title', el.getAttribute('title') || 'Click para copiar timestamp');
        el.style.cursor = 'help';
    });
    
});
</script>
@endsection