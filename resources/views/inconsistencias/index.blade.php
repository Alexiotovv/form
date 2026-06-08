@extends('admin.base')

@section('titulo_pagina', 'Inconsistencias SIS')

@section('css')
<style>
    .detalle-row td {
        background-color: #f8f9fa;
    }
    .badge-sis-null {
        background-color: #ffc107;
        color: #333;
    }
    .badge-sis-zero {
        background-color: #dc3545;
        color: #fff;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>⚠️ Inconsistencias SIS (valores 0 o nulos)</h4>
</div>

{{-- Filtro de período --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('inconsistencias.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
            <label class="form-label mb-0 fw-semibold">Período (ANNOMES):</label>
            <select name="annomes" class="form-select form-select-sm" style="max-width:200px;" onchange="this.form.submit()">
                <option value="">-- Seleccione --</option>
                @foreach($mesesDisponibles as $mes)
                    <option value="{{ $mes }}" {{ $annomes == $mes ? 'selected' : '' }}>{{ $mes }}</option>
                @endforeach
            </select>
            @if($annomes)
                <span class="text-muted small">
                    {{ $resumen->count() }} IPRESS con inconsistencias
                </span>
            @endif
        </form>
    </div>
</div>

@if(!$annomes)
    <div class="alert alert-info">Seleccione un período para ver las inconsistencias.</div>
@elseif($resumen->isEmpty())
    <div class="alert alert-success">✅ No se encontraron inconsistencias SIS para el período <strong>{{ $annomes }}</strong>.</div>
@else
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Resultados para el período <strong>{{ $annomes }}</strong></span>
        <small class="text-muted">Haz clic en "Ver" para expandir el detalle de productos</small>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>CODIGO_PRE</th>
                        <th>Nombre IPRESS</th>
                        <th class="text-center">Productos afectados</th>
                        <th class="text-center">Suma SIS</th>
                        <th class="text-center">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resumen as $fila)
                    <tr>
                        <td><code>{{ $fila->CODIGO_PRE }}</code></td>
                        <td>{{ $fila->nombre_ipress ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge bg-danger">{{ $fila->total_productos }}</span>
                        </td>
                        <td class="text-center">{{ number_format($fila->total_sis, 0) }}</td>
                        <td class="text-center">
                            <button class="btn btn-outline-secondary btn-sm"
                                data-bs-toggle="collapse"
                                data-bs-target="#detalle-{{ Str::slug($fila->CODIGO_PRE) }}"
                                aria-expanded="false">
                                Ver
                            </button>
                        </td>
                    </tr>
                    <tr class="collapse detalle-row" id="detalle-{{ Str::slug($fila->CODIGO_PRE) }}">
                        <td colspan="5" class="p-0">
                            <div class="p-3">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th>CODIGO_MED</th>
                                            <th>Descripción</th>
                                            <th class="text-center">SIS</th>
                                            <th class="text-center">SALDO</th>
                                            <th class="text-center">STOCK_FIN</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($detalle->get($fila->CODIGO_PRE, collect()) as $prod)
                                        <tr>
                                            <td><code>{{ $prod->CODIGO_MED }}</code></td>
                                            <td>{{ $prod->descripcion_sismed ?? '—' }}</td>
                                            <td class="text-center">
                                                @if(is_null($prod->SIS))
                                                    <span class="badge badge-sis-null">NULL</span>
                                                @else
                                                    <span class="badge badge-sis-zero">0</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $prod->SALDO ?? '—' }}</td>
                                            <td class="text-center">{{ $prod->STOCK_FIN ?? '—' }}</td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="5" class="text-muted text-center">Sin detalle</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection
