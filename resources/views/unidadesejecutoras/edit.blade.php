@extends('admin.dashboard')

@section('content')
<div class="container">
    <h4>Editar Unidad Ejecutora</h4>

    <form action="{{ route('unidadesejecutoras.update', $unidade) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>Código</label>
            <input type="text" name="codigo" class="form-control" value="{{ $unidade->codigo }}" required>
        </div>
        <div class="mb-3">
            <label>Ejecutora</label>
            <input type="text" name="ejecutora" class="form-control" value="{{ $unidade->ejecutora }}" required>
        </div>
        <div class="mb-3">
            <label>Pliego</label>
            <input type="text" name="pliego" class="form-control" value="{{ $unidade->pliego }}" required>
        </div>
        <div class="mb-3">
            <label>Sector</label>
            <input type="text" name="sector" class="form-control" value="{{ $unidade->sector }}" required>
        </div>
        <button type="submit" class="btn btn-success">💾 Actualizar</button>
        <a href="{{ route('unidades.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
