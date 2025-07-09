@extends('admin.base')

@section('content')
<div class="container">
    <a href="{{ route('admin.dashboard') }}" class="">
        ← Volver al Panel Admin
    </a>

    <h3>Configurar Clave de Acceso al Formulario</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('clave.update') }}">
        @csrf
        <div class="mb-3">
            <label>Nueva Clave</label>
            <input type="text" name="clave" class="form-control" value="{{ old('clave', $clave->clave ?? '') }}" required>
        </div>
        <button class="btn btn-primary">Guardar</button>
    </form>

</div>
@endsection
