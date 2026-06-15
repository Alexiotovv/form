@extends('admin.base')

@section('content')
<div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 60vh; text-align: center;">
    <div style="font-size: 5rem;">🔒</div>
    <h1 class="display-4 fw-bold text-danger mt-2">403</h1>
    <p class="lead text-muted mb-1">No tienes permiso para realizar esta acción.</p>
    <p class="text-muted mb-4" style="font-size: 0.85rem;">{{ $exception->getMessage() ?: 'Acceso denegado.' }}</p>
    <div class="d-flex gap-2">
        <button onclick="history.back()" class="btn btn-secondary">
            ← Regresar
        </button>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
            🏠 Ir al inicio
        </a>
    </div>
</div>
@endsection
