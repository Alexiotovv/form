@extends('layouts.thanks')

@section('title', 'Gracias')

@section('content')
<div class="text-center my-5">

    <!-- Icono de éxito -->
    <img src="../../images/img_thanks.png" alt="Éxito" width="180" class="mb-4">

    <!-- Título -->
    <h2 class="fw-bold">Gracias!</h2>

    <!-- Nombre en negrita y azul -->
    <h5 class="fw-bold text-primary">{{ session('nombres') }} {{ session('apellidos') }}</h5>

    <!-- Código y establecimiento -->
    <p class="mb-1">{{ session('establecimiento') }}</p>

    <!-- Enlace al archivo ZIP -->
    <p>
        <a href="{{ session('ruta_descarga') }}" class="text-decoration-none">
            archivo_subido
        </a>
    </p>

    <!-- Fecha y hora -->
    <p class="text-muted">{{ session('fecha') }} - {{ session('hora') }}</p>

    <!-- Mensaje final -->
    <p class="text-muted">Su información ha sido recibido !</p>

    <!-- Botón para llenar de nuevo -->
    <div class="d-flex justify-content-center gap-2 mt-3">
        <a href="{{ route('registro.create') }}" class="btn btn-outline-dark">
            🔁 Llenar otra vez
        </a>
        <a href="{{ route('registro.index') }}" class="btn btn-outline-dark">
            ↩️ Ir a Lista de Archivos
        </a>
    </div>

</div>
@endsection
