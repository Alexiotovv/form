@extends('admin.dashboard')

@section('content')
<div class="container">
    <h4>Nueva Unidad Ejecutora</h4>

    <form action="{{ route('unidadesejecutoras.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Código</label>
            <input type="text" name="codigo" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Ejecutora</label>
            <input type="text" name="ejecutora" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Pliego</label>
            <input type="text" name="pliego" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Sector</label>
            <input type="text" name="sector" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-light btn-sm">💾 Guardar</button>
        <a href="{{ route('unidadesejecutoras.index') }}" class="btn btn-light btn-sm">Cancelar</a>
    </form>
</div>
@endsection
